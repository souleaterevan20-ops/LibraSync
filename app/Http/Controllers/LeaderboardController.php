<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class LeaderboardController extends Controller
{
    public function index()
    {
        $ranked = User::whereIn('role', ['student', 'teacher'])
            ->orderByDesc('points')
            ->get()
            ->values();

        $me = Auth::user();
        $isStaff = in_array($me->role, ['super_admin', 'student_assistant'], true);

        $rows = $ranked->map(function (User $user, int $index) use ($me, $isStaff) {
            $isSelf = $user->id === $me->id;

            return [
                'rank' => $index + 1,
                // Staff (Super Admin / Library Staff) can see full names for moderation.
                // Students/Teachers only ever see their own name; everyone else shows as initials.
                'name' => ($isSelf || $isStaff) ? $user->name : $this->initials($user->name),
                'is_self' => $isSelf,
                'points' => $user->points,
            ];
        });

        $myRank = $rows->firstWhere('is_self', true)['rank'] ?? null;

        return view('leaderboard.index', compact('rows', 'myRank'));
    }

    private function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name));

        return collect($parts)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)).'.')->implode(' ');
    }
}
