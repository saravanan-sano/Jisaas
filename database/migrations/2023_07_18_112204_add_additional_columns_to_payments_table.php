<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdditionalColumnsToPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('upipayments', function (Blueprint $table) {
            $table->string('createdAt')->nullable();
            $table->string('merchant_name')->nullable();
            $table->string('merchant_upi_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('upipayments', function (Blueprint $table) {
            $table->dropColumn('createdAt');
            $table->dropColumn('merchant_name');
            $table->dropColumn('merchant_upi_id');
        });
    }
}
