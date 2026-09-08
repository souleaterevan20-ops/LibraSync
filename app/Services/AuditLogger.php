<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    /**
     * Record an entry in the audit trail.
     *
     * @param string      $action      e.g. "Approved Registration", "Deleted User", "Returned Book"
     * @param mixed|null  $subject     Any Eloquent model this action applies to
     * @param string|null $description Optional extra detail
     * @param User|null   $actor       Defaults to the currently authenticated user
     */
    public static function log(string $action, mixed $subject = null, ?string $description = null, ?User $actor = null): AuditLog
    {
        $actor = $actor ?? Auth::user();

        return AuditLog::create([
            'actor_id' => $actor?->id,
            'actor_name' => $actor?->name ?? 'System',
            'actor_role' => $actor?->role,
            'action' => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id,
            'description' => $description,
        ]);
    }
}
