<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoginLogsTable extends Migration
{
    public function up()
    {
        Schema::create('login_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->longText('user_agent')->nullable();
            $table->longText('ip')->nullable();
            $table->longText('previous_login_ip')->nullable();
            $table->unsignedBigInteger('no_of_time_login')->default(0);
            $table->timestamp('last_login_at');
            // Add more columns as per your requirements

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('login_logs');
    }
}
