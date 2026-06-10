<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['order_number', 'customer_id', 'status', 'total_amount', 'price_override', 'paid_amount', 'notes', 'completed_at', 'delivered_at'])]
class Order extends Model
{
    protected $casts = [
        'status'         => OrderStatus::class,
        'completed_at'   => 'datetime',
        'delivered_at'   => 'datetime',
        'total_amount'   => 'float',
        'price_override' => 'float',
        'paid_amount'    => 'float',
    ];

    /**
     * The effective total: use price_override if set, otherwise total_amount.
     */
    public function getEffectiveTotalAttribute(): float
    {
        return $this->price_override ?? $this->total_amount;
    }

    public function getPendingAmountAttribute(): float
    {
        return $this->effective_total - ($this->paid_amount ?? 0);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payments::class);
    }
}
