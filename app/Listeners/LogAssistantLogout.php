<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use App\Models\AssistantSession;
use App\Services\AuditLogger;
use App\Services\Notifier;

class LogAssistantLogout
{
    public function handle(Logout $event): void
    {
        $user = $event->user;

        if ($user) {
            AuditLogger::log('Logged Out', $user, null, $user);

            if ($user->role === 'student_assistant') {
                Notifier::sendToRole('super_admin', 'logout', 'Library Staff logged out', "{$user->name} logged out of LibraSync.");
            }
        }

        $sessionId = session('active_desk_session');

        if ($sessionId) {
            $session = AssistantSession::find($sessionId);
            if ($session) {
                $session->update([
                    'logout_at' => now(),
                ]);
            }
        }
    }
}
