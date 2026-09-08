<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Centralized CAPSLOCK data standardization (spec #5).
 *
 * Add `use NormalizesUppercase;` to a model and declare which of its own
 * fillable fields are human-readable free text in a $uppercaseFields
 * property, e.g.:
 *
 *   protected array $uppercaseFields = ['title', 'author'];
 *
 * Every one of those fields is automatically uppercased right before the
 * model saves — no manual strtoupper() calls scattered through controllers.
 *
 * This is opt-in per field on purpose: a field is only ever touched if a
 * model explicitly lists it, so passwords, emails, roles, statuses, enum
 * values, IDs, and timestamps are never at risk of being uppercased just
 * because they happen to be a string column.
 */
trait NormalizesUppercase
{
    public static function bootNormalizesUppercase(): void
    {
        static::saving(function ($model) {
            foreach ($model->uppercaseFields ?? [] as $field) {
                if ($model->isDirty($field) && is_string($model->{$field})) {
                    $model->{$field} = Str::upper(trim($model->{$field}));
                }
            }
        });
    }
}
