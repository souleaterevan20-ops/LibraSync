<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\BorrowRecord;
use Illuminate\Support\Facades\Auth;

class FinesController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $activeLoans = BorrowRecord::with('book')
            ->where('user_id', $user->id)
            ->where('status', 'borrowed')
            ->get()
            ->map(function ($record) {
                return [
                    'title' => $record->book->title ?? 'Unknown title',
                    'due_at' => $record->due_at,
                    'fine' => $record->calculateCurrentFine(),
                ];
            })
            ->filter(fn ($r) => $r['fine'] > 0)
            ->values();

        $pastFines = BorrowRecord::with('book')
            ->where('user_id', $user->id)
            ->where('fine_amount', '>', 0)
            ->orderByDesc('returned_at')
            ->take(10)
            ->get();

        return view('student.fines', [
            'activeLoans' => $activeLoans,
            'pastFines' => $pastFines,
            'penaltyBalance' => (float) $user->penalty_balance,
        ]);
    }
}
