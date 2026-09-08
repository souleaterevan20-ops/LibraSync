<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = Book::query();
        $query->when($search, fn ($q) => $q->where('title', 'like', "%{$search}%")->orWhere('isbn', 'like', "%{$search}%"));

        $books = $query->orderBy('title')->paginate(25)->withQueryString();

        // Stats are computed from the whole catalog, not just the current page,
        // so the summary cards stay accurate regardless of pagination/search.
        $stats = [
            'titles' => Book::count(),
            'total_copies' => (int) Book::sum('total_copies'),
            'available_copies' => (int) Book::sum('available_copies'),
            'lost_copies' => (int) Book::sum('lost_copies'),
            'damaged_copies' => (int) Book::sum('damaged_copies'),
            'low_stock_titles' => Book::where('available_copies', 0)->where('total_copies', '>', 0)->count(),
        ];

        return view('admin.inventory', compact('books', 'stats', 'search'));
    }
}