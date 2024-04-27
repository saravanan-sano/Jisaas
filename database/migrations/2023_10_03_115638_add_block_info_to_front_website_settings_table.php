<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBlockInfoToFrontWebsiteSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('front_website_settings', function (Blueprint $table) {
            $table->text('block_widget')->after('links_widget')->nullable();

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
            $table->dropColumn('block_widget');
        });
    }
}
