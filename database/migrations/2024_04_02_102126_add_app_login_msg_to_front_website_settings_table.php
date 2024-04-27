<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAppLoginMsgToFrontWebsiteSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('front_website_settings', function (Blueprint $table) {
            $table->text('app_login_msg')->nullable()->after('app_greeting_msg');
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
            $table->dropColumn('app_login_msg');
        });
    }
}
