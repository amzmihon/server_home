<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes;

    const STATUS_PENDING = 'pending';
    const STATUS_AUTHORIZED = 'authorized';
    const STATUS_CAPTURED = 'captured';
    const STATUS_REFUNDED = 'refunded';
    const STATUS_FAILED = 'failed';

    const GATEWAY_STRIPE = 'stripe';
    const GATEWAY_BKASH = 'bkash';
    const GATEWAY_NAGAD = 'nagad';
    const GATEWAY_BANK_TRANSFER = 'bank_transfer';

    protected $fillable = [
        'order_id',
        'user_id',
        'gateway',
        'transaction_id',
        'reference_id',
        'amount',
        'currency',
        'status',
        'metadata', // JSON for additional data
        'response_data', // JSON for gateway response
        'failed_reason',
        'attempted_at',
        'completed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'response_data' => 'array',
        'attempted_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the order this payment belongs to
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the user making the payment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if payment is successful
     */
    public function isSuccessful(): bool
    {
        return in_array($this->status, [self::STATUS_AUTHORIZED, self::STATUS_CAPTURED]);
    }

    /**
     * Scope by gateway
     */
    public function scopeByGateway($query, $gateway)
    {
        return $query->where('gateway', $gateway);
    }

    /**
     * Scope by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
