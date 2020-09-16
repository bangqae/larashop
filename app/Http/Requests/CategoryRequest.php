<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // Pakain Wanita
        //     Jeans
        //     Gamis
        // Pakain Pria
        //     Kemeja
        //     Jeans

        $parentId = (int) $this->get('parent_id');
        $id = (int) $this->get('id');
        $parent = Category::find($parentId);

        if ($this->method() == 'PUT') {
            if ($parentId > 0) {
                // Kondisi ketika user edit category dengan mendefinisikan parent category
                $name = 'required|not_in:'. $parent->name .'|unique:categories,name,'. $id .',id,parent_id,'. $parentId;
            } else {
                // Kondisi ketika user edit category tanpa mendefinisikan parent category
                $name = 'required|unique:categories,name,'. $id;
            }

            $slug = 'unique:categories,slug,'. $id;

        } else {
            // Kondisi ketika user add category
            if ($parentId > 0) {
                $name = 'required|not_in:'. $parent->name .'|unique:categories,name,NULL,id,parent_id,'. $parentId;
            } else {
                $name = 'required|unique:categories,name,NULL,id';
            }

            $slug = 'unique:categories,slug';

        }

        return [
            'name' => $name,
            'slug' => $slug,
        ];
    }
}
