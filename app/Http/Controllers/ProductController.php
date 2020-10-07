<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\AttributeOption;
use App\Models\ProductAttributeValue;

use Illuminate\Support\Str;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->data['q'] = null;

        $this->data['categories'] = Category::parentCategories() // Categories di view sidebar
                                    ->orderBy('name', 'ASC')
                                    ->get();
        
        $this->data['minPrice'] = Product::min('price');
        $this->data['maxPrice'] = Product::max('price');

        $this->data['colors'] = AttributeOption::whereHas('attribute', function ($query) {
                                    $query->where('code', 'color')
                                        ->where('is_filterable', 1);
                                })->orderBy('name', 'ASC')->get();

        $this->data['sizes'] = AttributeOption::whereHas('attribute', function ($query) {
                                    $query->where('code', 'size')
                                        ->where('is_filterable', 1);
                                })->orderBy('name', 'asc')->get();

        $this->data['sorts'] = [
            url('products') => 'Default',
            url('products?sort=price-asc') => 'Price - Low to High',
            url('products?sort=price-desc') => 'Price - High to Low',
            url('products?sort=created_at-desc') => 'Newest to Oldest',
            url('products?sort=created_at-asc') => 'Oldest to Newest',
        ];

        $this->data['selectedSort'] = url('products');
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $products = Product::active();
        
        $products = $this->searchProducts($products, $request);
        $products = $this->filterProductsByPriceRange($products, $request);
        $products = $this->filterProductsByAttribute($products, $request);
        $products = $this->sortProducts($products, $request);
        
        $this->data['products'] = $products->paginate(9);
        return $this->load_theme('products.index', $this->data);
    }

    private function searchProducts($products, $request)
    {
        if ($q = $request->query('q')) {
            $q = str_replace('-', ' ', Str::slug($q));
            // var_dump($q);exit;
            $products = $products->whereRaw('MATCH(name, slug, short_desc, description) AGAINST (? IN NATURAL LANGUAGE MODE)', [$q]);

            $this->data['q'] = $q; // Karena awalnya memiliki value null, kita ganti ke value baru
        }

        if ($categorySlug = $request->query('category')) {
            $category = Category::where('slug', $categorySlug)->firstOrFail(); // Kalau tidak ditemukan langsung error

            $childIds = Category::childIds($category->id);
            $categoryIds = array_merge([$category->id], $childIds);

            // print_r($categoryIds);exit;
            $products = $products->whereHas('categories', function ($query) use ($categoryIds) { // Query categories ke products
                            $query->whereIn('categories.id', $categoryIds);
            });
        }

        return $products;
    }

    private function filterProductsByPriceRange($products, $request)
    {
        $lowPrice = null;
        $highPrice = null;

        if ($priceSlider = $request->query('price')) {
            $prices = explode('-', $priceSlider);

            $lowPrice = !empty($prices[0]) ? (float)$prices[0] : $this->data['minPrice'];
            $highPrice = !empty($prices[1]) ? (float)$prices[1] : $this->data['maxPrice'];

            if ($lowPrice && $highPrice) {
                $products = $products->where('price', '>=', $lowPrice)
                            ->where('price', '<=', $highPrice)
                            ->orWhereHas('variants', function ($query) use ($lowPrice, $highPrice) {
                                $query->where('price', '>=', $lowPrice)
                                    ->where('price', '<=', $highPrice);
                            });

                $this->data['minPrice'] = $lowPrice;
                $this->data['maxPrice'] = $highPrice;
            }
        }

        return $products;
    }

    private function filterProductsByAttribute($products, $request)
    {
        if ($attributOptionID = $request->query('option')) {
            $attributOption = AttributeOption::findOrFail($attributOptionID);
            
            $products = $products->whereHas('ProductAttributeValues', function ($query) use ($attributOption) {
                                    $query->where('attribute_id', $attributOption->attribute_id)
                                        ->where('text_value', $attributOption->name);
            });
        }

        return $products;
    }

    private function sortProducts($products, $request)
    {
        if ($sort = preg_replace('/\s+/', '', $request->query('sort'))) {
            // var_dump($sort);exit;
            $avaiableSorts = ['price', 'created_at'];
            $avaiableOrder = ['asc', 'desc'];
            $sortAndOrder = explode('-', $sort);

            $sortBy = strtolower($sortAndOrder[0]);
            $orderBy = strtolower($sortAndOrder[1]);

            if (in_array($sortBy, $avaiableSorts) && in_array($orderBy, $avaiableOrder)) {
                $products = $products->orderBy($sortBy, $orderBy);
            }

            $this->data['selectedSort'] = url('products?sort='. $sort);
        }

        return $products;
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $slug
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $product = Product::active()->where('slug', $slug)->first();

        if (!$product) {
            return redirect('products');
        }

        if ($product->type == 'configurable') {
            $this->data['colors'] = ProductAttributeValue::getAttributeOptions($product, 'color')->pluck('text_value', 'text_value');
            $this->data['sizes'] = ProductAttributeValue::getAttributeOptions($product, 'size')->pluck('text_value', 'text_value');
        }

        $this->data['product'] = $product;

        return $this->load_theme('products.show', $this->data);
    }
}
