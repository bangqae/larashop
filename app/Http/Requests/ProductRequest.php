<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
        $qty = 'numeric'; // Bagian ini
        $price = 'numeric';
        $status = '';
        $weight = 'numeric';

        if ($this->method() == 'PUT') // Ketika update
        {
            $type = '';
            $sku = 'required|unique:products,sku,'. $this->get('id');
            $name = 'required|unique:products,name,'. $this->get('id');
            $status = 'required';

            if ($this->get('type') == 'simple') { // Ketika update dan ber-type simple
                $qty .= '|required'; // Terdapat tanda '|' diawal karena bagian ini menambahkan yang sebelumnya
                $price .= '|required';
                $weight .= '|required';
            }
        } else { // Ketika create
            $type = 'required';
            $sku = 'required|unique:products,sku';
            $name = 'required|unique:products,name';
        }

        return [
            'type' => $type,
            'sku' => $sku,
            'name' => $name,
            'weight' => 'required|numeric',
            'price' => 'required|numeric',
            'status' => 'required',
            'price' => $price,
            'qty' => $qty,
            'status' => $status,
            'weight' => $weight,
        ];
    }
}
