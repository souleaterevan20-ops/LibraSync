<?php

namespace App\Services;

use App\Models\BorrowRecord;
use App\Models\User;
use Carbon\Carbon;

/**
 * Centralized overdue penalty logic. Every place that can charge an overdue
 * fine (the manual "Mark Overdue" / "Sync Penalty" button, the automatic
 * daily scheduler, and the return-book flow) must call syncOverdueCharge()
 * instead of computing fines/points itself, so the calculation only ever
 * lives in one place and running it repeatedly never double-charges
 * (spec #24, #26, #27).
 */
class PenaltyService
{
    public const RATE_PER_DAY = 10; // ₱10 per overdue day
    public const POINTS_PER_DAY = 10; // -10 leaderboard points per overdue day

    /**
     * How many whole days late this record currently is (0 if not overdue).
     */
   public static function daysLate(BorrowRecord $record): int
{
    if (! $record->due_at) {
        return 0;
    }

    $dueDate = Carbon::parse($record->due_at)->startOfDay();
    $today = now()->startOfDay();

       return $today->gt($dueDate) ? $today->diffInDays($dueDate, true) : 0;
}
    public static function expectedFine(int $daysLate): float
    {
        return $daysLate * self::RATE_PER_DAY;
    }
    /**
 * The fine to show/use right now for this record — frozen at whatever was
 * charged if the book's already been returned, otherwise calculated live
 * from today's date. This is the ONE place "what's the current fine"
 * gets decided — nothing else should re-implement this check.
 */
public static function currentFine(BorrowRecord $record): float
{
    if ($record->status === 'returned') {
        return (float) $record->fine_amount;
    }

    return self::expectedFine(self::daysLate($record));
}

    public static function expectedPointDeduction(int $daysLate): int
    {
        return $daysLate * self::POINTS_PER_DAY;
    }

    /**
     * Bring a borrow record's charged penalty/points up to date with how many
     * days late it currently is, charging only the difference since the last
     * time this ran. Safe to call as many times as you like (manual button,
     * daily scheduler, or right before finalizing a return) — it will never
     * charge twice for the same overdue day.
     *
     * Returns null if the record isn't currently overdue or nothing new is
     * owed; otherwise returns ['fine' => float, 'points' => int, 'days_late' => int]
     * describing what was newly charged this call.
     */
    public static function syncOverdueCharge(BorrowRecord $record, bool $deductPoints = true): ?array
    {
        if ($record->status !== 'borrowed') {
            return null;
        }

        $daysLate = self::daysLate($record);

        if ($daysLate <= 0) {
            return null;
        }

        $expectedFine = self::expectedFine($daysLate);
        $expectedPoints = self::expectedPointDeduction($daysLate);

        $alreadyChargedFine = (float) ($record->overdue_charged_amount ?? 0);
        $alreadyChargedPoints = (int) ($record->overdue_points_deducted ?? 0);

        $newFine = max(0, $expectedFine - $alreadyChargedFine);
        $newPoints = max(0, $expectedPoints - $alreadyChargedPoints);

        if ($newFine <= 0 && $newPoints <= 0) {
            return null;
        }

        $record->update([
            'overdue_marked_at' => $record->overdue_marked_at ?? now(),
            'overdue_charged_amount' => $expectedFine,
            'overdue_points_deducted' => $expectedPoints,
            'fine_amount' => $expectedFine,
            'fine_status' => 'unpaid',
        ]);

        if ($newFine > 0) {
            $record->user->increment('penalty_balance', $newFine);
        }

        if ($newPoints > 0 && $deductPoints) {
            LeaderboardService::deduct($record->user, $newPoints, "Overdue day(s) — '{$record->book->title}'");
        }

        return ['fine' => $newFine, 'points' => $newPoints, 'days_late' => $daysLate];
    }

    /**
     * Super Admin oversight action (spec #22-23): directly adjust or waive a
     * penalty's total amount — distinct from a routine payment, which only
     * ever records money actually collected. Never silent: always requires a
     * reason, and the before/after amounts are fully audited by the caller.
     *
     * @throws \InvalidArgumentException if the new amount is less than what's
     *         already been paid (which would make the remaining balance negative).
     */
    /**
     * $newAmount is ignored when $waive is true — "Waive completely" means
     * exactly that: the remaining balance becomes ₱0 no matter what was
     * left in the New Amount field. Previously this method trusted
     * $newAmount even when $waive was true, which could produce a record
     * marked "Waived" while still carrying a real remaining balance that
     * silently stayed on the user's outstanding penalty total — a status
     * that says "waived" while still charging the user isn't a logically
     * possible state, so the checkbox is now authoritative over the amount.
     */
    public static function adjustPenalty(BorrowRecord $record, float $newAmount, bool $waive = false): float
    {
        if ($waive) {
            $newAmount = (float) $record->fine_paid_amount;
        } elseif ($newAmount < (float) $record->fine_paid_amount) {
            throw new \InvalidArgumentException(
                "Cannot set the penalty below the ₱" . number_format($record->fine_paid_amount, 2) . ' already paid on this record.'
            );
        }

        $record->update(['fine_amount' => $newAmount]);
        $record->update(['fine_status' => $waive ? 'waived' : $record->computedFineStatus()]);

        // The user's penalty balance is always recalculated from actual
        // remaining amounts across every one of their records (spec #38),
        // never adjusted directly — so it stays correct automatically here
        // too. Waived records are explicitly excluded even though
        // fineRemaining() is now always 0 for them after the fix above —
        // this extra check is a second layer of protection against any
        // other code path that might one day set fine_amount on a waived
        // record without going through this method.
        $user = $record->user;
        $newBalance = (float) $user->borrowRecords()
            ->where('fine_amount', '>', 0)
            ->where('fine_status', '!=', 'waived')
            ->get()
            ->sum(fn ($r) => $r->fineRemaining());
        $user->update(['penalty_balance' => $newBalance]);

        return $newAmount;
    }
}
