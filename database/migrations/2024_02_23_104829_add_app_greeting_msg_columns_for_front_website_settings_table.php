<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAppGreetingMsgColumnsForFrontWebsiteSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('front_website_settings', function (Blueprint $table) {
            $table->text('app_greeting_msg')->nullable();
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
            $table->dropColumn('app_greeting_msg');
        });
    }
}
