<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffCheckin extends BaseModel
{
    protected $default = ['xid'];

    protected $guarded = ['id', 'warehouse_id', 'user_id', 'created_at', 'updated_at', 'company_id', 'created_by'];

    protected $hidden = ['id', 'warehouse_id', 'user_id', 'created_at', 'updated_at', 'company_id', 'created_by'];

    protected $appends = ['xid', 'x_warehouse_id', 'x_user_id',];

    protected $fillable = [
        'id',
        'user_id',
        'date',
        'check_in_time',
        'check_out_time',
        'hours',
        'check_in_ip',
        'check_out_ip',
        'check_in_location_details',
        'check_out_location_details',
        'status',
    ];

    protected $hashableGetterFunctions = [
        'getXUserIdAttribute' => 'user_id',
        'getXWarehouseIdAttribute' => 'warehouse_id',
    ];

    protected $casts = [
        'user_id' => Hash::class . ':hash',
        'warehouse_id' => Hash::class . ':hash',
    ];
    use HasFactory;
}
