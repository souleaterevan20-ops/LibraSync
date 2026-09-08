<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Spec #50: automated email notifications via Laravel's mail system and
 * queued jobs — "Do not send email synchronously if it can slow down the
 * user request." Implements ShouldQueue so every send goes through the
 * `jobs` table (QUEUE_CONNECTION=database is already configured) instead
 * of blocking the request that triggered it.
 */
class LibrarySyncNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $notificationTitle,
        public string $notificationMessage,
        public ?string $actionUrl = null,
        public string $actionLabel = 'Open LibraSync',
    ) {}

    public function build(): self
    {
        return $this->subject($this->notificationTitle)
            ->view('emails.notification');
    }
}
