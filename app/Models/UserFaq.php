<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserFaq extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'user_faq';
    protected $primaryKey = 'email';
    protected $fillable = ['question_id', 'count', 'email', 'role_id'];

    public function questionAndAnswer()
    {
        return $this->belongsTo(QuestionAnswer::class, 'question_id');
    }
}
