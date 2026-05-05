<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable('order_item_id', 'file_path')]
class DesignFile extends Model
{
    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }
}
