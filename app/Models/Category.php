<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name','slug','parent_id'];

    public function childs() // Jamak
    {
        // Relasi ke dirinya sendiri, parent_id bukan pivot table, tapi field di tabel categories yang diambil dari id
        return $this->hasMany('App\Model\Category', 'parent_id'); // Relasi one to many
    }

    public function parent() // Tunggal
    {
        // Relasi ke dirinya sendiri, parent_id bukan pivot table, tapi field di tabel categories yang diambil dari id
        return $this->belongsTo('App\Models\Category', 'parent_id'); // Relasi one to one
    }

    public function products() // Jamak
    {
        // Tabel product_categories adalah pivot table
        return $this->belongsToMany('App\Models\Product', 'product_categories'); // Relasi many to many
    }
}


// Note :
// Relasi parent-childs ini dapat dijadikan referensi
// untuk project sadikoen fam
