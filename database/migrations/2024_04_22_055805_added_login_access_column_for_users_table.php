<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddedLoginAccessColumnForUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // 'login_access' column to manage login access type:
            // 0 = both desktop and mobile, 1 = desktop only, 2 = mobile only
            $table->integer('login_access')->default(0)->comment('0 = both desktop and mobile, 1 = desktop only, 2 = mobile only');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('login_access');
        });
    }
}
