<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHsnSacCodeToProductsTable extends Migration
{
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('hsn_sac_code')->nullable()->default(null);
            $table->string('product_code')->nullable()->default(null)->after('hsn_sac_code');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('product_code');
            $table->dropColumn('hsn_sac_code');
        });
    }
}