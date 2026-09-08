<?php

namespace App\Services;

use App\Models\User;
use App\Services\AuditLogger;

/**
 * Single source of truth for every leaderboard point change in the system.
 * No controller should call ->increment('points', ...) / ->decrement('points', ...)
 * directly — always go through here so every deduction is floored at zero and
 * every change is logged with a consistent reason (spec #22-24).
 */
class LeaderboardService
{
    /**
     * Add points to a user (e.g. +10 for an on-time return).
     */
    public static function award(User $user, int $points, string $reason): void
    {
        if ($points <= 0) {
            return;
        }

        $user->increment('points', $points);

        AuditLogger::log('Leaderboard Points Awarded', $user, "+{$points} — {$reason}");
    }

    /**
     * Deduct points from a user, never letting the total go below zero
     * (e.g. -10 per overdue day, -20 for lost/damaged).
     */
    public static function deduct(User $user, int $points, string $reason): void
    {
        if ($points <= 0) {
            return;
        }

        $user->refresh();
        $newTotal = max(0, $user->points - $points);
        $actualDeduction = $user->points - $newTotal;

        $user->update(['points' => $newTotal]);

        if ($actualDeduction > 0) {
            AuditLogger::log('Leaderboard Points Deducted', $user, "-{$actualDeduction} — {$reason}");
        }
    }
}
