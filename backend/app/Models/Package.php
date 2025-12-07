<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package extends Model
{
    use SoftDeletes;

    const BILLING_MONTHLY = 'monthly';
    const BILLING_ANNUALLY = 'annually';
    const BILLING_BIENNIAL = 'biennial';

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'base_price',
        'billing_cycle',
        'setup_fee',
        'is_popular',
        'is_active',
        'discount_percentage',
        'order',
        'max_renewals',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'setup_fee' => 'decimal:2',
        'is_popular' => 'boolean',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the category this package belongs to
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get all features in this package
     */
    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, 'package_features')
            ->withPivot('value', 'price_modifier', 'is_default')
            ->withTimestamps();
    }

    /**
     * Get all orders for this package
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Calculate total price including modifiers
     */
    public function calculatePrice(array $featureValues = []): float
    {
        $total = (float) $this->base_price + (float) $this->setup_fee;

        if (!empty($featureValues)) {
            foreach ($featureValues as $featureId => $value) {
                $feature = $this->features()
                    ->where('features.id', $featureId)
                    ->first();

                if ($feature) {
                    $modifier = $feature->pivot->price_modifier ?? 0;
                    $total += (float) $modifier * ($value ?? 1);
                }
            }
        }

        return round($total, 2);
    }

    /**
     * Scope to get only active packages
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('order');
    }
}
