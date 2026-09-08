<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Spec #51: SMS notifications for the Philippines, credentials from
 * environment variables only (SMS_PROVIDER, SMS_API_KEY, SMS_API_SECRET,
 * SMS_SENDER) — never hardcoded in source.
 *
 * This ships wired up for Semaphore (semaphore.co), a widely-used PH SMS
 * gateway, since something concrete has to be the default — but nothing
 * about the rest of the app depends on that choice. Swap sendViaSemaphore()
 * for a different provider's API call and everything else (Notifier,
 * queued dispatch, preference checks) keeps working unchanged.
 *
 * IMPORTANT: this requires a real SMS_API_KEY from an actual provider
 * account, which nobody but you can obtain — I can't create or verify
 * one for you. Until SMS_PROVIDER/SMS_API_KEY are set in .env, every call
 * here safely no-ops (logs and returns false) instead of throwing, so the
 * rest of the app is completely unaffected by SMS being unconfigured.
 */
class SmsSender
{
    public static function send(string $phoneNumber, string $message): bool
    {
        $provider = config('services.sms.provider');
        $apiKey = config('services.sms.api_key');

        if (! $provider || ! $apiKey || ! $phoneNumber) {
            Log::info('SMS not sent — provider not configured or no phone number on file.', [
                'provider_configured' => (bool) $provider,
                'has_phone' => (bool) $phoneNumber,
            ]);

            return false;
        }

        return match (strtolower($provider)) {
            'semaphore' => self::sendViaSemaphore($phoneNumber, $message),
            default => self::logUnsupportedProvider($provider),
        };
    }

    private static function sendViaSemaphore(string $phoneNumber, string $message): bool
    {
        try {
            $response = Http::asForm()->post('https://api.semaphore.co/api/v4/messages', [
                'apikey' => config('services.sms.api_key'),
                'number' => $phoneNumber,
                'message' => $message,
                'sendername' => config('services.sms.sender') ?: null,
            ]);

            if (! $response->successful()) {
                Log::warning('SMS send failed.', ['status' => $response->status(), 'body' => $response->body()]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('SMS send threw an exception.', ['message' => $e->getMessage()]);

            return false;
        }
    }

    private static function logUnsupportedProvider(string $provider): bool
    {
        Log::warning("SMS_PROVIDER \"{$provider}\" is not implemented in SmsSender. Only \"semaphore\" is wired up.");

        return false;
    }
}
