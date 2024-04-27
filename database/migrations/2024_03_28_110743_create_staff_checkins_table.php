<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStaffCheckinsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    public function up()
    {
        Schema::create('staff_checkins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('user_id');
            $table->date('date');
            $table->time('check_in_time');
            $table->time('check_out_time')->nullable();
            $table->string('hours')->nullable();
            $table->string('check_in_ip')->nullable();
            $table->text('check_in_location_details')->nullable();
            $table->string('check_out_ip')->nullable();
            $table->text('check_out_location_details')->nullable();
            $table->string('type')->nullable();
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('status')->default(1);
            $table->timestamps();

            // Assuming 'users' table exists, you need to define the foreign key constraint for 'user_id' accordingly
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('staff_checkins');
    }
}
