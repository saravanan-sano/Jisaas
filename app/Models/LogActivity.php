<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogActivity extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'log_datetime',
        'log_type',
        'table_name',
        'request_info',
        'data',
    ];
}
