<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'role' => ['required', 'string', 'in:student,teacher,student_assistant,super_admin'],
        ];
    }

    public function messages(): array
    {
        return [
            'role.required' => 'Please select your role.',
            'role.in' => 'Please select a valid role.',
        ];
    }

    /**
     * Human-friendly labels for messages/redisplay — never used for system logic.
     */
    private const ROLE_LABELS = [
        'student' => 'Student',
        'teacher' => 'Teacher',
        'student_assistant' => 'Library Staff',
        'super_admin' => 'Super Admin',
    ];

    /**
     * Attempt to authenticate the request's credentials, following the exact
     * order spec #4 requires: 1) credentials, 2) selected role, 3) account
     * status, 4) verification. Session regeneration/logging/redirect happen
     * back in the controller once this returns successfully.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // 1. Authenticate credentials. Email is normalized to lowercase to match
        // how it's stored on every account-creation path — the password itself
        // is never transformed in any way before this comparison.
        $credentials = $this->only('email', 'password');
        $credentials['email'] = Str::lower(trim($credentials['email']));

        if (! Auth::attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        $user = Auth::user();

        // 2. Verify selected role matches the account (backend check — never trust the frontend).
        if ($user->role !== $this->input('role')) {
            Auth::logout();
            RateLimiter::hit($this->throttleKey());

            $actualLabel = self::ROLE_LABELS[$user->role] ?? $user->role;

            throw ValidationException::withMessages([
                'role' => "The selected role does not match this account. This account is registered as {$actualLabel}.",
            ]);
        }

        // 3. Verify account status.
        if (! $user->is_active) {
            Auth::logout();
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'Your account has been disabled. Please contact the library administrator.',
            ]);
        }

        // 4. Verify required email/account verification — let it through to the
        // "pending approval" waiting room rather than blocking here, since that's
        // an intentional, informative landing page rather than a hard failure.

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
