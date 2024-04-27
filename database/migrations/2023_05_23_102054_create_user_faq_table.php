<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserFaqTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_faq', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->uuid('question_id');
            $table->foreign('question_id')
                    ->references('id')
                    ->on('question_and_answer')
                    ->cascade('delete');
                    
            $table->integer('count')->nullable();
            $table->string('email');
            $table->integer('role_id')->nullable();

        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_faq');
    }
}
