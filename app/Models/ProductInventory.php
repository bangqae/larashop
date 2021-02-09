<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductInventory extends Model
{
    protected $fillable = [
        'product_id',
        'qty',
    ];

    public function product()
    {
        return $this->belongsTo('App\Models\Product'); // 1 data in here belongs to 1 data in products
    }

    public static function reduceStock($productId, $qty)
    {
        $inventory = self::where('product_id', $productId)->firstOrFail();
        $inventory->qty = $inventory->qty - $qty;
        $inventory->save();
        // print_r($inventory->toArray());exit;
    }
}
