<?php

namespace App\Models;
use App\Models\BaseModel;
class Wholesale extends BaseModel
{

    protected $table = 'wholesales';
    protected $default = [
        'start_quantity',
        'end_quantity',
        'wholesale_price',
        'product_id',
        'product_details_id',
        'warehouse_id'
    ];
    protected $fillable = [
        'start_quantity',
        'end_quantity',
        'wholesale_price',
        'product_id',
        'product_details_id',
        'warehouse_id'
    ];
    public function productdetails()
    {
        return $this->belongsTo(ProductDetails::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
