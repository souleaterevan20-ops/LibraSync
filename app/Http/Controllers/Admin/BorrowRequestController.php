<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BorrowRecord;
use App\Services\AuditLogger;
use App\Services\LeaderboardService;
use App\Services\Notifier;
use App\Services\PenaltyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BorrowRequestController extends Controller
{
    /**
     * Full detail view for a single borrow record — "View Borrow Details".
     */
    public function show(BorrowRecord $record)
    {
        $record->load(['user', 'book', 'checkedInBy', 'penaltyPayments.recordedBy']);

        return view('admin.borrow-detail', compact('record'));
    }

    /**
     * Display all pending borrow requests.
     */
    public function index()
    {
        $requests = BorrowRecord::where('status', 'pending')
            ->with(['user', 'book'])
            ->orderBy('created_at', 'asc')
            ->get();

        return view('admin.borrow-requests', compact('requests'));
    }

    /**
     * Approve the borrow request and decrement available book copies.
     */
    public function approve(BorrowRecord $record)
    {
        $book = $record->book;

        if ($book->available_copies < 1) {
            return redirect()->back()->with('error', "Cannot approve request. No available copies left of '{$book->title}'.");
        }

        $record->update([
            'status' => 'borrowed',
            'borrowed_at' => now(),
            'due_at' => now()->addDays(7),
            'checked_out_by' => auth()->id(),
        ]);

        $book->decrement('available_copies');

        AuditLogger::log('Approved Borrow Request', $record, "Approved '{$book->title}' for {$record->user->name}");
        Notifier::send($record->user, 'borrow_approved', 'Borrow request approved', "Your request to borrow '{$book->title}' has been approved. Please pick it up and return by ".now()->addDays(7)->format('M j, Y').'.', route('my-borrowings.index'), external: true);

        return redirect()->back()->with('status', "Successfully approved borrow request for '{$book->title}'!");
    }

    /**
     * Reject a pending borrow request.
     */
    public function reject(Request $request, BorrowRecord $record)
    {
        $book = $record->book;
        $reason = $request->input('reason');

        $record->update(['status' => 'rejected']);

        AuditLogger::log('Rejected Borrow Request', $record, $reason);
        Notifier::send($record->user, 'borrow_rejected', 'Borrow request rejected', "Your request to borrow '{$book->title}' was rejected.".($reason ? " Reason: {$reason}" : ''), route('my-borrowings.index'), external: true);

        return redirect()->back()->with('status', "Borrow request for '{$book->title}' has been rejected.");
    }

    /**
     * Display all active loans (books currently checked out).
     *
     * Spec #30 (staff Returns page needs "Search borrower / Search book") and
     * #58 (paginate lists, don't load entire tables) — this page used to pull
     * every single active loan on every request with no way to narrow it down.
     */
    public function activeLoans(Request $request)
    {
        $search = $request->input('search');

        $loans = BorrowRecord::where('status', 'borrowed')
            ->with(['user', 'book'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })->orWhereHas('book', function ($bq) use ($search) {
                        $bq->where('title', 'like', "%{$search}%")
                            ->orWhere('isbn', 'like', "%{$search}%");
                    });
                });
            })
            ->orderBy('due_at', 'asc')
            ->paginate(15)
            ->withQueryString();

        $settledRecently = BorrowRecord::where('status', 'returned')
            ->where('fine_amount', '>', 0)
            ->with(['user', 'book'])
            ->orderByDesc('returned_at')
            ->take(25)
            ->get();

        return view('admin.returns', compact('loans', 'settledRecently', 'search'));
    }

    /**
     * Explicitly flag an active loan as overdue: charges the current running
     * late fine to the borrower's penalty balance right now (rather than
     * waiting until the book is eventually returned) and notifies them.
     * Safe to use even though the book stays checked out — the eventual
     * check-in only charges any *additional* fine accrued since this point.
     * Safe to click repeatedly (idempotent) — see PenaltyService::syncOverdueCharge().
     */
    public function markOverdue(BorrowRecord $record)
    {
        if ($record->status !== 'borrowed') {
            return redirect()->back()->with('error', 'Only active loans can be marked overdue.');
        }

        if (! $record->isOverdue()) {
            return redirect()->back()->with('error', "'{$record->book->title}' isn't past its due date yet.");
        }

        $charge = DB::transaction(fn () => PenaltyService::syncOverdueCharge($record));

        if (! $charge) {
            return redirect()->back()->with('status', "'{$record->book->title}' is already up to date — no additional penalty owed since the last sync.");
        }

        $record->refresh();

        Notifier::send(
            $record->user,
            'overdue',
            'Book marked overdue',
            "'{$record->book->title}' is {$charge['days_late']} day(s) overdue. A ₱" . number_format($charge['fine'], 2) . " penalty has been applied to your account. Please return it as soon as possible.",
            route('fines.index')
        );

        AuditLogger::log('Marked Book Overdue', $record, "Charged ₱{$charge['fine']} penalty and -{$charge['points']} points for '{$record->book->title}' ({$charge['days_late']} day(s) late).");

        return redirect()->back()->with('status', "'{$record->book->title}' marked overdue. ₱" . number_format($charge['fine'], 2) . " penalty applied to {$record->user->name} and they've been notified.");
    }

    public function returnBook(Request $request, BorrowRecord $record)
    {
        // Guard against reprocessing a record that's already been checked in —
        // without this, a double form-submit (or clicking Lost/Damaged after
        // Return) would double-charge penalties/points and inflate inventory
        // counts. This is the "never count the same lost book twice" requirement
        // applied to every return path, not just lost books (spec #20).
        if ($record->status !== 'borrowed') {
            return redirect()->back()->with('error', 'This book has already been checked in — no changes were made.');
        }

        $book = $record->book;
        $condition = $request->input('condition', 'good'); // good, lost, damaged
        $wasOverdue = $record->isOverdue();

        $request->validate([
            'penalty_amount' => ['nullable', 'numeric', 'min:0', 'max:99999'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        // Bring the overdue fine fully up to date before finalizing the return.
        // For lost/damaged books, the flat -20 point penalty below replaces the
        // per-day point deduction, so points aren't touched here — only the fine.
        $isLostOrDamaged = in_array($condition, ['lost', 'damaged'], true);
        $notes = $request->input('notes');

        [$overdueFineSoFar, $replacementCharge] = DB::transaction(function () use ($record, $book, $condition, $wasOverdue, $isLostOrDamaged, $request, $notes) {
            PenaltyService::syncOverdueCharge($record, deductPoints: ! $isLostOrDamaged);
            $record->refresh();

            // For lost/damaged, the Library Staff can manually set the penalty
            // (defaults to the book's listed replacement cost if left blank).
            $replacementCharge = 0;
            if ($isLostOrDamaged) {
                $replacementCharge = $request->filled('penalty_amount')
                    ? (float) $request->input('penalty_amount')
                    : (float) $book->replacement_cost;
            }

            $overdueFineSoFar = (float) $record->fine_amount;
            $totalCharge = $overdueFineSoFar + $replacementCharge;
            $fineStatus = $totalCharge > 0 ? 'unpaid' : 'none';

            $record->update([
                'status' => 'returned',
                'returned_at' => now(),
                'condition' => $condition,
                'fine_amount' => $totalCharge,
                'fine_status' => $fineStatus,
                'checked_in_by' => auth()->id(),
            ]);

            // Inventory model (spec #19): TOTAL = AVAILABLE + BORROWED + LOST + DAMAGED.
            // A lost/damaged book never goes back on the shelf as available — it moves
            // into its own tracked bucket instead, and total_copies is never silently
            // changed by a return (only a deliberate Super Admin inventory adjustment
            // should ever change the total).
            match ($condition) {
                'lost' => $book->increment('lost_copies'),
                'damaged' => $book->increment('damaged_copies'),
                default => $book->increment('available_copies'),
            };

            if ($replacementCharge > 0) {
                $record->user->increment('penalty_balance', $replacementCharge);
            }

            $this->applyReadingPoints($record, $condition, $wasOverdue);

            Notifier::send($record->user, 'returned', 'Book returned', "'{$book->title}' has been successfully checked in.", null, $record);
            AuditLogger::log('Returned Book', $record, "Condition: {$condition}" . ($notes ? " — Notes: {$notes}" : ''));

            return [$overdueFineSoFar, $replacementCharge];
        });

        if ($isLostOrDamaged) {
            $noteLine = $notes ? " Note from staff: \"{$notes}\"." : '';
            $chargeLine = $replacementCharge > 0
                ? " A penalty of ₱" . number_format($replacementCharge, 2) . ' has been added to your account.'
                : '';

            Notifier::send(
                $record->user,
                'penalty',
                'Penalty issued',
                "'{$book->title}' was marked {$condition}. -20 leaderboard points applied.{$chargeLine}{$noteLine}",
                route('fines.index'),
                reference: $record,
                external: true
            );

            return redirect()->back()->with('status', "Book '{$book->title}' marked as {$condition}." . ($replacementCharge > 0 ? ' Penalty of ₱' . number_format($replacementCharge, 2) . ' charged to the borrower.' : ''));
        }

        if ($overdueFineSoFar > 0) {
            return redirect()->back()->with('status', "Book '{$book->title}' has been checked in. Note: Student returned the book late and accrued a fine of ₱" . number_format($overdueFineSoFar, 2) . "!");
        }

        return redirect()->back()->with('status', "Book '{$book->title}' has been successfully returned on time!");
    }

    /**
     * Leaderboard points on return: +10 on-time, -20 flat for lost/damaged.
     * Overdue-day deductions already happened inside PenaltyService::syncOverdueCharge()
     * above, so an overdue-but-good return doesn't need any further point change here.
     */
    private function applyReadingPoints(BorrowRecord $record, string $condition, bool $wasOverdue): void
    {
        $user = $record->user;

        if ($condition === 'lost' || $condition === 'damaged') {
            LeaderboardService::deduct($user, 20, ucfirst($condition) . " book — '{$record->book->title}'");
            // NOTE: no separate "Penalty issued" notification here — returnBook()
            // sends one combined notification after this method returns (points
            // deduction + replacement charge together), instead of two "Penalty
            // issued" notifications back-to-back for the same lost/damaged event
            // (spec #42/#43: "free from duplicate notifications").
            return;
        }

        if ($wasOverdue) {
            // Points for the overdue days were already deducted by PenaltyService.
            Notifier::send($user, 'penalty', 'Penalty issued', "Overdue return — penalty already applied via daily sync.", null, $record);
            return;
        }

        LeaderboardService::award($user, 10, "On-time return — '{$record->book->title}'");
        Notifier::send($user, 'reward', 'Points earned', 'You earned +10 points for returning your book on time!', null, $record);
    }
}