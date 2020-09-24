<?php

namespace App\Http\Controllers\Admin;

use App\Authorizable;

use App\Models\Attribute;
use App\Models\AttributeOption;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Session; // use Session

use App\Http\Requests\AttributeRequest;
use App\Http\Requests\AttributeOptionRequest;

class AttributeController extends Controller
{
    use Authorizable;
    
    /** Method khusus yang akan dieksekusi pada saat pembuatan objek (instance). */
    public function __construct()
    {
        parent::__construct();

        $this->data['currentAdminMenu'] = 'catalog';
        $this->data['currentAdminSubMenu'] = 'attribute';

        $this->data['types'] = Attribute::types();
        $this->data['booleanOptions'] = Attribute::booleanOptions();
        $this->data['validations'] = Attribute::validations();
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->data['attributes'] = Attribute::orderBy('name','ASC')->paginate(10);
        
        return view('admin.attributes.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->data['attribute'] = null;

        return view('admin.attributes.form', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\AttributeRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AttributeRequest $request)
    {
        $params = $request->except('_token');
        // Convert ke boolean karena input dari form berupa text
        $params['is_required'] = (bool) $params['is_required'];
        $params['is_unique'] = (bool) $params['is_unique'];
        $params['is_configurable'] = (bool) $params['is_configurable'];
        $params['is_filterable'] = (bool) $params['is_filterable'];

        if(Attribute::create($params)) {
            Session::flash('success', 'Attribute has been saved!');
        }

        return redirect('admin/attributes');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $attribute = Attribute::findOrFail($id);

        $this->data['attribute'] = $attribute;

        return view('admin.attributes.form', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\AttributeRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(AttributeRequest $request, $id)
    {
        $params = $request->except('_token');
        $params['is_required'] = (bool) $params['is_required'];
        $params['is_unique'] = (bool) $params['is_unique'];
        $params['is_configurable'] = (bool) $params['is_configurable'];
        $params['is_filterable'] = (bool) $params['is_filterable'];

        unset($params['code']); // Agar tidak mengambil data dari inputan
        unset($params['type']);

        $attribute = Attribute::findOrFail($id);

        if($attribute->update($params)) {
            Session::flash('success', 'Attribute has been updated!');
        }

        return redirect('admin/attributes');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $attribute = Attribute::findOrFail($id);

        if($attribute->delete()) {
            Session::flash('success', 'Attribute has been deleted!');
        }

        return redirect('admin/attributes');
    }

    /**
     * Show options of specified attribute.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function options($attributeID)
    {
        if(empty($attributeID)) {
            return redirect('admin/attributes'); // Error handling, jikalau id-nya kosong
        }

        $attribute = Attribute::findOrFail($attributeID);
        $this->data['attribute'] = $attribute;

        return view('admin.attributes.options', $this->data);
    }

    /**
     * Store a new option of certain attribute.
     *
     * @param  \Illuminate\Http\AttributeOptionRequest  $request
     * @param  int  $attributeID
     * @return \Illuminate\Http\Response
     */
    public function store_option(AttributeOptionRequest $request, $attributeID)
    {
        if(empty($attributeID)) {
            return redirect('admin/attributes');
        }

        $params = [
            'attribute_id' => $attributeID,
            'name' => $request->get('name'),
        ];

        if(AttributeOption::create($params)) {
            Session::flash('success', 'Option has been saved!');
        }

        return redirect('admin/attributes/'. $attributeID .'/options');
    }

    /**
     * Show the form for editing an option.
     *
     * @param  int  $optionID
     * @return \Illuminate\Http\Response
     */
    public function edit_option($optionID)
    {
        $option = AttributeOption::findOrFail($optionID);

        $this->data['attributeOption'] = $option;
        $this->data['attribute'] = $option->attribute;

        return view('admin.attributes.options', $this->data);
    }

    /**
     * Update specified option.
     *
     * @param  \Illuminate\Http\AttributeOptionRequest  $request
     * @param int $optionID
     * @return \Illuminate\Http\Response
     */
    public function update_option(AttributeOptionRequest $request, $optionID)
    {
        $option = AttributeOption::findOrFail($optionID);
        $params = $request->except('_token');

        if ($option->update($params)) {
            Session::flash('success', 'Option has been edited!');
        }

        return redirect('admin/attributes/'. $option->attribute->id .'/options');
    }

    /**
     * Remove the specified option.
     *
     * @param  int  $optionID
     * @return \Illuminate\Http\Response
     */
    public function remove_option($optionID)
    {
        if (empty($optionID)) {
            return redirect('admin/attributes');
        }

        $option = AttributeOption::findOrFail($optionID);

        if($option->delete()) {
            Session::flash('success', "Option has been deleted!");
        }

        return redirect('admin/attributes/'. $option->attribute->id .'/options');
    }
}
