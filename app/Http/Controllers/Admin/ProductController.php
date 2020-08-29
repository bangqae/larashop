<?php

namespace App\Http\Controllers\Admin;

use DB;
use Str;
// use Illuminate\Http\Request;
use Auth;
use Session;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Http\Requests\ProductImageRequest;

class ProductController extends Controller
{

    public function __construct()
    {
        $this->data['statuses'] = Product::statuses();
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

        $this->data['categories'] = $categories->toArray();
        $this->data['product'] = null;
        $this->data['productID'] = 0;
        $this->data['categoryIDs'] = [];

        return view('admin.products.form', $this->data);
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

        $saved = false;
        $saved = DB::transaction(function () use ($params) {
            $product = Product::create($params);
            $product->categories()->sync($params['category_ids']);

            return true;
        });

        if ($saved) {
            Session::flash('success', 'Product has been saved!');
        } else {
            Session::flash('error', 'Product could not be saved!');
        }

        return redirect('admin/products');
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

        $this->data['categories'] = $categories->toArray();
        $this->data['product'] = $product;
        $this->data['productID'] = $product->id;
        $this->data['categoryIDs'] = $product->categories->pluck('id')->toArray();

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
            $product->update($params);
            $product->categories()->sync($params['category_ids']); // Kita singkronkan kembali dengan categories

            return true;
        });

        if ($saved) {
            Session::flash('success', 'Product has been edited!');
        } else {
            Session::flash('error', 'Product could not be edited!');
        }

        return redirect('admin/products');
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
