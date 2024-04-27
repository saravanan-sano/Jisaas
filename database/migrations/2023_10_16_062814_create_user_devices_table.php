<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
			$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
			$table->string('name');
			$table->string('email');
			$table->string('device_id')->nullable();
			$table->string('ip')->nullable();
			$table->string('session_id')->nullable();
			$table->string('operating_system')->nullable();
			$table->string('browser')->nullable();
			$table->string('userAgent')->nullable();
			$table->binary('token');
			$table->binary('refresh_token')->nullable();
			$table->string('location')->nullable();
			$table->integer('status')->default(1);
            $table->integer('draft')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_devices');
    }
}
