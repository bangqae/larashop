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

    /** 
     * Method khusus yang akan dieksekusi
     * pada saat pembuatan objek (instance).
     */
    public function __construct()
    {
        parent::__construct();

        $this->data['currentAdminMenu'] = 'catalog';
        $this->data['currentAdminSubMenu'] = 'product';

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
        $configurableAttributes = $this->_getConfigurableAttributes(); // Ambil data attribute dari method getConAtts

        $this->data['categories'] = $categories->toArray();
        $this->data['product'] = null;
        $this->data['productID'] = 0; // Karena kita hanya menggunakan satu view form,
        $this->data['categoryIDs'] = []; // terdapat beberapa beberapa variabel yang harus diisi
        $this->data['configurableAttributes'] = $configurableAttributes;

        return view('admin.products.form', $this->data);
    }

    /**
	 * Get configurable attributes for products
	 *
	 * @return array
	 */
    private function _getConfigurableAttributes() // Method getConAtts
    {
        return Attribute::where('is_configurable', true)->get(); // Ambil attribute yang configurable
    }

    /**
	 * Generate attribute combination depend on the provided attributes
	 *
	 * @param array $arrays attributes
	 *
	 * @return array
	 */
    private function _generateAttributeCombinations($arrays) // Method untuk kombinasi attributes, genAttrCombs
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

    /**
	 * Convert variant attributes as variant name
	 *
	 * @param array $variant variant
	 *
	 * @return string
	 */
    private function _convertVariantAsName($variant) // Konversi variant menjadi name product
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

    /**
	 * Generate product variants for the configurable product
	 *
	 * @param Product $product product object
	 * @param array   $params  params
	 *
	 * @return void
	 */
    private function _generateProductVariants($product, $params) // Method genPrdVrnts
    {
        $configurableAttributes = $this->_getConfigurableAttributes(); // Ambil data attribute dari method getConAtts

        $variantAttributes = [];
        foreach ($configurableAttributes as $attribute) {
            $variantAttributes[$attribute->code] = $params[$attribute->code];
        }
        $variants = $this->_generateAttributeCombinations($variantAttributes); // Panggil method genAttrCombs

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
                    'name' => $product->name . $this->_convertVariantAsName($variant),
                ];
                // print_r($variantParams);exit;
                $variantParams['slug'] = Str::slug($variantParams['name']);

                $newProductVariant = Product::create($variantParams);

                $categoryIds = !empty($params['category_ids']) ? $params['category_ids'] : [];
                $newProductVariant->categories()->sync($categoryIds);

                $this->_saveProductAttributeValues($newProductVariant, $variant, $product->id); // Panggil method svPrdAttrVls
            }
        }
    }

    /**
	 * Save the product attribute values
	 *
	 * @param Product $product         product object
	 * @param array   $variant         variant
	 * @param int     $parentProductID parent product ID
	 *
	 * @return void
	 */
    private function _saveProductAttributeValues($product, $variant, $parentProductID) // Method svPrdAttrVls
    {
        foreach (array_values($variant) as $attributeOptionID) {
            $attributeOption = AttributeOption::find($attributeOptionID);

            $attributeValueParams = [
                'parent_product_id' => $parentProductID,
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
     * @param  ProductRequest  $request params
     * 
     * @return \Illuminate\Http\Response
     */
    public function store(ProductRequest $request)
    {
        $params = $request->except('_token');
        $params['slug'] = Str::slug($params['name']);
        $params['user_id'] = Auth::user()->id;

        $product = DB::transaction(
            function () use ($params) {
                $categoryIds = !empty($params['category_ids']) ? $params['category_ids'] : []; // Tangkap id category yang dipilih
                $product = Product::create($params);
                $product->categories()->sync($categoryIds); // Simpan category

                if ($params['type'] == 'configurable') { // Ketika user menambahkan product dengan type configurable
                    $this->_generateProductVariants($product, $params);
                }

                return $product;
            }
        );

        if ($product) {
            Session::flash('success', 'Product has been saved!');
        } else {
            Session::flash('error', 'Product could not be saved!');
        }

        return redirect('admin/products/'. $product->id .'/edit/');
    }

    /**
	 * Show the form for editing the specified resource.
	 *
	 * @param int $id product ID
	 *
	 * @return \Illuminate\Http\Response
	 */
    public function edit($id)
    {
        if (empty($id)) { // Kalo $id-nya null, arahkan ke method create
            return redirect('admin/products/create');
        }
        
        $product = Product::findOrFail($id);
        $categories = Category::orderBy('name', 'ASC')->get();
        // Menambahkan attribute baru (qty) ke original
        // Attribute data, bukan color,size,etc
        // Cek dengan ddump
        $product->qty = isset($product->productInventory) ? $product->productInventory->qty : null;

        $this->data['categories'] = $categories->toArray();
        $this->data['product'] = $product;
        $this->data['productID'] = $product->id;
        
        // I'm made this for adding qty to $product, but the one which indokoding made is way better
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
	 * @param ProductRequest $request params
	 * @param int            $id      product ID
	 *
	 * @return \Illuminate\Http\Response
	 */
    public function update(ProductRequest $request, $id)
    {
        $params = $request->except('_token'); // Ambil seluruh request kecuali token

        $params['slug'] = Str::slug($params['name']);

        $product = Product::findOrFail($id);

        $saved = false; // Flag untuk proses penyimpanan (berhasil atau tidak)
        $saved = DB::transaction(
            function () use ($product, $params) {
                $categoryIds = !empty($params['category_ids']) ? $params['category_ids'] : [];
                $product->update($params);
                $product->categories()->sync($categoryIds); // Kita singkronkan kembali dengan categories

                if ($product->type == 'configurable') { // Cek apakah product configurable atau simple
                    $this->_updateProductVariants($params); // Panggil method uptPrdVrnts
                } else {
                    ProductInventory::updateOrCreate(['product_id' => $product->id], ['qty' => $params['qty']]);
                }

                return true;
            }
        );

        if ($saved) {
            Session::flash('success', 'Product has been updated!');
        } else {
            Session::flash('error', 'Product could not be updated!');
        }

        return redirect('admin/products');
    }

    /**
	 * Product variants
	 *
	 * @param array $params params
	 *
	 * @return void
	 */
    private function _updateProductVariants($params) // Method uptPrdVrnts
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
	 * @param int $id product id
	 *
	 * @return void
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
	 * Show product images
	 *
	 * @param int $id product id
	 *
	 * @return void
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
	 * Show add image form
	 *
	 * @param int $id product id
	 *
	 * @return Response
	 */
    public function addImage($id)
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
	 * Upload image
	 *
	 * @param ProductImageRequest $request params
	 * @param int                 $id      product id
	 *
	 * @return Response
	 */
    public function uploadImage(ProductImageRequest $request, $id)
    {
        $product = Product::findOrFail($id);

        if ($request->has('image')) {
            $image = $request->file('image');
            $name = $product->slug .'_'. time(); // time() agar nama yang sama tidak tertimpa
            $fileName = $name . '.' . $image
            ->getClientOriginalExtension(); // Ambil ekstensi asli file

            $folder = ProductImage::UPLOAD_DIR. '/images'; // Folder file image
            
            $filePath = $image->storeAs($folder .'/original', $fileName, 'public');

            $resizedImage = $this->_resizeImage($image, $fileName, $folder);

            $params = array_merge(
                [
                    'product_id' => $product->id,
                    'path' => $filePath,
                ],
                $resizedImage
            );

            if (ProductImage::create($params)) {
                Session::flash('success', 'Image has been uploaded!');
            } else {
                Session::flash('error', 'Image could not be uploaded!');
            }

            return redirect('admin/products/'. $id .'/images');
        }
    }

    /**
	 * Resize image
	 *
	 * @param file   $image    raw file
	 * @param string $fileName image file name
	 * @param string $folder   folder name
	 *
	 * @return Response
	 */
	private function _resizeImage($image, $fileName, $folder)
	{
		$resizedImage = []; // Inisiate array for ['small','medium','large','extra-large]

		$smallImageFilePath = $folder . '/small/' . $fileName;
		$size = explode('x', ProductImage::SMALL);
		list($width, $height) = $size;

		$smallImageFile = \Image::make($image)->fit($width, $height)->stream();
		if (\Storage::put('public/' . $smallImageFilePath, $smallImageFile)) {
			$resizedImage['small'] = $smallImageFilePath;
		}
		
		$mediumImageFilePath = $folder . '/medium/' . $fileName;
		$size = explode('x', ProductImage::MEDIUM);
		list($width, $height) = $size;

		$mediumImageFile = \Image::make($image)->fit($width, $height)->stream();
		if (\Storage::put('public/' . $mediumImageFilePath, $mediumImageFile)) {
			$resizedImage['medium'] = $mediumImageFilePath;
		}

		$largeImageFilePath = $folder . '/large/' . $fileName;
		$size = explode('x', ProductImage::LARGE);
		list($width, $height) = $size;

		$largeImageFile = \Image::make($image)->fit($width, $height)->stream();
		if (\Storage::put('public/' . $largeImageFilePath, $largeImageFile)) {
			$resizedImage['large'] = $largeImageFilePath;
		}

		$extraLargeImageFilePath  = $folder . '/xlarge/' . $fileName;
		$size = explode('x', ProductImage::EXTRA_LARGE);
		list($width, $height) = $size;

		$extraLargeImageFile = \Image::make($image)->fit($width, $height)->stream();
		if (\Storage::put('public/' . $extraLargeImageFilePath, $extraLargeImageFile)) {
			$resizedImage['extra_large'] = $extraLargeImageFilePath;
		}

		return $resizedImage;
	}

	/**
	 * Remove image
	 *
	 * @param int $id image id
	 *
	 * @return Response
	 */
    public function removeImage($id)
    {
        $image = ProductImage::findOrFail($id);

        if ($image->delete()) {
            Session::flash('success', 'Image has been deleted!');
        }

        return redirect('admin/products/' . $image->product->id . '/images');
    }
}
