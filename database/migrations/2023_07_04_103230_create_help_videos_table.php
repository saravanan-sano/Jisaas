<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHelpVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('help_videos', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('language')->unsigned()->nullable();
            $table->string('pagename')->nullable()->default(null);
            $table->string('video_url');  
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
        Schema::dropIfExists('help_videos');
    }
}
