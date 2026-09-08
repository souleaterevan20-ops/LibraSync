<?php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BorrowRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $totalBookCopies = (int) Book::sum('total_copies');
        $totalAvailable = (int) Book::sum('available_copies');
        $totalBorrowed = max($totalBookCopies - $totalAvailable, 0);
        // Spec #81/#82: this card is labeled "Active Users", so it must mean
        // the same thing "Total Users" means on the Super Admin dashboard —
        // verified AND enabled. Previously this only checked is_verified,
        // so a disabled Student/Teacher (is_active=false) still counted as
        // "active" here even though the same account correctly does NOT
        // count toward Super Admin's Total Users — two dashboards silently
        // disagreeing about what "active" means for the same accounts.
        $activeUsers = User::whereIn('role', ['student', 'teacher'])
            ->where('is_verified', true)->where('is_active', true)->count();
        $overdueItems = BorrowRecord::where('status', 'borrowed')->where('due_at', '<', now())->count();
        // Spec #77/#81: a rejected registration is not "pending" anymore —
        // exclude it so this count only reflects registrations genuinely
        // awaiting a decision.
        $pendingApprovals = User::where('is_verified', false)->whereNull('rejected_at')->count();

        $today = Carbon::today();
        $booksBorrowedToday = BorrowRecord::whereDate('borrowed_at', $today)->count();
        $booksReturnedToday = BorrowRecord::whereNotNull('returned_at')->whereDate('returned_at', $today)->count();
        // Spec #29: "Today's Transactions" must count only Student/Teacher
        // registrations — Super Admin and Library Staff accounts (created via
        // seeding or internal admin actions) should never inflate this figure.
        $newUsersToday = User::whereIn('role', ['student', 'teacher'])
            ->whereDate('created_at', $today)
            ->count();

        $weeks = collect(range(5, 0))->map(function ($i) {
            $start = Carbon::now()->subWeeks($i)->startOfWeek();
            $end = (clone $start)->endOfWeek();
            return [
                'label' => $start->format('M j'),
                'borrowed' => BorrowRecord::whereBetween('borrowed_at', [$start, $end])->count(),
                'returned' => BorrowRecord::whereNotNull('returned_at')->whereBetween('returned_at', [$start, $end])->count(),
            ];
        });

        $upcomingDue = BorrowRecord::where('status', 'borrowed')
            ->whereBetween('due_at', [now(), now()->addDays(3)])
            ->with(['user', 'book'])
            ->orderBy('due_at')
            ->take(5)
            ->get();

        $recentTransactions = BorrowRecord::with(['user', 'book'])
            ->latest('updated_at')
            ->take(5)
            ->get();

        $totalUnpaidPenalties = (float) User::sum('penalty_balance');
        $usersWithPenalties = User::where('penalty_balance', '>', 0)->count();
        $overdueFines = (float) BorrowRecord::where('fine_status', 'unpaid')->sum('fine_amount');

        // Calendar: every active loan's due date, this month, grouped by day.
        $calendarLoans = BorrowRecord::with(['user', 'book'])
            ->where('status', 'borrowed')
            ->whereBetween('due_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->get()
            ->groupBy(fn ($r) => \Carbon\Carbon::parse($r->due_at)->format('Y-m-d'))
            ->map(fn ($records) => $records->map(fn ($r) => [
                'borrower' => $r->user->name ?? 'Unknown',
                'book' => $r->book->title ?? 'Unknown',
                'borrowed_at' => optional($r->borrowed_at)->format('M d, Y'),
                'due_at' => optional($r->due_at)->format('M d, Y g:i A'),
            ]));

        return view('assistant.dashboard', [
            'totalBookCopies' => $totalBookCopies,
            'totalAvailable' => $totalAvailable,
            'totalBorrowed' => $totalBorrowed,
            'activeUsers' => $activeUsers,
            'overdueItems' => $overdueItems,
            'pendingApprovals' => $pendingApprovals,
            'booksBorrowedToday' => $booksBorrowedToday,
            'booksReturnedToday' => $booksReturnedToday,
            'newUsersToday' => $newUsersToday,
            'weeks' => $weeks,
            'upcomingDue' => $upcomingDue,
            'recentTransactions' => $recentTransactions,
            'totalUnpaidPenalties' => $totalUnpaidPenalties,
            'usersWithPenalties' => $usersWithPenalties,
            'overdueFines' => $overdueFines,
            'calendarLoans' => $calendarLoans,
            'unreadNotifications' => Auth::user()->unreadNotifications()->count(),
        ]);
    }
}
