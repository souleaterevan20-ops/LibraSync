<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\AssistantSession;
use App\Services\AuditLogger;
use App\Services\Notifier;

class LogAssistantLogin
{
    public function handle(Login $event): void
    {
        $user = $event->user;

        AuditLogger::log('Logged In', $user, null, $user);

        // Track desk sessions for Library Staffs and Super Admins.
        if (in_array($user->role, ['student_assistant', 'super_admin'], true)) {
            session(['active_desk_session' => AssistantSession::create([
                'user_id' => $user->id,
                'login_at' => now(),
                'ip_address' => request()->ip(),
            ])->id]);
        }

        if ($user->role === 'student_assistant') {
            Notifier::sendToRole('super_admin', 'login', 'Library Staff logged in', "{$user->name} logged in to LibraSync.");
        }
    }
}
