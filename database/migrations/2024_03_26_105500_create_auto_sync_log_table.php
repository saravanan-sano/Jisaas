<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAutoSyncLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auto_sync_logs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('company_id')->unsigned()->nullable()->default(null);
            $table->bigInteger('warehouse_id')->unsigned()->nullable()->default(null);
            $table->bigInteger('user_id')->unsigned()->nullable()->default(null);
            $table->date('sync_date')->nullable()->default(null);
            $table->time('sync_time')->nullable()->default(null);
            $table->boolean('sync_status')->default(0);
            $table->string('product_count')->nullable()->default(null);
            $table->boolean('status')->default(0);
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
        Schema::dropIfExists('auto_sync_logs');
    }
}
