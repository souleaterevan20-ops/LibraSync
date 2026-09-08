<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Concerns\NormalizesUppercase;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, NormalizesUppercase;

    /**
     * Human-readable fields stored/displayed in UPPERCASE (spec #5).
     * Never includes email, password, role, or any other system/logic field.
     */
    protected array $uppercaseFields = [
        'name',
        'department',
        'course_program',
        'favorite_genre',
        'year_level_position',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_verified',
        'is_active',
        'approved_by',
        'approved_at',
        'rejected_at',
        'rejection_reason',
        'must_change_password',
        'avatar',
        'quick_actions',
        'bio',
        'favorite_genre',
        'contact_number',
        'department',
        'course_program',
        'school_or_employee_id',
        'year_level_position',
        'date_of_birth',
        'points',
        'penalty_balance',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'is_verified' => 'boolean',
            'is_active' => 'boolean',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'must_change_password' => 'boolean',
            'deleted_at' => 'datetime',
            'quick_actions' => 'array',
        ];
    }

    public function borrowRecords()
    {
        return $this->hasMany(BorrowRecord::class);
    }

    public function announcementDismissals()
    {
        return $this->hasMany(AnnouncementDismissal::class);
    }

        /**
     * Human-facing display name for this account's role. The internal
     * database value (e.g. 'student_assistant') never changes — this is the
     * single place that decides what a role is CALLED on screen, so every
     * view stays consistent even if the displayed name changes in the future.
     */
    public function roleLabel(): string
    {
        return match ($this->role) {
            'super_admin' => 'Super Admin',
            'student_assistant' => 'Library Staff',
            'teacher' => 'Teacher',
            'student' => 'Student',
            default => ucwords(str_replace('_', ' ', $this->role ?? '')),
        };
    }

    public function penaltyPayments()
    {
        return $this->hasMany(PenaltyPayment::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class)->latest();
    }

    public function unreadNotifications()
    {
        return $this->hasMany(Notification::class)->where('is_read', false)->latest();
    }

    public function assistantSessions()
    {
        return $this->hasMany(AssistantSession::class)->latest('login_at');
    }

    /**
     * Rank label based on points, used for the "Rank" field on the profile view.
     */
    public function getRankLabelAttribute(): string
    {
        return match (true) {
            $this->points >= 500 => 'Top Reader',
            $this->points >= 250 => 'Active Reader',
            $this->points >= 100 => 'Regular Reader',
            default => 'New Reader',
        };
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isStudentAssistant(): bool
    {
        return $this->role === 'student_assistant';
    }

    /**
     * The Library Staff (or Super Admin) account that approved this
     * registration, if any (spec #78).
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Single source of truth for the account lifecycle (spec #83/#84).
     * Every place in the app that needs to know "what state is this
     * account in" should read this instead of re-deriving it from the
     * raw is_verified / is_active / rejected_at / deleted_at columns.
     */
    public function accountStatus(): string
    {
        if ($this->trashed()) {
            return 'deleted';
        }

        if ($this->rejected_at) {
            return 'rejected';
        }

        if (! $this->is_verified) {
            return 'pending';
        }

        if (! $this->is_active) {
            return 'disabled';
        }

        return 'active';
    }

    /**
     * Human-facing label for accountStatus(), used by badges throughout
     * the admin/staff UI so the wording never drifts between views.
     */
    public function accountStatusLabel(): string
    {
        return match ($this->accountStatus()) {
            'pending' => 'Pending',
            'active' => 'Active',
            'disabled' => 'Disabled',
            'rejected' => 'Rejected',
            'deleted' => 'Deleted',
            default => ucfirst($this->accountStatus()),
        };
    }
}
