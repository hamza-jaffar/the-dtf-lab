<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['item_name', 'quantity', 'unit_price', 'total_price', 'purchase_date', 'notes'])]
class Purchase extends Model
{
    protected $casts = [
        'purchase_date' => 'date',
        'unit_price' => 'float',
        'total_price' => 'float',
        'quantity' => 'integer',
    ];
}
