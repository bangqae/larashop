<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAttributeValue extends Model
{
    protected $fillable = [
        'parent_product_id',
        'product_id',
        'attribute_id',
        'text_value',
        'boolean_value',
        'integer_value',
        'float_value',
        'datetime_value',
        'date_value',
        'json_value',
    ];

    public function product() // Tunggal
    {
        return $this->belongsTo('App\Models\Product'); // 1 to 1
    }

    public function attribute()
    {
        return $this->belongsTo('App\Models\Attribute'); 
    }

    public static function getAttributeOptions($product, $attributeCode)
    {
        $productVariantIDs = $product->variants->pluck('id');
        $attribute = Attribute::where('code', $attributeCode)->first();

        $attributeOptions = ProductAttributeValue::where('attribute_id', $attribute->id)
                            ->whereIn('product_id', $productVariantIDs)
                            ->get();

        return $attributeOptions;
    }
}
