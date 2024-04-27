<?php

namespace App\Models;

use App\Classes\Common;
use App\Models\BaseModel;
use App\Models\Product;
use App\Scopes\CompanyScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class FrontProductCard extends BaseModel
{
    protected $table = 'front_product_cards';

    protected $default = ['xid', 'image', 'image_url'];

    protected $guarded = ['id'];

    protected $hidden = ['id', 'products'];

    protected $casts = [
        'products' => 'array'
    ];

    protected $filterable = ['title'];

    protected $appends = ['xid', 'x_products', 'products_details','image_url'];

    protected $hashableGetterArrayFunctions = [
        'getXProductsAttribute' => 'products',
    ];

    public function getImageUrlAttribute()
    {
        $productCardLogoPath = Common::getFolderPath('product_cards');

        return $this->image == null ? asset('images/category.png') : Common::getFileUrl($productCardLogoPath, $this->image);
    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new CompanyScope);

        static::addGlobalScope('current_warehouse', function (Builder $builder) {
            $warehouse = warehouse();

            if ($warehouse) {
                $builder->where('front_product_cards.warehouse_id', $warehouse->id);
            }
        });
    }

    public function getProductsDetailsAttribute()
    {
        $products = Product::select('id', 'name', 'slug', 'image', 'description', 'brand_id', 'category_id')
            ->with(['details:id,product_id,sales_price,mrp,tax_id,sales_tax_type,current_stock,shipping_price,purchase_price', 'brand:id,name,slug,image', 'category:id,name,slug,image', 'front_wholesale'])
            ->whereIn('products.id', $this->products)
            ->get();

        $products1 = $products->map(function ($product) {
            if (isset($product->details)) {
                foreach ($product->details as &$detail) {


                    // Check if the tax relationship and tax object exist
                    if (isset($detail['tax_id'])) {
                        $tax = Tax::find($detail['tax_id']);
                        $taxType = $detail['sales_tax_type'];
                        $taxRate = $tax->rate;
                        $unitPrice = $detail['sales_price'];

                        $singleUnitPrice = $unitPrice;

                        if ($taxType == 'inclusive') {
                            $subTotal = $singleUnitPrice;
                            $singleUnitPrice = ($singleUnitPrice * 100) / (100 + $taxRate);
                            $taxAmount = ($singleUnitPrice) * ($taxRate / 100);
                        } else {
                            $taxAmount = ($singleUnitPrice * ($taxRate / 100));
                            $subTotal = $singleUnitPrice + $taxAmount;
                            $singleUnitPrice = $subTotal;
                        }

                        // Update the sales_price in the details
                        $detail['sales_price'] = $subTotal;
                    }
                }

                // Make sure to unset the reference to avoid potential issues
                unset($detail);
            }

            return $product;
        });




        return $products1;
    }
}
