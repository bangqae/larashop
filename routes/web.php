<?php

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

Route::get('/', function () {
    return view('welcome');
});

Route::group(
    ['namespace' => 'Admin', 'prefix' => 'admin', 'middleware' => ['auth']],
    function () {
        Route::get('dashboard', 'DashboardController@index');
        Route::resource('categories', 'CategoryController');
        
        Route::resource('products', 'ProductController');
        Route::get('products/{productID}/images', 'ProductController@images');
        Route::get('products/{productID}/add-image', 'ProductController@add_image');
        Route::post('products/images/{productID}', 'ProductController@upload_image');
        Route::delete('products/images/{imageID}', 'ProductController@remove_image');

        Route::resource('attributes', 'AttributeController');
        // Menampilkan list option untuk attribute tertentu
        Route::get('attributes/{attributeID}/options', 'AttributeController@options');
        // Menampilkan form tambah option
        Route::get('attributes/{attributeID}/add-option', 'AttributeController@add_option'); // Gak kepake hehe
        // Menyimpan option sebuah attribute
        Route::post('attributes/options/{attributeID}', 'AttributeController@store_option');
        // Menghapus option dari attribute tertentu
        Route::delete('attributes/options/{optionID}', 'AttributeController@remove_option');
        // Menampilkan form edit option
        Route::get('attributes/options/{optionID}/edit', 'AttributeController@edit_option');
        // Meng-update option sebuah attribute
        Route::put('attributes/options/{optionID}', 'AttributeController@update_option');
    }
);

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
