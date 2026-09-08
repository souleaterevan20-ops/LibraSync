<?php

namespace App\Models;

use App\Models\Concerns\NormalizesUppercase;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Book extends Model
{
    use HasFactory, SoftDeletes, NormalizesUppercase;

    /**
     * Human-readable fields stored/displayed in UPPERCASE (spec #5).
     * 'type' and 'isbn' are excluded — 'type' is a system status value
     * ('standard'/'reference') and 'isbn' is an identifier, not free text.
     */
    protected array $uppercaseFields = [
        'title',
        'author',
        'publisher',
        'genre',
        'remarks',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'cover_image',
        'author',
        'publisher',
        'publication_year',
        'isbn',
        'genre',
        'type',
        'total_copies',
        'available_copies',
        'lost_copies',
        'damaged_copies',
        'remarks',
        'replacement_cost',
    ];

    protected function casts(): array
    {
        return [
            'deleted_at' => 'datetime',
        ];
    }

    public function borrowRecords()
    {
        return $this->hasMany(BorrowRecord::class);
    }

    /**
     * Copies currently checked out — this is TOTAL minus everything else
     * accounted for (available, lost, damaged), so it never double-counts a
     * book that's already been marked lost or damaged (spec #19).
     */
    public function borrowedCopies(): int
    {
        return max($this->total_copies - $this->available_copies - $this->lost_copies - $this->damaged_copies, 0);
    }

    public function numberOfBorrowers(): int
    {
        return $this->borrowRecords()->distinct('user_id')->count('user_id');
    }
}