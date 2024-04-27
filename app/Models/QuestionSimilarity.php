<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionSimilarity extends Model
{
    use HasFactory;
    protected $table = 'question_similarity';
    public $timestamps = false;
    protected $fillable = ['question_id', 'related_question_id'];

    public function question()
    {
        return $this->belongsTo(QuestionAnswer::class, 'question_id');
    }

    public function relatedQuestion()
    {
        return $this->belongsTo(QuestionAnswer::class, 'related_question_id');
    }
}
