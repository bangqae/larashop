<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddParentIdAndTypeToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->after('id')->nullable(); // Nanti untuk product yang ber-type simple atau induk, maka parent_id-nya null
            $table->string('type')->after('sku');

            $table->foreign('parent_id')->references('id')->on('products'); // Column parent_id mereferensi ke column id pada tabel products itu sendiri
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign('products_parent_id_foreign');
            $table->dropColumn('parent_id');
            $table->dropColumn('type');
        });
    }
}
