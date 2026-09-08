<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BorrowRecord;
use App\Models\PenaltyPayment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Super Admin dashboard — real counts pulled straight from the DB.
     */
    public function index()
    {
        // ---- Top stat cards -------------------------------------------------
        $totalBookCopies = (int) Book::sum('total_copies');
        $totalAvailable  = (int) Book::sum('available_copies');
        $totalBorrowed   = max($totalBookCopies - $totalAvailable, 0);
        // Spec #81: "Total Users" must count only approved, active Student/
        // Teacher accounts — pending, rejected, and soft-deleted accounts
        // are never counted here. Disabled accounts are broken out
        // separately rather than silently folded into (or excluded from)
        // the headline number, so the stat is never ambiguous.
        $activeStudentTeacher = User::whereIn('role', ['student', 'teacher'])
            ->where('is_verified', true)->where('is_active', true)->count();
        $disabledStudentTeacher = User::whereIn('role', ['student', 'teacher'])
            ->where('is_verified', true)->where('is_active', false)->count();
        $totalUsers = $activeStudentTeacher;
        $pendingRegistrations = User::where('is_verified', false)->whereNull('rejected_at')->count();

        // ---- Books stock donut ----------------------------------------------
        $reservedCopies = 0; // no reservation feature yet
        $stock = [
            'available' => $totalAvailable,
            'borrowed'  => $totalBorrowed,
            'reserved'  => $reservedCopies,
            'total'     => max($totalBookCopies, 1),
        ];

        // ---- Borrowings overview (last 6 weeks) ------------------------------
        $weeks = collect(range(5, 0))->map(function ($i) {
            $start = Carbon::now()->subWeeks($i)->startOfWeek();
            $end = (clone $start)->endOfWeek();

            return [
                'label'    => $start->format('M j'),
                'borrowed' => BorrowRecord::whereBetween('borrowed_at', [$start, $end])->count(),
                'returned' => BorrowRecord::whereNotNull('returned_at')
                    ->whereBetween('returned_at', [$start, $end])->count(),
            ];
        });

        // ---- System summary ---------------------------------------------------
        $activeStudentAssistants = User::where('role', 'student_assistant')
            ->where('is_verified', true)->count();

        $newBooksThisMonth = Book::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)->count();

        $overdueBooks = BorrowRecord::where('status', 'borrowed')
            ->where('due_at', '<', now())->count();

        $pendingBorrowRequests = BorrowRecord::where('status', 'pending')->count();
        $unreadNotifications = Auth::user()->unreadNotifications()->count();

        // ---- Penalty summary (feeds the Outstanding/Collected Penalties cards) ---
        $outstandingPenalties = (float) User::sum('penalty_balance');
        $collectedPenalties = (float) PenaltyPayment::sum('amount');

        // ---- Latest borrowings table -------------------------------------------
        $latestBorrowings = BorrowRecord::with(['user', 'book'])
            ->latest('borrowed_at')
            ->take(5)
            ->get()
            ->map(function ($record) {
                $status = $record->status;
                if ($status === 'borrowed' && $record->due_at && Carbon::parse($record->due_at)->isPast()) {
                    $status = 'overdue';
                }
                return [
                    'user'   => $record->user,
                    'book'   => $record->book,
                    'date'   => $record->borrowed_at,
                    'due'    => $record->due_at,
                    'status' => $status,
                ];
            });

        // ---- Top 5 active users (by points) -------------------------------------
        $topUsers = User::whereIn('role', ['student', 'teacher'])
            ->orderByDesc('points')
            ->take(5)
            ->get();

        return view('admin.dashboard', [
            'totalBookCopies' => $totalBookCopies,
            'totalAvailable' => $totalAvailable,
            'totalBorrowed' => $totalBorrowed,
            'totalUsers' => $totalUsers,
            'disabledStudentTeacher' => $disabledStudentTeacher,
            'pendingRegistrations' => $pendingRegistrations,
            'stock' => $stock,
            'weeks' => $weeks,
            'activeStudentAssistants' => $activeStudentAssistants,
            'newBooksThisMonth' => $newBooksThisMonth,
            'overdueBooks' => $overdueBooks,
            'pendingBorrowRequests' => $pendingBorrowRequests,
            'outstandingPenalties' => $outstandingPenalties,
            'collectedPenalties' => $collectedPenalties,
            'unreadNotifications' => $unreadNotifications,
            'latestBorrowings' => $latestBorrowings,
            'topUsers' => $topUsers,
        ]);
    }
}
