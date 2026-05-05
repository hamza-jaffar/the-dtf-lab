<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable('order_number', 'customer_id', 'status', 'total_amount', 'paid_amount', 'completed_at', 'delivered_at')]
class Order extends Model
{
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
