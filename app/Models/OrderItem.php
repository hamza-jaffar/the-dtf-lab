<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable('order_id', 'width', 'height', 'square_inches'.'rate_per_inch', 'total_price', 'design_name', 'quantity')]
class OrderItem extends Model
{
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function files()
    {
        return $this->hasMany(DesignFile::class);
    }
}
