<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SemesterArchive extends Model
{
    protected $fillable = [
        'semester',
        'created_by',
        'record_count',
        'user_count',
        'penalty_count',
        'payment_count',
        'zip_path',
        'pdf_path',
        'size_bytes',
        'status',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sizeHuman(): string
    {
        $bytes = $this->size_bytes;
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }
        return $bytes . ' B';
    }
}
