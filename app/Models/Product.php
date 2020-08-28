<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'user_id',
        'sku',
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
        return $this->belongsTo('App\Models\User'); // Relasi one to one, tapi di model User : one to many
    }

    public function categories() // Jamak
    {
        // Tabel product_categories adalah pivot table
        return $this->belongsToMany('App\Models\Category','product_categories'); // Relasi many to many
    }

    public static function statuses()
    {
        return [
            0 => 'draft',
            1 => 'active',
            2 => 'inactive',
        ];
    }
}
