<?php

namespace App\Services;

use App\Jobs\SendSmsNotification;
use App\Mail\LibrarySyncNotificationMail;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class Notifier
{
    /**
     * Spec #54: "critical account/security notifications may remain
     * mandatory" — these two ignore the user's email/sms toggles entirely
     * and are always sent externally when $external is true, because they
     * gate actually being able to log in / keep the account secure.
     */
    private const MANDATORY_TYPES = ['registration', 'account'];

    /**
     * Send an in-app notification to a single user.
     *
     * Spec #42/#43: pass $reference (the model the notification is about —
     * a BorrowRecord, Penalty, Announcement, etc.) to give the notification
     * a unique event identity: recipient + type + reference_type +
     * reference_id. If a notification for that exact event already exists,
     * it's returned as-is instead of creating a duplicate — safe to call
     * this twice for the same event (double form submit, a job that runs
     * twice, a scheduled command that overlaps).
     *
     * $reference is optional and defaults to null for backward compatibility
     * with existing call sites that don't have a natural "one event, one
     * notification" model to key off of.
     *
     * Spec #50-54: pass $external = true to ALSO queue this notification as
     * email/SMS, on top of the in-app row this method always creates. This
     * is opt-in per call site — only the recommended important events
     * (spec #52) should pass true. Defaults to false so every existing
     * Notifier::send() call in the codebase is unaffected. Mandatory types
     * are sent externally regardless of the user's per-channel preference;
     * everything else respects $user->email_notifications / sms_notifications.
     */
    public static function send(User $user, string $type, string $title, string $message, ?string $link = null, $reference = null, bool $external = false): Notification
    {
        if ($reference) {
            $referenceType = get_class($reference);
            $referenceId = $reference->id;

            $existing = Notification::where('user_id', $user->id)
                ->where('type', $type)
                ->where('reference_type', $referenceType)
                ->where('reference_id', $referenceId)
                ->first();

            if ($existing) {
                if ($external) {
                    self::dispatchExternal($user, $type, $title, $message, $link);
                }

                return $existing;
            }
        }

        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference?->id,
            'title' => $title,
            'message' => $message,
            'link' => $link,
        ]);

        if ($external) {
            self::dispatchExternal($user, $type, $title, $message, $link);
        }

        return $notification;
    }

    /**
     * Send an in-app notification to every user with the given role(s).
     */
    public static function sendToRole(string|array $roles, string $type, string $title, string $message, ?string $link = null): void
    {
        $roles = is_array($roles) ? $roles : [$roles];

        User::whereIn('role', $roles)->get()->each(function (User $user) use ($type, $title, $message, $link) {
            self::send($user, $type, $title, $message, $link);
        });
    }

    /**
     * Spec #33/#52: fan out email/SMS ONLY (no new in-app Notification row —
     * announcements already have their own dashboard-banner + per-user
     * dismiss mechanism, so this doesn't duplicate that) to every active
     * Student/Teacher. Used for IMPORTANT/URGENT announcements only.
     * Chunked to avoid loading the whole user table into memory at once
     * on a large roster (spec #58).
     */
    public static function sendExternalToRole(string|array $roles, string $title, string $message, ?string $link = null): void
    {
        $roles = is_array($roles) ? $roles : [$roles];

        User::whereIn('role', $roles)->where('is_active', true)
            ->chunkById(100, function ($users) use ($title, $message, $link) {
                foreach ($users as $user) {
                    self::dispatchExternal($user, 'announcement', $title, $message, $link);
                }
            });
    }

    /**
     * Queues the email/SMS jobs themselves. Both are ShouldQueue, so this
     * only ever pushes to the `jobs` table — it never makes an outbound
     * HTTP/SMTP call inline during the request (spec #50).
     */
    private static function dispatchExternal(User $user, string $type, string $title, string $message, ?string $link): void
    {
        $isMandatory = in_array($type, self::MANDATORY_TYPES, true);

        if ($user->email && ($isMandatory || $user->email_notifications)) {
            Mail::to($user->email)->queue(new LibrarySyncNotificationMail($title, $message, $link));
        }

        if ($user->contact_number && ($isMandatory || $user->sms_notifications)) {
            SendSmsNotification::dispatch($user->contact_number, "{$title}: {$message}");
        }
    }
}
