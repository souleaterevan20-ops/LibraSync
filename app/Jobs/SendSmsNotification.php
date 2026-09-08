<?php

namespace App\Jobs;

use App\Services\SmsSender;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Spec #50: "Use QUEUE for external email/SMS processing." SmsSender
 * itself makes a synchronous HTTP call, so it must never run inline
 * during a web request — this job is what actually gets dispatched,
 * and it runs on the queue worker instead.
 */
class SendSmsNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $phoneNumber,
        public string $message,
    ) {}

    public function handle(): void
    {
        SmsSender::send($this->phoneNumber, $this->message);
    }
}
