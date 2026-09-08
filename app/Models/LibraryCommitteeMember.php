<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LibraryCommitteeMember extends Model
{
    protected $fillable = ['name', 'position', 'photo_path', 'display_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order')->orderBy('name');
    }
}
