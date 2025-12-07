<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomizationLimit extends Model
{
    protected $fillable = [
        'user_id',
        'feature_id',
        'max_value',
        'current_value',
        'is_enforced',
    ];

    protected $casts = [
        'is_enforced' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user this limit belongs to
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the feature this limit is for
     */
    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }

    /**
     * Check if user can add more to this feature
     */
    public function canAddMore(int $amount = 1): bool
    {
        if (!$this->is_enforced) {
            return true;
        }

        return ($this->current_value + $amount) <= $this->max_value;
    }

    /**
     * Get remaining capacity
     */
    public function getRemainingCapacity(): int
    {
        return max(0, $this->max_value - $this->current_value);
    }
}
