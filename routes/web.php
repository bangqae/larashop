<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'HomeController@index');
Route::get('/products', 'ProductController@index');
Route::get('/product/{slug}', 'ProductController@show');
Route::get('/products/quick-view/{slug}', 'ProductController@quickView');

Route::get('/carts', 'CartController@index');
Route::post('/carts', 'CartController@store');
Route::post('/carts/update', 'CartController@update');
Route::get('/carts/remove/{cartID}', 'CartController@destroy');

Route::get('orders/checkout', 'OrderController@checkout');
Route::post('orders/checkout', 'OrderController@doCheckout');
Route::post('orders/shipping-cost', 'OrderController@shippingCost');
Route::post('orders/set-shipping', 'OrderController@setShipping');
Route::get('orders/received/{orderID}', 'OrderController@received');
Route::get('orders/cities', 'OrderController@cities');

Route::post('payments/notification', 'PaymentController@notification');
Route::get('payments/completed', 'PaymentController@completed');
Route::get('payments/unfinish', 'PaymentController@unfinish');
Route::get('payments/failed', 'PaymentController@failed');

Route::group(
    ['namespace' => 'Admin', 'prefix' => 'admin', 'middleware' => ['auth']],
    function () {
        Route::get('dashboard', 'DashboardController@index')->name('dashboard.index');
        
        Route::resource('categories', 'CategoryController');
        
        Route::resource('products', 'ProductController');
        Route::get('products/{productID}/images', 'ProductController@images')->name('products.images');
        Route::get('products/{productID}/add-image', 'ProductController@addImage')->name('products.add_image');
        Route::post('products/images/{productID}', 'ProductController@uploadImage')->name('products.upload_image');
        Route::delete('products/images/{imageID}', 'ProductController@removeImage')->name('products.remove_image');

        Route::resource('attributes', 'AttributeController');
        // Menampilkan list option untuk attribute tertentu
        Route::get('attributes/{attributeID}/options', 'AttributeController@options')->name('attributes.options');
        // Menampilkan form tambah option
        Route::get('attributes/{attributeID}/add-option', 'AttributeController@add_option')->name('attributes.add_option'); // Gak kepake hehe
        // Menyimpan option sebuah attribute
        Route::post('attributes/options/{attributeID}', 'AttributeController@store_option')->name('attributes.store_option');
        // Menghapus option dari attribute tertentu
        Route::delete('attributes/options/{optionID}', 'AttributeController@remove_option')->name('attributes.remove_option');
        // Menampilkan form edit option
        Route::get('attributes/options/{optionID}/edit', 'AttributeController@edit_option')->name('attributes.edit_option');
        // Meng-update option sebuah attribute
        Route::put('attributes/options/{optionID}', 'AttributeController@update_option')->name('attributes.update_options');

        Route::resource('roles', 'RoleController');
        Route::resource('users', 'UserController');

        Route::get('orders/trashed', 'OrderController@trashed');
		Route::get('orders/restore/{orderID}', 'OrderController@restore');
		Route::resource('orders', 'OrderController');
		Route::get('orders/{orderID}/cancel', 'OrderController@cancel');
		Route::put('orders/cancel/{orderID}', 'OrderController@doCancel');
		Route::post('orders/complete/{orderID}', 'OrderController@doComplete');

		Route::resource('shipments', 'ShipmentController');
    }
);

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
