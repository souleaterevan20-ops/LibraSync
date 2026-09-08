<?php

namespace App\Models;

use App\Models\Concerns\NormalizesUppercase;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory, NormalizesUppercase;

    /**
     * Human-readable fields stored/displayed in UPPERCASE (spec #5).
     */
    protected array $uppercaseFields = ['title', 'message'];

    protected $fillable = [
        'created_by',
        'title',
        'message',
        'priority',
        'image_path',
        'video_path',
        'posted_at',
        'expires_at',
    ];

    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_IMPORTANT = 'important';
    public const PRIORITY_URGENT = 'urgent';

    public const PRIORITIES = [self::PRIORITY_NORMAL, self::PRIORITY_IMPORTANT, self::PRIORITY_URGENT];

    /**
     * Spec #33: only IMPORTANT/URGENT announcements should trigger external
     * SMS/email once that's wired up (Batch 10) — this is the flag that
     * work will read, so it exists ahead of time instead of being bolted on.
     */
    public function shouldNotifyExternally(): bool
    {
        return in_array($this->priority, [self::PRIORITY_IMPORTANT, self::PRIORITY_URGENT], true);
    }

    protected function casts(): array
    {
        return [
            'posted_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function dismissals()
    {
        return $this->hasMany(AnnouncementDismissal::class);
    }

    public function isDismissedBy(User $user): bool
    {
        return $this->dismissals()->where('user_id', $user->id)->exists();
    }

    public function scopeActive($query)
    {
        return $query->where('posted_at', '<=', now())
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
