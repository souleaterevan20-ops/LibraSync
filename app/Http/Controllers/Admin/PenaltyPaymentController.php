<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BorrowRecord;
use App\Models\PenaltyPayment;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\Notifier;
use App\Services\PenaltyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Penalty Settlement — spec #30-40. Every penalty belongs to a specific
 * BorrowRecord (not just a user), so Book A and Book B can be settled
 * independently even though they belong to the same borrower.
 */
class PenaltyPaymentController extends Controller
{
    /**
     * Dedicated Penalty Management / Settlement page (spec #32-33).
     */
    public function index(Request $request)
    {
        $query = BorrowRecord::with(['user', 'book', 'checkedInBy'])
            ->where('fine_amount', '>', 0);

        if ($request->filled('status')) {
            $query->where('fine_status', $request->input('status'));
        }
        if ($request->filled('user')) {
            $query->whereHas('user', fn ($q) => $q->where('name', 'like', '%' . $request->input('user') . '%'));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('returned_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('returned_at', '<=', $request->input('date_to'));
        }

        $records = $query->orderByDesc('updated_at')->paginate(20)->withQueryString();

        // Summary cards — computed from actual database records (spec #33).
        $allWithFines = BorrowRecord::where('fine_amount', '>', 0)->get();
        $totalOutstanding = $allWithFines->sum(fn ($r) => $r->fineRemaining());
        $totalCollected = (float) PenaltyPayment::sum('amount');
        $unpaidCount = $allWithFines->filter(fn ($r) => $r->computedFineStatus() === 'unpaid')->count();
        $partiallyPaidCount = $allWithFines->filter(fn ($r) => $r->computedFineStatus() === 'partially_paid')->count();
        $paidCount = $allWithFines->filter(fn ($r) => $r->computedFineStatus() === 'paid')->count();
        $overdueBooksCount = BorrowRecord::where('status', 'borrowed')->where('due_at', '<', now())->count();

        return view('admin.penalties.index', [
            'records' => $records,
            'totalOutstanding' => $totalOutstanding,
            'totalCollected' => $totalCollected,
            'unpaidCount' => $unpaidCount,
            'partiallyPaidCount' => $partiallyPaidCount,
            'paidCount' => $paidCount,
            'overdueBooksCount' => $overdueBooksCount,
        ]);
    }

    /**
     * Record a full or partial payment against ONE borrow record's penalty.
     * Available to Super Admin and Library Staff — whoever is at the
     * desk taking the payment.
     */
    public function store(Request $request, BorrowRecord $record)
    {
        $remaining = $record->fineRemaining();

        if ($remaining <= 0) {
            return redirect()->back()->withInput()->with('error', 'This penalty is already fully settled — nothing left to pay.');
        }

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:' . $remaining],
            'payment_method' => ['nullable', 'string', 'in:cash,gcash,other'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $user = $record->user;
        $amount = round((float) $validated['amount'], 2);

        [$balanceBefore, $newBalance] = DB::transaction(function () use ($record, $user, $amount, $validated) {
            $balanceBefore = (float) $user->penalty_balance;

            $record->update([
                'fine_paid_amount' => round((float) $record->fine_paid_amount + $amount, 2),
            ]);
            $record->update(['fine_status' => $record->computedFineStatus()]);

            // The user's penalty balance is never set directly by the frontend —
            // it's always recalculated from the actual remaining amounts across
            // every one of their borrow records (spec #38).
            $newBalance = (float) $user->borrowRecords()
                ->where('fine_amount', '>', 0)
                ->get()
                ->sum(fn ($r) => $r->fineRemaining());
            $user->update(['penalty_balance' => $newBalance]);

            PenaltyPayment::create([
                'user_id' => $user->id,
                'borrow_record_id' => $record->id,
                'recorded_by' => auth()->id(),
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $newBalance,
                'payment_method' => $validated['payment_method'] ?? 'cash',
                'notes' => $validated['notes'] ?? null,
            ]);

            return [$balanceBefore, $newBalance];
        });

        $fullySettled = $record->fineRemaining() <= 0;
        $bookTitle = $record->book->title ?? 'the book';

        if ($fullySettled) {
            Notifier::send(
                $user,
                'penalty',
                'Penalty fully settled!',
                "Your ₱{$amount} payment fully settled the penalty for '{$bookTitle}'. Thank you!",
                route('fines.index'),
                external: true
            );
        } else {
            Notifier::send(
                $user,
                'penalty',
                'Penalty payment received',
                "Your payment of ₱" . number_format($amount, 2) . " for '{$bookTitle}' has been recorded. Remaining on this book: ₱" . number_format($record->fineRemaining(), 2) . '.',
                route('fines.index'),
                external: true
            );
        }

        AuditLogger::log('Recorded Penalty Payment', $record, "₱{$amount} payment for '{$bookTitle}' — user balance ₱{$balanceBefore} → ₱{$newBalance}" . ($fullySettled ? ' (fully settled)' : ''));

        $message = $fullySettled
            ? "Payment recorded. Penalty for '{$bookTitle}' is now fully settled."
            : "Payment of ₱" . number_format($amount, 2) . " recorded for '{$bookTitle}'. Remaining: ₱" . number_format($record->fineRemaining(), 2) . '.';

        return redirect()->back()->with('status', $message);
    }

    /**
     * Super Admin oversight action — adjust or waive a penalty's total amount
     * (spec #22-23). Never silent: requires an explicit reason and is fully
     * audited with the before/after amounts.
     */
    public function adjustPenalty(Request $request, BorrowRecord $record)
    {
        $validated = $request->validate([
            'new_amount' => ['required_without:waive', 'nullable', 'numeric', 'min:0', 'max:99999'],
            'reason' => ['required', 'string', 'max:255'],
            'waive' => ['nullable', 'boolean'],
        ]);

        $bookTitle = $record->book->title ?? 'the book';
        $previousAmount = (float) $record->fine_amount;
        $submittedAmount = round((float) ($validated['new_amount'] ?? 0), 2);
        $waive = $request->boolean('waive');

        try {
            // The amount actually saved — when $waive is true this is NOT
            // necessarily $submittedAmount (see PenaltyService::adjustPenalty):
            // waiving always forces the remaining balance to ₱0, regardless
            // of whatever was left in the New Amount field. Every message
            // below uses this returned value, never the raw submission, so
            // what the borrower is told always matches what was actually
            // saved to the database.
            $newAmount = PenaltyService::adjustPenalty($record, $submittedAmount, $waive);
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        AuditLogger::log(
            $waive ? 'Waived Penalty' : 'Adjusted Penalty',
            $record,
            "'{$bookTitle}': ₱" . number_format($previousAmount, 2) . ' -> ₱' . number_format($newAmount, 2) . ". Reason: {$validated['reason']}"
        );

        Notifier::send(
            $record->user,
            'penalty',
            $waive ? 'Penalty waived' : 'Penalty adjusted',
            $waive
                ? "Your penalty for '{$bookTitle}' has been waived by a library administrator."
                : "Your penalty for '{$bookTitle}' has been adjusted to ₱" . number_format($newAmount, 2) . ' by a library administrator.',
            route('fines.index')
        );

        return redirect()->back()->with('status', $waive
            ? "Penalty for '{$bookTitle}' has been waived."
            : "Penalty for '{$bookTitle}' adjusted from ₱" . number_format($previousAmount, 2) . ' to ₱' . number_format($newAmount, 2) . '.');
    }
}
