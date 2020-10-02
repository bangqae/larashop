<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name','slug','parent_id'];

    public function childs() // Jamak
    {
        // Relasi ke dirinya sendiri, parent_id bukan pivot table, tapi field di tabel categories yang diambil dari id
        return $this->hasMany('App\Model\Category', 'parent_id'); // Relasi 1 to many
    }

    public function parent() // Tunggal
    {
        // Relasi ke dirinya sendiri, parent_id bukan pivot table, tapi field di tabel categories yang diambil dari id
        return $this->belongsTo('App\Models\Category', 'parent_id'); // Relasi 1 to 1
    }

    public function products() // Jamak
    {
        // Tabel product_categories adalah pivot table
        return $this->belongsToMany('App\Models\Product', 'product_categories'); // Relasi many to many
    }

    public function scopeParentCategories($query)
    {
        return $query->where('parent_id', 0);
    }

    public static function childIds($parentId = 0)
	{
		$categories = Category::select('id','name','parent_id')->where('parent_id', $parentId)->get()->toArray();

		$childIds = [];
		if(!empty($categories)){
			foreach($categories as $category){
				$childIds[] = $category['id'];
				$childIds = array_merge($childIds, Category::childIds($category['id']));
			}
		}

		return $childIds;
	}
}


// Note :
// Relasi parent-childs ini dapat dijadikan referensi
// untuk project sadikoen fam
