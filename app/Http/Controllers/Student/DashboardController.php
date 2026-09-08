<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BorrowRecord;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $borrowedRecords = BorrowRecord::where('user_id', $user->id)
            ->where('status', 'borrowed')
            ->with('book')
            ->get();

        $currentlyBorrowed = $borrowedRecords->count();
        $dueSoon = $borrowedRecords->filter(fn ($r) => Carbon::parse($r->due_at)->isFuture() && Carbon::parse($r->due_at)->diffInDays(now()) <= 2)->count();
        $overdue = $borrowedRecords->filter(fn ($r) => Carbon::parse($r->due_at)->isPast())->count();

        $currentPenalty = (float) $user->penalty_balance;
        $points = (int) $user->points;

        // Weekly borrowing activity (last 6 weeks) — real, from this user's records
        $weeks = collect(range(5, 0))->map(function ($i) use ($user) {
            $start = Carbon::now()->subWeeks($i)->startOfWeek();
            $end = (clone $start)->endOfWeek();
            return [
                'label' => $start->format('M j'),
                'borrowed' => BorrowRecord::where('user_id', $user->id)->whereBetween('borrowed_at', [$start, $end])->count(),
                'returned' => BorrowRecord::where('user_id', $user->id)->whereNotNull('returned_at')->whereBetween('returned_at', [$start, $end])->count(),
            ];
        });

        $booksThisMonth = BorrowRecord::where('user_id', $user->id)
            ->whereMonth('borrowed_at', now()->month)->whereYear('borrowed_at', now()->year)->count();

        $newArrivals = Book::latest()->take(5)->get();

        $topUsers = \App\Models\User::whereIn('role', ['student', 'teacher'])
            ->orderByDesc('points')->take(5)->get();

        $alreadyBorrowedBookIds = BorrowRecord::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'borrowed'])->pluck('book_id');

        $availableBooks = Book::where('available_copies', '>', 0)
            ->whereNotIn('id', $alreadyBorrowedBookIds)
            ->inRandomOrder()->take(3)->get();

        // Suggested Books — based on genres this user has borrowed before.
        $borrowedGenres = BorrowRecord::where('user_id', $user->id)
            ->with('book')
            ->get()
            ->pluck('book.genre')
            ->filter()
            ->unique();

        $suggestedBooks = $borrowedGenres->isNotEmpty()
            ? Book::whereIn('genre', $borrowedGenres)
                ->whereNotIn('id', $alreadyBorrowedBookIds)
                ->where('available_copies', '>', 0)
                ->inRandomOrder()->take(5)->get()
            : Book::where('available_copies', '>', 0)->whereNotIn('id', $alreadyBorrowedBookIds)->inRandomOrder()->take(5)->get();

        // Most Borrowed Books — LibraSync doesn't have a reservation system yet,
        // so this stands in for "Most Reserved" using historical borrow counts.
        $mostBorrowedBooks = Book::withCount('borrowRecords')
            ->having('borrow_records_count', '>', 0)
            ->orderByDesc('borrow_records_count')
            ->take(5)
            ->get();

        return view('student.dashboard', [
            'currentlyBorrowed' => $currentlyBorrowed,
            'dueSoon' => $dueSoon,
            'overdue' => $overdue,
            'currentPenalty' => $currentPenalty,
            'points' => $points,
            'borrowedRecords' => $borrowedRecords,
            'weeks' => $weeks,
            'booksThisMonth' => $booksThisMonth,
            'newArrivals' => $newArrivals,
            'topUsers' => $topUsers,
            'availableBooks' => $availableBooks,
            'suggestedBooks' => $suggestedBooks,
            'mostBorrowedBooks' => $mostBorrowedBooks,
            'unreadNotifications' => Auth::user()->unreadNotifications()->count(),
        ]);
    }
}
