<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInvoicedetailsNameToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('delivery_to')->after('terms_condition')->nullable();
            $table->string('place_of_supply')->after('delivery_to')->nullable();
            $table->string('reverse_charge')->after('place_of_supply')->nullable();
            $table->string('gr_rr_no')->after('reverse_charge')->nullable();
            $table->string('transport')->after('gr_rr_no')->nullable();
            $table->string('vechile_no')->after('transport')->nullable();
            $table->string('station')->after('vechile_no')->nullable();
            $table->string('buyer_order_no')->after('station')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('delivery_to');
            $table->dropColumn('place_of_supply');
            $table->dropColumn('reverse_charge');
            $table->dropColumn('gr_rr_no');
            $table->dropColumn('transport');
            $table->dropColumn('vechile_no');
            $table->dropColumn('station');
            $table->dropColumn('buyer_order_no');
        });
    }
}
