<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'qty',
        'base_price',
        'base_total',
        'tax_amount',
        'tax_percent',
        'discount_amount',
        'discount_amount',
        'sub_total',
        'sku',
        'type',
        'name',
        'weight',
        'attributes',
    ];

    public function product()
    {
        return $this->belongsTo('App\Models\Product');
    }
}
