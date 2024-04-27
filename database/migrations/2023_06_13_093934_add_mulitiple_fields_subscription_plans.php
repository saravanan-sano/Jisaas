<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMulitipleFieldsSubscriptionPlans extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->integer('max_orders')->unsigned()->default(0)->after('max_products');
            $table->integer('max_purchases')->unsigned()->default(0)->after('max_orders');
            $table->integer('max_expenses')->unsigned()->default(0)->after('max_purchases');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn(['max_orders','max_purchases','max_expenses']);
        });
    }
}
