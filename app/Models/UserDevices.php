<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDevices extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'user_devices';
    protected $fillable = [
        'user_id', 'name', 'email', 'device_id', 'location', 'created_at', 'updated_at', 'operating_system',
        'userAgent',
        'ip',
        'session_id',
        'refresh_token',
        'browser',
        'token'
    ];
}
