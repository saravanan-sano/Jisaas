<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionSimilarityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('question_similarity', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->uuid('question_id');
            $table->foreign('question_id')
                ->references('id')
                ->on('question_and_answer')
                ->cascade('delete');

            $table->uuid('related_question_id');
            $table->foreign('related_question_id')
                ->references('id')
                ->on('question_and_answer')
                ->cascade('delete');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('question_similarity');
    }
}
