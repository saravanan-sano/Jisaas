<?php

namespace App\Models;

use App\Models\BaseModel;
use App\Scopes\CompanyScope;

class PaymentMode extends BaseModel
{
    protected $table = 'payment_modes';

    protected $default = ['xid', 'name','is_default'];

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $filterable = ['name'];

    protected $hidden = ['id'];

    protected $appends = ['xid'];

    protected $casts = [
        'credentials' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'payment_mode_id', 'id');
    }
}
