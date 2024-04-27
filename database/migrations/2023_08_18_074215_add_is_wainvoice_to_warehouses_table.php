<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsWainvoiceToWarehousesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->string('is_wainvoice')->default(0);
            $table->string('first_invoice_no')->default(00001);
            $table->string('prefix_invoice')->default('YESERP');
            $table->string('invoice_spliter')->default('/');
            $table->string('suffix_invoice')->nullable();
            $table->string('reset_invoice')->nullable();
            $table->string('invoice_started')->default(0);
            $table->string('daily_reset')->default(0);
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
            $table->dropColumn('is_wainvoice');
            $table->dropColumn('prefix_invoice');
            $table->dropColumn('invoice_spliter');
            $table->dropColumn('suffix_invoice');
            $table->dropColumn('reset_invoice');
            $table->dropColumn('first_invoice_no'); 
            $table->dropColumn('invoice_started'); 
            $table->dropColumn('daily_reset');            
        });
    }
}
