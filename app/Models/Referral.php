<?php

namespace App\Models;

use App\Classes\Common;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Trebol\Entrust\Traits\EntrustUserTrait;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Casts\Hash;
use Illuminate\Support\Facades\Hash as FacadesHash;

class Referral extends BaseModel implements AuthenticatableContract, JWTSubject
{
    use Notifiable, EntrustUserTrait, Authenticatable, HasFactory;

    protected  $table = 'users';

    protected $default = ["xid", "name", "phone", "profile_image", "tax_number"];

    protected $guarded = ['id', 'warehouse_id', 'is_superadmin', 'opening_balance', 'opening_balance_type', 'credit_limit', 'credit_period', 'role_id', 'created_by', 'is_walkin_customer', 'created_at', 'updated_at'];

    protected $hidden = ['id', 'role_id', 'warehouse_id', 'password', 'remember_token'];

    protected $appends = ['xid', 'profile_image_url', 'x_warehouse_id'];

    protected $filterable = ['name', 'user_type', 'email', 'status', 'phone'];

    protected $hashableGetterFunctions = [
        'getXWarehouseIdAttribute' => 'warehouse_id',
    ];

    protected $casts = [
        'warehouse_id' => Hash::class . ':hash',
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);

        static::addGlobalScope('type', function (Builder $builder) {
            $builder->where('users.user_type', '=', 'referral');
        });
    }

    public function setPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['password'] = FacadesHash::make($value);
        }
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function setUserTypeAttribute($value)
    {
        $this->attributes['user_type'] = 'referral';
    }

    public function getProfileImageUrlAttribute()
    {
        $userImagePath = Common::getFolderPath('userImagePath');

        return $this->profile_image == null ? asset('images/user.png') : Common::getFileUrl($userImagePath, $this->profile_image);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
    public function orders()
    {
        return $this->hasMany(Order::class, 'referral_id', 'id');
    }

    public function details()
    {
        return $this->belongsTo(UserDetails::class, 'id', 'user_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
