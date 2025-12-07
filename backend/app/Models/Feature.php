<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Feature extends Model
{
    use SoftDeletes;

    const TYPE_NUMBER = 'number';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_DROPDOWN = 'dropdown';
    const TYPE_TEXT = 'text';

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'type',
        'options', // JSON for dropdown options
        'min_value',
        'max_value',
        'default_value',
        'base_price',
        'is_customizable',
        'is_active',
        'order',
    ];

    protected $casts = [
        'options' => 'array',
        'is_customizable' => 'boolean',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the category this feature belongs to
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get all packages that have this feature
     */
    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class, 'package_features')
            ->withPivot('value', 'price_modifier', 'is_default')
            ->withTimestamps();
    }

    /**
     * Validate type
     */
    public static function validTypes(): array
    {
        return [
            self::TYPE_NUMBER,
            self::TYPE_BOOLEAN,
            self::TYPE_DROPDOWN,
            self::TYPE_TEXT,
        ];
    }

    /**
     * Scope to get customizable features only
     */
    public function scopeCustomizable($query)
    {
        return $query->where('is_customizable', true)->where('is_active', true);
    }
}
