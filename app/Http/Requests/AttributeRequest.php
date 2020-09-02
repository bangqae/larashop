<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttributeRequest extends FormRequest
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
        if ($this->method() == 'PUT') // Jika edit
        {
            // Maksud dari pemberian id di sini adalah
            // agar baris data yang id tersebut tidak dijadikan perbandingan unique 
            $code = 'required|unique:attributes,code,'. $this->get('id');
            $name = 'required|unique:attributes,name,'. $this->get('id');
        } else { // Jika create
            $code = 'required|unique:attributes,code';
            $name = 'required|unique:attributes,name';
        }

        return [ // Nilai return sesuai method yang digunakan di controller
            'code' => $code,
            'name' => $name,
            'type' => 'required',
        ];
    }
}
