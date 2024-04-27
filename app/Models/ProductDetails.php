<?php

namespace App\Models;

use App\Casts\Hash;
use App\Models\BaseModel;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class ProductDetails extends BaseModel
{
    protected $table = 'product_details';

    protected $default = ['xid','mrp','purchase_price','sales_price'];

    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $hidden = ['id', 'warehouse_id', 'product_id', 'tax_id'];

    protected $appends = ['xid', 'x_warehouse_id', 'x_product_id', 'x_tax_id'];

    protected $filterable = ['id'];

    protected $hashableGetterFunctions = [
        'getXWarehouseIdAttribute' => 'warehouse_id',
        'getXProductIdAttribute' => 'product_id',
        'getXTaxIdAttribute' => 'tax_id',
    ];

    protected $casts = [
        'warehouse_id' => Hash::class . ':hash',
        'product_id' => Hash::class . ':hash',
        'tax_id' => Hash::class . ':hash',
    ];



    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class);
    }
    public function wholesale()
    {
        $warehouse = warehouse();
      
        return $this->hasMany(Wholesale::class,'product_id', 'product_id')->where('warehouse_id', $warehouse->id)->orderBy('id','asc');
    //    return $this->hasMany(Wholesale::where('product_id', 'product_id')->orderBy('id','asc'));

    //    return $this->hasMany(Wholesale::class,['product_id'='product_id','warehouse_id' = 'warehouse_id'])->orderBy('id','asc');
    }


    public function wholesale1()
    {
        $warehouse = warehouse();

        return $this->hasMany(Wholesale::class)
            ->where('product_id', 'id')
            ->where('warehouse_id', $warehouse->id);
    }


}
