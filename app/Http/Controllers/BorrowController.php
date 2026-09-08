<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BorrowRecord;
use Illuminate\Http\Request;

class BorrowController extends Controller
{
    /**
     * Display the catalog for students to browse and request books.
     */
    public function catalog(Request $request)
    {
        $search = $request->input('search');

        // Search books by title, author, or genre
        $books = Book::when($search, function ($query, $search) {
            return $query->where('title', 'like', "%{$search}%")
                         ->orWhere('author', 'like', "%{$search}%")
                         ->orWhere('genre', 'like', "%{$search}%");
        })->get();

        // Get the logged-in user's active borrow requests/records
        $myBorrows = BorrowRecord::where('user_id', auth()->id())
            ->with('book')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('catalog', compact('books', 'myBorrows', 'search'));
    }

    /**
     * Create a pending borrow request for a book.
     */
    public function requestBook(Book $book)
    {
        // 1. Check if the book is "Reference" type (Reference books cannot leave the library)
        if ($book->type === 'reference') {
            return redirect()->back()->with('error', "Reference books like '{$book->title}' can only be read inside the library.");
        }

        // 2. Check if copies are available
        if ($book->available_copies < 1) {
            return redirect()->back()->with('error', "Sorry, there are currently no available copies of '{$book->title}'.");
        }

        // 3. Check if user already has an active or pending request for this specific book
        $existing = BorrowRecord::where('user_id', auth()->id())
            ->where('book_id', $book->id)
            ->whereIn('status', ['pending', 'borrowed'])
            ->exists();

        if ($existing) {
            return redirect()->back()->with('error', "You already have an active request or loan for '{$book->title}'.");
        }

        // 4. Create the request
BorrowRecord::create([
    'user_id' => auth()->id(),
    'book_id' => $book->id,
    'status' => 'pending',
    'borrowed_at' => now(),
    'due_at' => now()->addDays(7), // 7-day borrowing period
]);

        // Only Library Staffs manage circulation now — notify them.
        \App\Services\Notifier::sendToRole(
            ['student_assistant'],
            'borrow_request',
            'New borrow request submitted',
            auth()->user()->name . " requested to borrow '{$book->title}'.",
            route('admin.borrows.index')
        );

        return redirect()->back()->with('status', "Your request to borrow '{$book->title}' has been sent to admin for approval!");
    }
}