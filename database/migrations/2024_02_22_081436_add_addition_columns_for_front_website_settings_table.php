<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdditionColumnsForFrontWebsiteSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('front_website_settings', function (Blueprint $table) {
            $table->string('header_line_1')->nullable()->default('');
            $table->string('header_line_2')->nullable()->default('');
            $table->text('order_thank_msg')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('front_website_settings', function (Blueprint $table) {
            $table->dropColumn('header_line_1');
            $table->dropColumn('header_line_2');
            $table->dropColumn('order_thank_msg');
        });
    }
}
