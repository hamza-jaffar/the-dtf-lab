<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['order_id', 'width', 'height', 'square_inches', 'rate_per_inch', 'total_price', 'design_name', 'quantity', 'pricing_type'])]
class OrderItem extends Model
{
    protected $casts = [
        'width'         => 'float',
        'height'        => 'float',
        'square_inches' => 'float',
        'rate_per_inch' => 'float',
        'total_price'   => 'float',
        'quantity'      => 'integer',
        'pricing_type'  => 'string',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function designFiles(): HasMany
    {
        return $this->hasMany(DesignFile::class);
    }
}
