<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQrandupiNameToWarehousesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->string('gst_in_no')->after('signature')->nullable();
            $table->string('qr_code')->after('gst_in_no')->nullable();
            $table->string('upi_id')->after('qr_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn('gst_in_no');
            $table->dropColumn('qr_code');
            $table->dropColumn('upi_id');
        });
    }
}
