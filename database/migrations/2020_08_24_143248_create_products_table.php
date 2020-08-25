<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('sku');
            $table->string('name');
            $table->string('slug');
            $table->decimal('price', 15, 2);
            $table->decimal('weight', 10, 2);
            $table->decimal('width', 10, 2);
            $table->decimal('height', 10, 2);
            $table->decimal('depth', 10, 2);
            $table->text('short_desc');
            $table->text('description');
            $table->integer('status');

            $table->unsignedBigInteger('user_id'); // User yang terakhir edit
            $table->foreign('user_id')->references('id')->on('users'); // Relasi

            $table->timestamps(); // Update at & created at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
