<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name','slug','parent_id'];

    public function childs()
    {
        return $this->hasMany('App\Model\Category', 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo('App\Models\Category', 'parent_id');
    }
}


// Note :
// Relasi parent-childs ini dapat dijadikan referensi
// untuk project sadikoen fam
