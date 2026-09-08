<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\BorrowRecord;
use App\Models\Book;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Report types every role may generate, and the extra ones Super Admin alone can see.
     */
    private const BASE_REPORTS = [
        'borrow' => 'Borrowing Report',
        'return' => 'Returns Report',
        'overdue' => 'Overdue Report',
        'inventory' => 'Books Report',
        'penalty' => 'Penalties Report',
        'payments' => 'Payments Report',
    ];

    private const ADMIN_ONLY_REPORTS = [
        'users' => 'Users Report',
        'leaderboard' => 'Leaderboard Report',
        'audit' => 'Audit Logs',
    ];

    public function index()
    {
        $reports = self::BASE_REPORTS;
        if (Auth::user()->role === 'super_admin') {
            $reports = array_merge($reports, self::ADMIN_ONLY_REPORTS);
        }

        return view('reports.index', compact('reports'));
    }

    public function show(Request $request, string $type)
    {
        $this->authorizeReport($type);

        [$rows, $columns] = $this->buildReport($type, $request);

        $label = (self::BASE_REPORTS + self::ADMIN_ONLY_REPORTS)[$type];

        return view('reports.show', compact('rows', 'columns', 'label', 'type'));
    }

    public function export(Request $request, string $type): StreamedResponse
    {
        $this->authorizeReport($type);

        [$rows, $columns] = $this->buildReport($type, $request);

        $filename = "{$type}-report-".now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($rows, $columns) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $columns);
            foreach ($rows as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function authorizeReport(string $type): void
    {
        if (array_key_exists($type, self::ADMIN_ONLY_REPORTS) && Auth::user()->role !== 'super_admin') {
            abort(403, 'Only the Super Admin can generate this report.');
        }

        if (!array_key_exists($type, self::BASE_REPORTS) && !array_key_exists($type, self::ADMIN_ONLY_REPORTS)) {
            abort(404);
        }
    }

    private function buildReport(string $type, Request $request): array
    {
        $from = $request->filled('from') ? Carbon::parse($request->input('from'))->startOfDay() : now()->subDays(30)->startOfDay();
        $to = $request->filled('to') ? Carbon::parse($request->input('to'))->endOfDay() : now()->endOfDay();

        return match ($type) {
            'borrow' => $this->borrowReport($from, $to),
            'return' => $this->returnReport($from, $to),
            'overdue' => $this->overdueReport(),
            'inventory' => $this->inventoryReport(),
            'penalty' => $this->penaltyReport(),
            'payments' => $this->paymentsReport($from, $to),
            'users' => $this->usersReport(),
            'leaderboard' => $this->leaderboardReport(),
            'audit' => $this->auditReport($from, $to),
        };
    }

    private function overdueReport(): array
    {
        $records = BorrowRecord::with(['user', 'book'])
            ->where('status', 'borrowed')
            ->where('due_at', '<', now())
            ->when($this->scopedToOwnRecords(), fn ($q) => $q->where('user_id', Auth::id()))
            ->get();

        $rows = $records->map(fn ($r) => [
            $r->user->name ?? 'Deleted User', $r->book->title ?? 'Unknown', optional($r->due_at)->format('Y-m-d'),
            (int) $r->due_at->diffInDays(now()), '₱' . number_format($r->calculateCurrentFine(), 2),
        ])->all();

        return [$rows, ['User', 'Book', 'Due Date', 'Days Overdue', 'Current Fine']];
    }

    private function paymentsReport(Carbon $from, Carbon $to): array
    {
        $payments = \App\Models\PenaltyPayment::with(['user', 'borrowRecord.book', 'recordedBy'])
            ->whereBetween('created_at', [$from, $to])
            ->when($this->scopedToOwnRecords(), fn ($q) => $q->where('user_id', Auth::id()))
            ->latest()
            ->get();

        $rows = $payments->map(fn ($p) => [
            $p->user->name ?? 'Deleted User', $p->borrowRecord->book->title ?? '—', '₱' . number_format($p->amount, 2),
            ucfirst($p->payment_method), $p->recordedBy->name ?? 'System', $p->created_at->format('Y-m-d H:i'),
        ])->all();

        return [$rows, ['User', 'Book', 'Amount', 'Method', 'Recorded By', 'Date']];
    }

    private function usersReport(): array
    {
        $users = User::orderBy('role')->orderBy('name')->get();

        $rows = $users->map(fn ($u) => [
            $u->name, $u->email, $u->roleLabel(),
            $u->is_active ? 'Active' : 'Disabled', $u->is_verified ? 'Verified' : 'Pending', $u->points, '₱' . number_format($u->penalty_balance, 2),
        ])->all();

        return [$rows, ['Name', 'Email', 'Role', 'Account Status', 'Verification', 'Points', 'Penalty Balance']];
    }

    private function borrowReport(Carbon $from, Carbon $to): array
    {
        $records = BorrowRecord::with(['user', 'book'])
            ->whereBetween('borrowed_at', [$from, $to])
            ->when($this->scopedToOwnRecords(), fn ($q) => $q->where('user_id', Auth::id()))
            ->get();

        $rows = $records->map(fn ($r) => [
            $r->user->name, $r->book->title, optional($r->borrowed_at)->format('Y-m-d'), optional($r->due_at)->format('Y-m-d'), $r->status,
        ])->all();

        return [$rows, ['User', 'Book', 'Borrowed At', 'Due At', 'Status']];
    }

    private function returnReport(Carbon $from, Carbon $to): array
    {
        $records = BorrowRecord::with(['user', 'book'])
            ->whereNotNull('returned_at')
            ->whereBetween('returned_at', [$from, $to])
            ->when($this->scopedToOwnRecords(), fn ($q) => $q->where('user_id', Auth::id()))
            ->get();

        $rows = $records->map(fn ($r) => [
            $r->user->name, $r->book->title, optional($r->returned_at)->format('Y-m-d'), $r->condition, '₱'.number_format($r->fine_amount, 2),
        ])->all();

        return [$rows, ['User', 'Book', 'Returned At', 'Condition', 'Fine']];
    }

    private function penaltyReport(): array
    {
        $records = BorrowRecord::with(['user', 'book'])
            ->where('fine_amount', '>', 0)
            ->when($this->scopedToOwnRecords(), fn ($q) => $q->where('user_id', Auth::id()))
            ->latest('updated_at')
            ->get();

        $rows = $records->map(fn ($r) => [
            $r->user->name ?? 'Deleted User', $r->book->title ?? 'Unknown', $r->penaltyType(),
            '₱' . number_format($r->fine_amount, 2), '₱' . number_format($r->fine_paid_amount, 2),
            '₱' . number_format($r->fineRemaining(), 2), str_replace('_', ' ', $r->computedFineStatus()),
        ])->all();

        return [$rows, ['User', 'Book', 'Type', 'Penalty', 'Paid', 'Remaining', 'Status']];
    }

    /**
     * Spec #56 ("Do not trust frontend role values"): the base reports
     * (borrow/return/overdue/penalty/payments) used to return EVERY user's
     * records to whoever asked — meaning any logged-in Student or Teacher
     * could see every other student's overdue books, unpaid penalties, and
     * payment history just by visiting /reports/overdue or /reports/payments
     * directly. Super Admin and Library Staff keep full library-wide
     * visibility (they already have it everywhere else in the app);
     * Student/Teacher now only ever see their own records here.
     */
    private function scopedToOwnRecords(): bool
    {
        return ! in_array(Auth::user()->role, ['super_admin', 'student_assistant'], true);
    }

    private function inventoryReport(): array
    {
        $books = Book::all();

        $rows = $books->map(fn (Book $b) => [
            $b->title, $b->author, $b->category ?? $b->genre, $b->type, $b->total_copies, $b->available_copies, $b->borrowedCopies(),
        ])->all();

        return [$rows, ['Title', 'Author', 'Category', 'Circulation Type', 'Total Copies', 'Available', 'Borrowed']];
    }

    private function leaderboardReport(): array
    {
        $users = User::whereIn('role', ['student', 'teacher'])->orderByDesc('points')->get();

        $rows = $users->values()->map(fn ($u, $i) => [$i + 1, $u->name, $u->role, $u->points])->all();

        return [$rows, ['Rank', 'Name', 'Role', 'Points']];
    }

    private function auditReport(Carbon $from, Carbon $to): array
    {
        $logs = AuditLog::whereBetween('created_at', [$from, $to])->latest()->get();

        $rows = $logs->map(fn ($l) => [$l->actor_name, $l->actor_role, $l->action, $l->description, $l->created_at->format('Y-m-d H:i')])->all();

        return [$rows, ['Actor', 'Role', 'Action', 'Description', 'Date']];
    }
}
