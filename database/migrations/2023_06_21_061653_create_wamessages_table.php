<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWamessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wamessages', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('warehouse_id')->unsigned()->nullable()->default(null);
            $table->bigInteger('message_type')->unsigned()->nullable();
            $table->string('phone')->nullable()->default(null);
            $table->string('message');  
            $table->bigInteger('delivery_status')->unsigned()->nullable();
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('draft')->default(0);
            $table->integer('created_by')->default(0);
            $table->integer('updated_by')->default(0);          
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
        Schema::dropIfExists('wamessages');
    }
}
