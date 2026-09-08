<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LibrarySetting extends Model
{
    protected $fillable = [
        'library_name',
        'description',
        'address',
        'email',
        'phone',
        'operating_hours',
    ];

    /**
     * There is exactly one settings row. This returns it, creating it with
     * defaults if it somehow doesn't exist yet (fresh install before the
     * migration's seed ran, or the row was deleted).
     */
    public static function current(): self
    {
        return static::first() ?? static::create(['library_name' => 'LibraSync']);
    }
}
