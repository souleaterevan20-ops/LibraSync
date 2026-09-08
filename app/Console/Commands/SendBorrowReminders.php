<?php

namespace App\Console\Commands;

use App\Models\BorrowRecord;
use App\Services\Notifier;
use Illuminate\Console\Command;

class SendBorrowReminders extends Command
{
    protected $signature = 'library:send-borrow-reminders';

    protected $description = 'Notify users of books due in 2 days. Overdue reminders are sent by library:process-overdue-books instead, to avoid duplicate "overdue" notifications.';

    /**
     * NOTE: The "overdue" reminder used to be sent from here as well as from
     * library:process-overdue-books (scheduled daily at 00:05). Both run
     * every day, so every overdue borrower was getting TWO "overdue"
     * notifications daily — this one with no throttling at all (spec #42,
     * #43: unique-event identity per notification; spec #53: no spam).
     *
     * ProcessOverdueBooks is the correct single source for this notification:
     * it only fires when PenaltyService actually applies a new day's charge,
     * and it includes the real amount. So the duplicate loop was removed
     * here — this command now only handles the 2-day-ahead "due soon"
     * reminder, which doesn't overlap with anything else.
     */
    public function handle(): void
    {
        $dueSoon = BorrowRecord::with(['user', 'book'])
            ->where('status', 'borrowed')
            ->whereDate('due_at', now()->addDays(2)->toDateString())
            ->get();

        foreach ($dueSoon as $record) {
            Notifier::send($record->user, 'due_soon', 'Book due in 2 days', "Reminder: '{$record->book->title}' is due on {$record->due_at->format('M j, Y')}.", null, $record);
        }

        $this->info('Sent '.$dueSoon->count().' due-soon reminder(s). Overdue reminders are handled by library:process-overdue-books.');
    }
}
