<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWholesalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wholesales', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('warehouse_id')->unsigned()->nullable()->default(null);
			$table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade')->onUpdate('cascade');
            $table->bigInteger('product_id')->unsigned()->nullable();
			$table->foreign('product_id')->references('id')->on('products')->onDelete('cascade')->onUpdate('cascade');
            $table->bigInteger('product_details_id')->unsigned()->nullable();
			$table->foreign('product_details_id')->references('id')->on('product_details')->onDelete('cascade')->onUpdate('cascade');
            $table->string('start_quantity');
            $table->string('end_quantity');
            $table->double('wholesale_price');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wholesales');
    }
}
