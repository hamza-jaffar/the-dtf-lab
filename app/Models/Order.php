<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['order_number', 'customer_id', 'status', 'total_amount', 'paid_amount', 'notes', 'completed_at', 'delivered_at'])]
class Order extends Model
{
    protected $casts = [
        'status'       => OrderStatus::class,
        'completed_at' => 'datetime',
        'delivered_at' => 'datetime',
        'total_amount' => 'integer',
        'paid_amount'  => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
