<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class BorrowRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'book_id',
        'status', // pending, borrowed, returned, overdue
        'condition', // good, lost, damaged
        'fine_amount',
        'fine_paid_amount',
        'fine_status', // unpaid, partially_paid, paid, waived, none
        'overdue_marked_at',
        'overdue_charged_amount',
        'overdue_points_deducted',
        'borrowed_at',
        'due_at',
        'returned_at',
        'checked_out_by',
        'checked_in_by',
        'is_archived',
        'archived_semester',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return [
            'borrowed_at' => 'datetime',
            'due_at' => 'datetime',
            'returned_at' => 'datetime',
            'deleted_at' => 'datetime',
            'overdue_marked_at' => 'datetime',
            'fine_amount' => 'decimal:2',
            'fine_paid_amount' => 'decimal:2',
        ];
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function checkedInBy()
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    public function checkedOutBy()
    {
        return $this->belongsTo(User::class, 'checked_out_by');
    }

    public function penaltyPayments()
    {
        return $this->hasMany(PenaltyPayment::class)->latest();
    }

    /**
     * How much of THIS record's fine is still owed. This — not the flat
     * user-level balance — is the source of truth for whether an individual
     * book's penalty is settled (spec #30: Book A and Book B are independent).
     */
    public function fineRemaining(): float
    {
        return max(0, (float) $this->fine_amount - (float) $this->fine_paid_amount);
    }

    /**
     * UNPAID / PARTIALLY_PAID / PAID / WAIVED / NONE, derived from the actual
     * amount owed rather than a status flag that could drift out of sync
     * (spec #31 — never mark everything paid when only one penalty is settled).
     */
    public function computedFineStatus(): string
    {
        if ($this->fine_status === 'waived') {
            return 'waived';
        }

        if ((float) $this->fine_amount <= 0) {
            return 'none';
        }

        if ($this->fineRemaining() <= 0) {
            return 'paid';
        }

        if ((float) $this->fine_paid_amount > 0) {
            return 'partially_paid';
        }

        return 'unpaid';
    }

    /**
     * What kind of penalty this is, for display (spec #30's "Penalty Type").
     */
    public function penaltyType(): ?string
    {
        if ((float) $this->fine_amount <= 0) {
            return null;
        }

        return match ($this->condition) {
            'lost' => 'Lost Book',
            'damaged' => 'Damaged Book',
            default => 'Overdue Return',
        };
    }

    /**
     * Human-readable reason for the penalty (spec #30's "Reason").
     */
    public function penaltyReason(): ?string
    {
        if ((float) $this->fine_amount <= 0) {
            return null;
        }

        return match ($this->condition) {
            'lost' => 'Book reported lost — replacement cost charged.',
            'damaged' => 'Book returned damaged — replacement cost charged.',
            default => 'Returned late — ₱10 charged per overdue day.',
        };
    }

    /**
     * Dynamically calculate late fines (10 Pesos per day).
     */
   public function calculateCurrentFine()
{
    return \App\Services\PenaltyService::currentFine($this);
}

    /**
     * Past its due date and not yet returned/lost/damaged.
     */
   public function isOverdue(): bool
{
    if ($this->status !== 'borrowed' || ! $this->due_at) {
        return false;
    }

    return now()->startOfDay()->gt(\Carbon\Carbon::parse($this->due_at)->startOfDay());
}
}