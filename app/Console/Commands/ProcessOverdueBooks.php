<?php

namespace App\Console\Commands;

use App\Models\BorrowRecord;
use App\Services\AuditLogger;
use App\Services\Notifier;
use App\Services\PenaltyService;
use Illuminate\Console\Command;

class ProcessOverdueBooks extends Command
{
    protected $signature = 'library:process-overdue-books';

    protected $description = 'Daily job: find active borrowed books past their due date, charge the ₱10/day penalty '
        . 'and -10/day leaderboard points via the same logic as the manual "Mark Overdue" button, and notify borrowers. '
        . 'Safe to run more than once a day — only the newly-accrued difference is ever charged.';

    public function handle(): void
    {
        $overdueLoans = BorrowRecord::with(['user', 'book'])
            ->where('status', 'borrowed')
            ->where('due_at', '<', now())
            ->get();

        $charged = 0;
        $skipped = 0;

        foreach ($overdueLoans as $record) {
            $charge = PenaltyService::syncOverdueCharge($record);

            if (! $charge) {
                $skipped++;
                continue;
            }

            $charged++;

            // Spec #53: "Do not send 10 SMS... DAY 1: OVERDUE SMS. Then
            // optionally DAY 3: REMINDER. Then controlled reminders." The
            // in-app notification still fires every day (useful, low-cost,
            // and expected), but the external channel — the one that costs
            // money per SMS and can feel like spam — only fires on day 1
            // and day 3 of being overdue, not daily forever.
            $sendExternal = in_array($charge['days_late'], [1, 3], true);

            Notifier::send(
                $record->user,
                'overdue',
                'Book overdue',
                "Your book '{$record->book->title}' is {$charge['days_late']} day(s) overdue. A ₱" . number_format($charge['fine'], 2) . ' penalty has been applied.',
                route('fines.index'),
                external: $sendExternal
            );

            AuditLogger::log(
                'Automatic Overdue Processing',
                $record,
                "Charged ₱{$charge['fine']} penalty and -{$charge['points']} points for '{$record->book->title}' ({$charge['days_late']} day(s) late)."
            );
        }

        $this->info("Processed {$overdueLoans->count()} overdue loan(s): {$charged} newly charged, {$skipped} already up to date.");
    }
}
