<?php

namespace App\Models;
use App\Models\BaseModel;

use App\Classes\Common;

class LoginLogs extends BaseModel
{
    protected $table = 'login_logs';

    protected $fillable = [
        'user_id',
        'user_agent',
        'ip',
        'previous_login_ip',
        'no_of_time_login',
        'last_login_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
