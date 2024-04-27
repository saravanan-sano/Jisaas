<?php

namespace App\Models;

use App\Casts\Hash;
use App\Classes\Common;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Builder;

class Payment extends BaseModel
{
    protected $table = 'payments';

    protected $default = ['xid'];

    protected $guarded = ['id', 'warehouse_id', 'payment_type','staff_user_id', 'created_at', 'updated_at'];

    protected $hidden = ['id', 'payment_mode_id','staff_user_id', 'user_id'];

    protected $appends = ['xid', 'x_payment_mode_id', 'x_user_id','x_staff_user_id',];

    protected $filterable = ['id', 'payment_number', 'payment_type', 'user_id','staff_user_id','date'];

    // protected $dates = ['date'];

    protected $hashableGetterFunctions = [
        'getXPaymentModeIdAttribute' => 'payment_mode_id',
        'getXUserIdAttribute' => 'user_id',
        'getXStaffUserIdAttribute' => 'staff_user_id',
    ];

    protected $casts = [
        'payment_mode_id' => Hash::class . ':hash',
        'user_id' => Hash::class . ':hash',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);

        static::addGlobalScope('current_warehouse', function (Builder $builder) {
            $warehouse = warehouse();

            if ($warehouse) {
                $builder->where('payments.warehouse_id', $warehouse->id);
            }
        });
    }

    public function paymentMode()
    {
        return $this->belongsTo(PaymentMode::class);
    }

    public function user()
    {
        return $this->belongsTo(StaffMember::class)->withoutGlobalScopes();
    }

    public function staffMember()
    {
        return $this->belongsTo(StaffMember::class, 'staff_user_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'user_id');
    }

}
