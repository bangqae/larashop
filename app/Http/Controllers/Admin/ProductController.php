<?php

namespace App\Http\Controllers\Admin;

use App\Authorizable;

use App\Models\Product;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\ProductImage;
use App\Models\AttributeOption;
use App\Models\ProductInventory;
use App\Models\ProductAttributeValue;

use Illuminate\Support\Str; // use Str;
use Illuminate\Support\Facades\DB; // use DB;
use Illuminate\Support\Facades\Auth; // use Auth;
use Illuminate\Support\Facades\Session; // use Session;

use App\Http\Controllers\Controller;

use App\Http\Requests\ProductRequest;
use App\Http\Requests\ProductImageRequest;

class ProductController extends Controller
{
    use Authorizable;

    /** Constructor, just static array from Product */
    public function __construct()
    {
        $this->data['statuses'] = Product::statuses();
        $this->data['types'] = Product::types();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->data['products'] = Product::orderBy('name', 'ASC')->paginate(10);

        return view('admin.products.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::orderBy('name', 'ASC')->get();
        $configurableAttributes = $this->getConfigurableAttributes(); // Ambil data attribute dari method getConAtts

        $this->data['categories'] = $categories->toArray();
        $this->data['product'] = null;
        $this->data['productID'] = 0; // Karena kita hanya menggunakan satu view form,
        $this->data['categoryIDs'] = []; // terdapat beberapa beberapa variabel yang harus diisi
        $this->data['configurableAttributes'] = $configurableAttributes; // tadi typo

        return view('admin.products.form', $this->data);
    }

    private function getConfigurableAttributes() // Method getConAtts
    {
        return Attribute::where('is_configurable', true)->get(); // Ambil attribute yang configurable
    }

    private function generateAttributeCombinations($arrays) // Method untuk kombinasi attributes, genAttrCombs
    {
        $result = [[]];
        foreach ($arrays as $property => $property_values) {
            $tmp = [];
            foreach ($result as $result_item) {
                foreach ($property_values as $property_value) {
                    $tmp[] = array_merge($result_item, array($property => $property_value));
                }
            }
            $result = $tmp;
        }
        return $result;
    }

    private function convertVariantAsName($variant) // Konversi variant menjadi name product
    {
        $variantName = '';

        foreach (array_keys($variant) as $key => $code) {
            $attributeOptionID = $variant[$code];
            $attributeOption = AttributeOption::find($attributeOptionID);

            if ($attributeOption) {
                $variantName .= ' - ' . $attributeOption->name;
            }
        }

        return $variantName;
    }

    private function generateProductVariants($product, $params) // Method genPrdVrnts
    {
        $configurableAttributes = $this->getConfigurableAttributes(); // Ambil data attribute dari method getConAtts

        $variantAttributes = [];
        foreach ($configurableAttributes as $attribute) {
            $variantAttributes[$attribute->code] = $params[$attribute->code];
        }
        $variants = $this->generateAttributeCombinations($variantAttributes); // Panggil method genAttrCombs

        if ($variants) {
            foreach ($variants as $variant) {
                $variantParams = [
                    // Ambil dari product id induk
                    'parent_id' => $product->id, 
                    'user_id' => Auth::user()->id,
                    // Ambil sku induk kemudian tambahkan beberapa karakter terkait dengan variant
                    'sku' => $product->sku . '-' .implode('-', array_values($variant)),
                    // Akan selalu simple karena ia adalah anak dari induk product,
                    // induk product yang configurable
                    'type' => 'simple', 
                    // Ambil nama dari induk yang ditambahkan beberapa karekter,
                    // tapi karena nama kedepannya tidak hanya berdasarkan 2 attribut,
                    // kita buat sebuah logic untuk meng-handle-nya
                    'name' => $product->name . $this->convertVariantAsName($variant),
                ];
                // print_r($variantParams);exit;
                $variantParams['slug'] = Str::slug($variantParams['name']);

                $newProductVariant = Product::create($variantParams);

                $categoryIds = !empty($params['category_ids']) ? $params['category_ids'] : [];
                $newProductVariant->categories()->sync($categoryIds);

                $this->saveProductAttributeValues($newProductVariant, $variant); // Panggil method svPrdAttrVls
            }
        }
    }

    private function saveProductAttributeValues($product, $variant) // Method svPrdAttrVls
    {
        foreach (array_values($variant) as $attributeOptionID) {
            $attributeOption = AttributeOption::find($attributeOptionID);

            $attributeValueParams = [
                'product_id' => $product->id,
                'attribute_id' => $attributeOption->attribute_id,
                'text_value' => $attributeOption->name,
            ];

            ProductAttributeValue::create($attributeValueParams);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  App\Http\Requests\ProductRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductRequest $request)
    {
        $params = $request->except('_token');
        $params['slug'] = Str::slug($params['name']);
        $params['user_id'] = Auth::user()->id;

        $product = DB::transaction(function () use ($params) {
            $categoryIds = !empty($params['category_ids']) ? $params['category_ids'] : []; // Tangkap id category yang dipilih
            $product = Product::create($params);
            $product->categories()->sync($categoryIds);

            if ($params['type'] == 'configurable') { // Ketika user menambahkan product dengan type configurable
                $this->generateProductVariants($product, $params);
            }

            return $product;
        });

        if ($product) {
            Session::flash('success', 'Product has been saved!');
        } else {
            Session::flash('error', 'Product could not be saved!');
        }

        // return redirect('admin/products');
        return redirect('admin/products/'. $product->id .'/edit/');
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
        if (empty($id)) { // Kalo $id-nya null, ubah jadi add
            return redirect('admin/products/create');
        }
        
        $product = Product::findOrFail($id);
        $categories = Category::orderBy('name', 'ASC')->get();
        // Menambahkan attribute baru ke original
        // Attribute data, bukan color,size,etc
        // Cek dengan ddump
        $product->qty = isset($product->productInventory) ? $product->productInventory->qty : null;

        $this->data['categories'] = $categories->toArray();
        $this->data['product'] = $product;
        $this->data['productID'] = $product->id;
        
        // Im adding this, but lame
        // if ($product->type == 'simple') {
        //     if ($product->productInventory()->where('product_id', $product->id)->exists()) {
        //         $this->data['qty'] = $product->productInventory->qty;
        //     } else {
        //         $this->data['qty'] = null;
        //     }
        // }

        $this->data['categoryIDs'] = $product->categories->pluck('id')->toArray();
        // dd($this->data);
        return view('admin.products.form', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  App\Http\Requests\ProductRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProductRequest $request, $id)
    {
        $params = $request->except('_token'); // Ambil seluruh request kecuali token
        // dd($params);
        $params['slug'] = Str::slug($params['name']);

        $product = Product::findOrFail($id);

        $saved = false; // Flag untuk proses penyimpanan (berhasil atau tidak)
        $saved = DB::transaction(function () use ($product, $params) {
            $categoryIds = !empty($params['category_ids']) ? $params['category_ids'] : [];
            $product->update($params);
            $product->categories()->sync($categoryIds); // Kita singkronkan kembali dengan categories

            if ($product->type == 'configurable') { // Cek apakah product configurable atau simple
                $this->updateProductVariants($params); // Panggil method uptPrdVrnts
            } else {
                ProductInventory::updateOrCreate(['product_id' => $product->id], ['qty' => $params['qty']]);
            }

            return true;
        });

        if ($saved) {
            Session::flash('success', 'Product has been updated!');
        } else {
            Session::flash('error', 'Product could not be updated!');
        }

        return redirect('admin/products');
    }

    private function updateProductVariants($params) // Method uptPrdVrnts
    {
        if ($params['variants']) {
            foreach ($params['variants'] as $productParams) {
                $product = Product::find($productParams['id']);
                $product->update($productParams);

                $product->status = $params['status'];
                $product->save();

                ProductInventory::updateOrCreate(['product_id' => $product->id], ['qty' => $productParams['qty']]);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        if($product->delete()) {
            Session::flash('success', 'Product has been deleted!');
        }

        return redirect('admin/products');
    }

    /**
     * Display list of images.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function images($id)
    {
        if (empty($id)) { // Kalo $id-nya null, ubah jadi method create
            return redirect('admin/products/create');
        }

        $product = Product::findOrFail($id);

        $this->data['productID'] = $product->id;
        $this->data['productImages'] = $product->productImages;

        return view('admin.products.images', $this->data);
    }

    /**
     * Show the form for add new product image.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function add_image($id)
    {
        if (empty($id)) { // Kalo $id-nya null, kembali ke index
            return redirect('admin/products');
        }

        $product = Product::findOrFail($id);

        $this->data['productID'] = $product->id;
        $this->data['product'] = $product;

        return view('admin.products.image_form', $this->data);
    }

    /**
     * Add new image to specified product.
     *
     * @param  App\Http\Requests\ProductImageRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response 
     */  
    public function upload_image(ProductImageRequest $request, $id)
    {
        $product = Product::findOrFail($id);

        if ($request->has('image')) {
            $image = $request->file('image');
            $name = $product->slug .'_'. time(); // time() agar nama yang sama tidak tertimpa
            $fileName = $name . '.' . $image
            ->getClientOriginalExtension(); // Ambil ekstensi asli file

            $folder = '/uploads/images'; // Folder file image
            $filePath = $image->storeAs($folder, $fileName, 'public');

            $params = [
                'product_id' => $product->id,
                'path' => $filePath,
            ];

            if (ProductImage::create($params)) {
                Session::flash('success', 'Image has been uploaded!');
            } else {
                Session::flash('error', 'Image could not be uploaded!');
            }

            return redirect('admin/products/'. $id .'/images');
        }
    }

    /**
     * Remove image file and image data from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function remove_image($id)
    {
        $image = ProductImage::findOrFail($id);

        if ($image->delete()) {
            Session::flash('success', 'Image has been deleted!');
        }

        return redirect('admin/products/' . $image->product->id . '/images');
    }
}
