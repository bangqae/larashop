<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'parent_id',
        'user_id',
        'sku',
        'type',
        'name',
        'slug',
        'price',
        'weight',
        'width',
        'height',
        'length',
        'short_desc',
        'description',
        'status',
    ];

    public function user() // Tunggal
    {
        return $this->belongsTo('App\Models\User'); // Relasi 1 to 1, tapi di model User : 1 to many
    }

    public function productInventory() // Tunggal
    {
        return $this->hasOne('App\Models\ProductInventory'); // 1 to 1
    }

    public function categories() // Jamak
    {
        // product_categories is pivot table
        return $this->belongsToMany('App\Models\Category','product_categories'); // many to many
    }

    public function variants() // Jamak
    {
        return $this->hasMany('App\Models\Product', 'parent_id')->orderBy('price', 'ASC'); // 1 parent can have many variant
    }

    public function parent() // Tunggal
    {
        return $this->belongsTo('App\Models\Product', 'parent_id'); // 1 variant only have 1 parent
    }

    public function productAttributeValues() // Jamak
    {
        return $this->hasMany('App\Models\ProductAttributeValue', 'parent_product_id'); // 1 to many
    }

    public function productImages() // Jamak
    {
        return $this->hasMany('App\Models\ProductImage')->orderBy('id', 'DESC'); // 1 to many
    }

    public static function statuses()
    {
        return [
            0 => 'draft',
            1 => 'active',
            2 => 'inactive',
        ];
    }

    function status_label()
    {
        $statuses = $this->statuses();

       return isset($this->status) ? $statuses[$this->status] : null;
    }

    public static function types() // Whether the product has other variants or not
    {
        return [
            'simple' => 'Simple',
            'configurable' => 'Configurable',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1)
            ->where('parent_id', NULL);
    }

    function price_label()
    {
        return ($this->variants->count() > 0) ? $this->variants->first()->price : $this->price;
    }

    public function configurable()
    {
        return $this->type == 'configurable';
    }
    
    public function simple()
    {
        return $this->type == 'simple';
    }
}
