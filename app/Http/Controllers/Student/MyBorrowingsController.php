<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\BorrowRecord;
use Illuminate\Support\Facades\Auth;

class MyBorrowingsController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $active = BorrowRecord::with('book')
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending', 'borrowed'])
            ->orderBy('due_at')
            ->get();

        $history = BorrowRecord::with('book')
            ->where('user_id', $user->id)
            ->whereIn('status', ['returned', 'lost', 'damaged'])
            ->orderByDesc('returned_at')
            ->paginate(15);

        return view('student.my-borrowings', compact('active', 'history'));
    }
}
