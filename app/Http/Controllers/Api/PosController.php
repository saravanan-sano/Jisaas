<?php

namespace App\Http\Controllers\Api;

use App\Classes\Common;
use App\Http\Controllers\ApiBaseController;
use App\Http\Requests\Api\Order\PosRequest;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Settings;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\User;
use App\Models\UserDetails;
use App\Models\Warehouse;
use App\Models\PaymentMode;
use Carbon\Carbon;
use Examyou\RestAPI\ApiResponse;
use Illuminate\Support\Facades\Log;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Vinkla\Hashids\Facades\Hashids;
use Square\SquareClient;
use Square\Environment;
use Square\Exceptions\ApiException;
use Illuminate\Support\Facades\Cache;

class PosController extends ApiBaseController
{
    public function posProducts()
    {
        $request = request();
        $allProducs = [];
        $warehouse = warehouse();
        $warehouseId = $warehouse->id;
        $company = company();
        $timezone = $company->timezone;

        // $topSellingProducts = Common::getTopProducts();

        // get default products

        $products = Product::select(
            'products.id',
            'products.name',
            'products.image',
            'product_details.sales_price',
            'products.category_id',
            'products.unit_id',
            'product_details.sales_tax_type',
            'product_details.tax_id',
            'product_details.current_stock',
            'taxes.rate',
            'products.item_code',
            'products.is_default',
            'product_details.is_shipping',
            'product_details.shipping_price',
            'product_details.purchase_price',
            'product_details.is_wholesale_only'

        )->with('wholesale1')
            ->join('product_details', 'product_details.product_id', '=', 'products.id')
            ->leftJoin('taxes', 'taxes.id', '=', 'product_details.tax_id')
            ->join('units', 'units.id', '=', 'products.unit_id')
            ->where('product_details.warehouse_id', '=', $warehouseId)
            ->where('product_details.current_stock', '>', 0);


        if ($warehouse->products_visibility == 'warehouse') {
            $products->where('products.warehouse_id', '=', $warehouse->id);
        }

        $products = $products->where('products.is_default', '=', 1)->get();

        foreach ($products as $product) {

            $stockQuantity = $product->current_stock;
            $unit = $product->unit_id != null ? Unit::find($product->unit_id) : null;
            $tax = $product->tax_id != null ? Tax::find($product->tax_id) : null;
            $taxType = $product->sales_tax_type;

            $unitPrice = $product->sales_price;
            $singleUnitPrice = $unitPrice;

            if ($product->rate != '') {
                $taxRate = $product->rate;

                if ($product->sales_tax_type == 'inclusive') {
                    $subTotal = $singleUnitPrice;
                    $singleUnitPrice =  ($singleUnitPrice * 100) / (100 + $taxRate);
                    $taxAmount = ($singleUnitPrice) * ($taxRate / 100);
                } else {
                    $taxAmount =  ($singleUnitPrice * ($taxRate / 100));
                    $subTotal = $singleUnitPrice + $taxAmount;
                }
            } else {
                $taxAmount = 0;
                $taxRate = 0;
                $subTotal = $singleUnitPrice;
            }

            if ($product->sales_price != $request->sales_price) {
                if ($product->price_history != '') {
                    $oldPrice = json_decode($product->price_history);
                    array_push($oldPrice, ['sale_price' => $product->sales_price, 'purchase_price' => $product->purchase_price, 'mrp' => $product->mrp, 'date' => Carbon::now()->tz($timezone)]);
                    $price_history = $oldPrice;
                } else {
                    $price_history = json_encode([['sale_price' => $product->sales_price, 'purchase_price' => $product->purchase_price, 'mrp' => $product->mrp, 'date' => Carbon::now()->tz($timezone)]]);
                }
            }

            $allProducs[] = [
                'item_id'    =>  '',
                'xid'    =>  $product->xid,
                'name'    =>  $product->name,
                'image'    =>  $product->image,
                'item_code' => $product->item_code,
                'image_url'    =>  $product->image_url,
                "x_category_id" => $product->category_id != null ? $this->getHashFromId($product->category_id) : null,
                "x_brand_id" => $product->brand_id != null ? $this->getHashFromId($product->brand_id) : null,
                'discount_rate'    =>  0,
                'total_discount'    =>  0,
                'x_tax_id'    => $tax ? $tax->xid : null,
                'tax_type'    =>  $taxType,
                'tax_rate'    =>  $taxRate,
                'total_tax'    =>  $taxAmount,
                'x_unit_id'    =>  $unit ? $unit->xid : null,
                'unit'    =>  $unit,
                'unit_price'    =>  $unitPrice,
                'single_unit_price'    =>  $singleUnitPrice,
                'subtotal'    =>  $subTotal,
                'quantity'    =>  1,
                'stock_quantity'    =>  $stockQuantity,
                'unit_short_name'    =>  $unit ? $unit->short_name : '',
                // Latest Added Fields
                'price_history' => $price_history,
                'wholesale' => $product->wholesale1,
                'is_wholesale_only' => $product->is_wholesale_only,
                'is_shipping' => $product->is_shipping,
                'shipping_price' => $product->shipping_price,
                'purchase_price' => $product->purchase_price,
            ];
        }



        if ($request->has('category_id') && $request->category_id != "") {
            $products = Product::select(
                'products.id',
                'products.name',
                'products.image',
                'product_details.sales_price',
                'products.category_id',
                'products.unit_id',
                'product_details.sales_tax_type',
                'product_details.tax_id',
                'product_details.current_stock',
                'taxes.rate',
                'products.item_code',
                'product_details.is_shipping',
                'product_details.shipping_price',
                'product_details.purchase_price',
                'product_details.is_wholesale_only'
            )->with('wholesale1')
                ->join('product_details', 'product_details.product_id', '=', 'products.id')
                ->leftJoin('taxes', 'taxes.id', '=', 'product_details.tax_id')
                ->join('units', 'units.id', '=', 'products.unit_id')
                ->where('product_details.warehouse_id', '=', $warehouseId);

            if ($warehouse->products_visibility == 'warehouse') {
                $products->where('products.warehouse_id', '=', $warehouse->id);
            }

            // Category Filters
            if ($request->has('category_id') && $request->category_id != "") {
                $categoryId = $this->getIdFromHash($request->category_id);
                $products = $products->where('category_id', '=', $categoryId);
            }

            // Brand Filters
            if ($request->has('brand_id') && $request->brand_id != "") {
                $brandId = $this->getIdFromHash($request->brand_id);
                $products = $products->where('brand_id', '=', $brandId);
            }


            $products =    $products->get();


            foreach ($products as $product) {
                $stockQuantity = $product->current_stock;
                $unit = $product->unit_id != null ? Unit::find($product->unit_id) : null;
                $tax = $product->tax_id != null ? Tax::find($product->tax_id) : null;
                $taxType = $product->sales_tax_type;

                $unitPrice = $product->sales_price;
                $singleUnitPrice = $unitPrice;

                if ($product->rate != '') {
                    $taxRate = $product->rate;

                    if ($product->sales_tax_type == 'inclusive') {
                        $subTotal = $singleUnitPrice;
                        $singleUnitPrice =  ($singleUnitPrice * 100) / (100 + $taxRate);
                        $taxAmount = ($singleUnitPrice) * ($taxRate / 100);
                    } else {
                        $taxAmount =  ($singleUnitPrice * ($taxRate / 100));
                        $subTotal = $singleUnitPrice + $taxAmount;
                    }
                } else {
                    $taxAmount = 0;
                    $taxRate = 0;
                    $subTotal = $singleUnitPrice;
                }

                if ($product->sales_price != $request->sales_price) {
                    if ($product->price_history != '') {
                        $oldPrice = json_decode($product->price_history);
                        array_push($oldPrice, ['sale_price' => $product->sales_price, 'purchase_price' => $product->purchase_price, 'mrp' => $product->mrp, 'date' => Carbon::now()->tz($timezone)]);
                        $price_history = $oldPrice;
                    } else {
                        $price_history = json_encode([['sale_price' => $product->sales_price, 'purchase_price' => $product->purchase_price, 'mrp' => $product->mrp, 'date' => Carbon::now()->tz($timezone)]]);
                    }
                }

                $allProducs[] = [
                    'item_id'    =>  '',
                    'xid'    =>  $product->xid,

                    'name'    =>  $product->name,
                    'image'    =>  $product->image,
                    'image_url'    =>  $product->image_url,
                    "x_category_id" => $product->category_id != null ? $this->getHashFromId($product->category_id) : null,
                    "x_brand_id" => $product->brand_id != null ? $this->getHashFromId($product->brand_id) : null,
                    'discount_rate'    =>  0,
                    'total_discount'    =>  0,
                    'x_tax_id'    => $tax ? $tax->xid : null,
                    'tax_type'    =>  $taxType,
                    'tax_rate'    =>  $taxRate,
                    'total_tax'    =>  $taxAmount,
                    'x_unit_id'    =>  $unit ? $unit->xid : null,
                    'unit'    =>  $unit,
                    'unit_price'    =>  $unitPrice,
                    'single_unit_price'    =>  $singleUnitPrice,
                    'subtotal'    =>  $subTotal,
                    'quantity'    =>  1,
                    'stock_quantity'    =>  $stockQuantity,
                    'unit_short_name'    =>  $unit ? $unit->short_name : '',
                    'item_code' => $product->item_code,
                    'price_history' => $price_history,
                    'wholesale' => $product->wholesale1,
                    // Latest Added Fields
                    'is_wholesale_only' => $product->is_wholesale_only,
                    'is_shipping' => $product->is_shipping,
                    'shipping_price' => $product->shipping_price,
                    'purchase_price' => $product->purchase_price,
                ];
            }
            $data = [
                'products' => $allProducs,
                'Count' => count($allProducs),
            ];

            return ApiResponse::make('Data fetched11', $data);
        }

        if ($request->has('fullproduct') && $request->fullproduct != "") {
            $products = Product::select(
                'products.id',
                'products.name',
                'products.item_code',
                'products.image',
                'products.category_id',
                'product_details.sales_price',
                'products.unit_id',
                'product_details.sales_tax_type',
                'product_details.tax_id',
                'product_details.current_stock',
                'taxes.rate',
                'product_details.is_shipping',
                'product_details.shipping_price',
                'product_details.purchase_price',
                'product_details.is_wholesale_only'
            )->with('wholesale1')
                ->join('product_details', 'product_details.product_id', '=', 'products.id')
                ->leftJoin('taxes', 'taxes.id', '=', 'product_details.tax_id')
                ->join('units', 'units.id', '=', 'products.unit_id')
                ->where('product_details.warehouse_id', '=', $warehouseId);
            //->where('product_details.current_stock', '>', 0);
            //hide this code by kali on 18-03-2024 based on customer request

            if ($warehouse->products_visibility == 'warehouse') {
                $products->where('products.warehouse_id', '=', $warehouse->id);
            }

            // Category Filters
            if ($request->has('category_id') && $request->category_id != "") {
                $categoryId = $this->getIdFromHash($request->category_id);
                $products = $products->where('category_id', '=', $categoryId);
            }

            // Brand Filters
            if ($request->has('brand_id') && $request->brand_id != "") {
                $brandId = $this->getIdFromHash($request->brand_id);
                $products = $products->where('brand_id', '=', $brandId);
            }


            if ($request->has('limit') && $request->limit != "") {
                $products =    $products->take($request->limit)->get();
            } else {
                $products =   $products->get();
            }


            foreach ($products as $product) {

                $stockQuantity = $product->current_stock;
                $unit = $product->unit_id != null ? Unit::find($product->unit_id) : null;
                $tax = $product->tax_id != null ? Tax::find($product->tax_id) : null;
                $taxType = $product->sales_tax_type;

                $unitPrice = $product->sales_price;
                $singleUnitPrice = $unitPrice;

                if ($product->rate != '') {
                    $taxRate = $product->rate;

                    if ($product->sales_tax_type == 'inclusive') {
                        $subTotal = $singleUnitPrice;
                        $singleUnitPrice =  ($singleUnitPrice * 100) / (100 + $taxRate);
                        $taxAmount = ($singleUnitPrice) * ($taxRate / 100);
                    } else {
                        $taxAmount =  ($singleUnitPrice * ($taxRate / 100));
                        $subTotal = $singleUnitPrice + $taxAmount;
                    }
                } else {
                    $taxAmount = 0;
                    $taxRate = 0;
                    $subTotal = $singleUnitPrice;
                }

                if ($product->sales_price != $request->sales_price) {
                    if ($product->price_history != '') {
                        $oldPrice = json_decode($product->price_history);
                        array_push($oldPrice, ['sale_price' => $product->sales_price, 'purchase_price' => $product->purchase_price, 'mrp' => $product->mrp, 'date' => Carbon::now()->tz($timezone)]);
                        $price_history = $oldPrice;
                    } else {
                        $price_history = json_encode([['sale_price' => $product->sales_price, 'purchase_price' => $product->purchase_price, 'mrp' => $product->mrp, 'date' => Carbon::now()->tz($timezone)]]);
                    }
                }

                $allProducs[] = [
                    'item_id'    =>  '',
                    'xid'    =>  $product->xid,
                    'name'    =>  $product->name,
                    'image'    =>  $product->image,
                    'image_url'    =>  $product->image_url,
                    "x_category_id" => $product->category_id != null ? $this->getHashFromId($product->category_id) : null,
                    "x_brand_id" => $product->brand_id != null ? $this->getHashFromId($product->brand_id) : null,
                    'discount_rate'    =>  0,
                    'total_discount'    =>  0,
                    'x_tax_id'    => $tax ? $tax->xid : null,
                    'tax_type'    =>  $taxType,
                    'tax_rate'    =>  $taxRate,
                    'total_tax'    =>  $taxAmount,
                    'x_unit_id'    =>  $unit ? $unit->xid : null,
                    'unit'    =>  $unit,
                    'unit_price'    =>  $unitPrice,
                    'single_unit_price'    =>  $singleUnitPrice,
                    'subtotal'    =>  $subTotal,
                    'quantity'    =>  1,
                    'stock_quantity'    =>  $stockQuantity,
                    'unit_short_name'    =>  $unit ? $unit->short_name : '',
                    'item_code' => $product->item_code,
                    'price_history' => $price_history,
                    // Latest Added Fields
                    'wholesale' => $product->wholesale1,
                    'is_wholesale_only' => $product->is_wholesale_only,
                    'is_shipping' => $product->is_shipping,
                    'shipping_price' => $product->shipping_price,
                    'purchase_price' => $product->purchase_price,
                ];
            }

            $data = [
                'products' => $allProducs,
                'Count' => count($allProducs),
            ];

            if ($data)
                return ApiResponse::make('Data fetched2', $data);
        }


        $maxSellingProducts = OrderItem::select('order_items.product_id', DB::raw('sum(order_items.subtotal) as total_amount'))
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.order_type', 'sales');

        if ($warehouseId && $warehouseId != null) {
            $maxSellingProducts = $maxSellingProducts->where('orders.warehouse_id', $warehouseId);
        }

        $maxSellingProducts = $maxSellingProducts->groupBy('order_items.product_id')
            ->orderByRaw("sum(order_items.subtotal) desc")
            ->take(50)
            ->get();

        if ($maxSellingProducts) {
            $topSellingProductsNames = [];
            $topSellingProductsValues = [];
            $topSellingProductsColors = [];
            $counter = 0;
            foreach ($maxSellingProducts as $maxSellingProduct) {



                $products = Product::select(
                    'products.id',
                    'products.name',
                    'products.image',
                    'product_details.sales_price',
                    'products.category_id',
                    'products.unit_id',
                    'product_details.sales_tax_type',
                    'product_details.tax_id',
                    'product_details.current_stock',
                    'taxes.rate',
                    'products.item_code',
                    'products.is_default',
                    'product_details.is_shipping',
                    'product_details.shipping_price',
                    'product_details.purchase_price',
                    'product_details.is_wholesale_only'

                )->with('wholesale1')
                    ->join('product_details', 'product_details.product_id', '=', 'products.id')
                    ->leftJoin('taxes', 'taxes.id', '=', 'product_details.tax_id')
                    ->join('units', 'units.id', '=', 'products.unit_id')
                    ->where('product_details.warehouse_id', '=', $warehouseId);
                //  ->where('product_details.current_stock', '>', 0);


                if ($warehouse->products_visibility == 'warehouse') {
                    $products->where('products.warehouse_id', '=', $warehouse->id);
                }

                // Category Filters
                if ($request->has('category_id') && $request->category_id != "") {
                    $categoryId = $this->getIdFromHash($request->category_id);
                    $products = $products->where('category_id', '=', $categoryId);
                }

                // Brand Filters
                if ($request->has('brand_id') && $request->brand_id != "") {
                    $brandId = $this->getIdFromHash($request->brand_id);
                    $products = $products->where('brand_id', '=', $brandId);
                }
                $products1 =    $products;



                $products =    $products->where('products.id', '=', $maxSellingProduct->product_id)->orderBy('products.is_default', 'desc')->get();



                foreach ($products as $product) {

                    $stockQuantity = $product->current_stock;
                    $unit = $product->unit_id != null ? Unit::find($product->unit_id) : null;
                    $tax = $product->tax_id != null ? Tax::find($product->tax_id) : null;
                    $taxType = $product->sales_tax_type;

                    $unitPrice = $product->sales_price;
                    $singleUnitPrice = $unitPrice;

                    if ($product->rate != '') {
                        $taxRate = $product->rate;

                        if ($product->sales_tax_type == 'inclusive') {
                            $subTotal = $singleUnitPrice;
                            $singleUnitPrice =  ($singleUnitPrice * 100) / (100 + $taxRate);
                            $taxAmount = ($singleUnitPrice) * ($taxRate / 100);
                        } else {
                            $taxAmount =  ($singleUnitPrice * ($taxRate / 100));
                            $subTotal = $singleUnitPrice + $taxAmount;
                        }
                    } else {
                        $taxAmount = 0;
                        $taxRate = 0;
                        $subTotal = $singleUnitPrice;
                    }

                    if ($product->sales_price != $request->sales_price) {
                        if ($product->price_history != '') {
                            $oldPrice = json_decode($product->price_history);
                            array_push($oldPrice, ['sale_price' => $product->sales_price, 'purchase_price' => $product->purchase_price, 'mrp' => $product->mrp, 'date' => Carbon::now()->tz($timezone)]);
                            $price_history = $oldPrice;
                        } else {
                            $price_history = json_encode([['sale_price' => $product->sales_price, 'purchase_price' => $product->purchase_price, 'mrp' => $product->mrp, 'date' => Carbon::now()->tz($timezone)]]);
                        }
                    }

                    $allProducs[] = [
                        'item_id'    =>  '',
                        'xid'    =>  $product->xid,
                        'name'    =>  $product->name,
                        'image'    =>  $product->image,
                        'item_code' => $product->item_code,
                        'image_url'    =>  $product->image_url,
                        "x_category_id" => $product->category_id != null ? $this->getHashFromId($product->category_id) : null,
                        "x_brand_id" => $product->brand_id != null ? $this->getHashFromId($product->brand_id) : null,
                        'discount_rate'    =>  0,
                        'total_discount'    =>  0,
                        'x_tax_id'    => $tax ? $tax->xid : null,
                        'tax_type'    =>  $taxType,
                        'tax_rate'    =>  $taxRate,
                        'total_tax'    =>  $taxAmount,
                        'x_unit_id'    =>  $unit ? $unit->xid : null,
                        'unit'    =>  $unit,
                        'unit_price'    =>  $unitPrice,
                        'single_unit_price'    =>  $singleUnitPrice,
                        'subtotal'    =>  $subTotal,
                        'quantity'    =>  1,
                        'stock_quantity'    =>  $stockQuantity,
                        'unit_short_name'    =>  $unit ? $unit->short_name : '',
                        // Latest Added Fields
                        'price_history' => $price_history,
                        'wholesale' => $product->wholesale1,
                        'is_wholesale_only' => $product->is_wholesale_only,
                        'is_shipping' => $product->is_shipping,
                        'shipping_price' => $product->shipping_price,
                        'purchase_price' => $product->purchase_price,

                    ];
                }
            }
        } else {
            $products = Product::select(
                'products.id',
                'products.name',
                'products.item_code',
                'products.image',
                'product_details.sales_price',
                'products.unit_id',
                'product_details.sales_tax_type',
                'product_details.tax_id',
                'product_details.current_stock',
                'taxes.rate',
                'product_details.is_shipping',
                'product_details.shipping_price',
                'product_details.purchase_price',
                'product_details.is_wholesale_only'

            )->with('wholesale1')
                ->join('product_details', 'product_details.product_id', '=', 'products.id')
                ->leftJoin('taxes', 'taxes.id', '=', 'product_details.tax_id')
                ->join('units', 'units.id', '=', 'products.unit_id')
                ->where('product_details.warehouse_id', '=', $warehouseId)
                ->where('product_details.current_stock', '>', 0);

            if ($warehouse->products_visibility == 'warehouse') {
                $products->where('products.warehouse_id', '=', $warehouse->id);
            }

            // Category Filters
            if ($request->has('category_id') && $request->category_id != "") {
                $categoryId = $this->getIdFromHash($request->category_id);
                $products = $products->where('category_id', '=', $categoryId);
            }

            // Brand Filters
            if ($request->has('brand_id') && $request->brand_id != "") {
                $brandId = $this->getIdFromHash($request->brand_id);
                $products = $products->where('brand_id', '=', $brandId);
            }


            if ($request->has('limit') && $request->limit != "") {
                $products =    $products->take($request->limit)->get();
            } else {
                $products =   $products->get();
            }


            foreach ($products as $product) {

                $stockQuantity = $product->current_stock;
                $unit = $product->unit_id != null ? Unit::find($product->unit_id) : null;
                $tax = $product->tax_id != null ? Tax::find($product->tax_id) : null;
                $taxType = $product->sales_tax_type;

                $unitPrice = $product->sales_price;
                $singleUnitPrice = $unitPrice;

                if ($product->rate != '') {
                    $taxRate = $product->rate;

                    if ($product->sales_tax_type == 'inclusive') {
                        $subTotal = $singleUnitPrice;
                        $singleUnitPrice =  ($singleUnitPrice * 100) / (100 + $taxRate);
                        $taxAmount = ($singleUnitPrice) * ($taxRate / 100);
                    } else {
                        $taxAmount =  ($singleUnitPrice * ($taxRate / 100));
                        $subTotal = $singleUnitPrice + $taxAmount;
                    }
                } else {
                    $taxAmount = 0;
                    $taxRate = 0;
                    $subTotal = $singleUnitPrice;
                }

                if ($product->sales_price != $request->sales_price) {
                    if ($product->price_history != '') {
                        $oldPrice = json_decode($product->price_history);
                        array_push($oldPrice, ['sale_price' => $product->sales_price, 'purchase_price' => $product->purchase_price, 'mrp' => $product->mrp, 'date' => Carbon::now()->tz($timezone)]);
                        $price_history = $oldPrice;
                    } else {
                        $price_history = json_encode([['sale_price' => $product->sales_price, 'purchase_price' => $product->purchase_price, 'mrp' => $product->mrp, 'date' => Carbon::now()->tz($timezone)]]);
                    }
                }

                $allProducs[] = [
                    'item_id'    =>  '',
                    'xid'    =>  $product->xid,
                    'name'    =>  $product->name,
                    'image'    =>  $product->image,
                    'image_url'    =>  $product->image_url,
                    "x_category_id" => $product->category_id != null ? $this->getHashFromId($product->category_id) : null,
                    "x_brand_id" => $product->brand_id != null ? $this->getHashFromId($product->brand_id) : null,
                    'discount_rate'    =>  0,
                    'total_discount'    =>  0,
                    'x_tax_id'    => $tax ? $tax->xid : null,
                    'tax_type'    =>  $taxType,
                    'tax_rate'    =>  $taxRate,
                    'total_tax'    =>  $taxAmount,
                    'x_unit_id'    =>  $unit ? $unit->xid : null,
                    'unit'    =>  $unit,
                    'unit_price'    =>  $unitPrice,
                    'single_unit_price'    =>  $singleUnitPrice,
                    'subtotal'    =>  $subTotal,
                    'quantity'    =>  1,
                    'stock_quantity'    =>  $stockQuantity,
                    'unit_short_name'    =>  $unit ? $unit->short_name : '',
                    'item_code' => $product->item_code,
                    // Latest Added Fields
                    'price_history' => $price_history,
                    'wholesale' => $product->wholesale1,
                    'is_wholesale_only' => $product->is_wholesale_only,
                    'is_shipping' => $product->is_shipping,
                    'shipping_price' => $product->shipping_price,
                    'purchase_price' => $product->purchase_price,
                ];
            }
        }



        $data = [
            'products' => $allProducs,
            'Count' => count($allProducs),
        ];

        if ($data)

            return ApiResponse::make('Data fetched3', $data);
    }

    public function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function addPosPayment(PosRequest $request)
    {

        $warehouse = warehouse();
        $user_payment =  PaymentMode::where('id', $this->getIdFromHash($request->payment_mode_id))->first();
        $randNum = $this->generateRandomString();


        if ($warehouse->is_card_gateway && $warehouse->cardpayment_method == 'square' && ($user_payment->name == 'card' || $user_payment->name == 'Card')) {

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://connect.squareup.com/v2/terminals/checkouts/',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => '{
                   "checkout": {
                       "amount_money": {
                           "amount": ' . ($request->amount * 100) . ',
                           "currency": "AUD"
                       },
                       "device_options": {
                           "device_id": "' . ($request->square_device_id) . '"
                       }
                   },
                   "idempotency_key": "' . $randNum . '"
               }',
                CURLOPT_HTTPHEADER => array(
                    'Square-Version: 2023-10-18',
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $warehouse->square_access_key . ''
                ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            return ApiResponse::make($randNum, json_decode($response, true));
        } else if ($warehouse->upi_gateway  && (strtolower($user_payment->name) == 'upi gateway')) {
            $host = $_SERVER['HTTP_ORIGIN'];
            $curl = curl_init();
            $company_id = Hashids::encode($warehouse->company_id);
            $upi_key = Settings::where('other_data', $warehouse->id)->where('name_key', 'upi_gateway')->first()->credentials['key'];
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.ekqr.in/api/create_order',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => '{
                "key": "' . $upi_key . '",
                "client_txn_id": "' . $randNum . '",
                "amount": "' . $request->amount . '",
                "p_info": "Product Name",
                "customer_name": "' . $request->customer['name'] . '",
                "customer_email": "' . ($request->customer['email'] ? $request->customer['email'] : 'user@customer.com') . '",
                "customer_mobile": "' . (strlen($request->customer['phone']) ? substr($request->customer['phone'], -10) : '1111111111') . '",
                "redirect_url": "' . $host . '/save-upi-invoices",
                "udf1": "Jlq1nbR6",
                "udf2": "' . $company_id . '",
                "udf3": "order"
                }',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
            ));

            $response = curl_exec($curl);
            curl_close($curl);

            return ApiResponse::make("success", ['data' => json_decode($response, true), 'client_txn_id' => $randNum]);
        } else if ($warehouse->paytm_gateway  && (strtolower($user_payment->name) == 'paytm')) {
        } else {
            return ApiResponse::make('Success');
        }
    }

    public function getUpiPayment()
    {
        $request = request();
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.ekqr.in/api/check_order_status',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
        "key": "2fd5954a-1e7d-4d7e-a8e7-c7cba1b7b8f5",
        "client_txn_id":"' . $request->client_txn_id . '",
        "txn_date": "' . $request->txn_date . '"
        }',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return ApiResponse::make("success", json_decode($response, true));
    }

    public function savePosPayments()
    {
        $request = request();

        $loggedInUser = user();
        $warehouse = warehouse();
        $warehouseId = $warehouse->id;
        $warehouse = Warehouse::where('id', $warehouseId)->first();
        $company = company();
        $orderDetails = $request->details;
        $oldOrderId = "";
        $posDefaultStatus = $warehouse->default_pos_order_status;


        // Generate a unique key for this request based on its content
        $requestKey = md5(json_encode($orderDetails));

        // Check if this request key exists in the cache or session
        if (Cache::has($requestKey)) {
            return response()->json(["message" => 'Duplicate Request Detected'], 422);
        }

        // Cache the request key for a short duration (e.g., 2 seconds)
        Cache::put($requestKey, true, now()->addSeconds(10));

        $orderstatus = isset($orderDetails['order_status']) ? $orderDetails['order_status'] : $warehouse->default_pos_order_status;

        $timezone = $company->timezone;

        if ($warehouse->is_billing != 1) {
            return response()->json(["message" => 'Billing Is Temperately Blocked by Admin'], 422);
        }

        DB::beginTransaction();

        try {
            // Lock rows for writing during transaction to prevent concurrency issues
            Warehouse::where('id', $warehouse->id)->lockForUpdate()->first();

            // Your existing code to create and save the order
            // Check if there is an existing order with the same date & time, company_id, and total



            if ($orderDetails['user_id'] != 0) {
                $userId = $this->getIdFromHash($orderDetails['user_id']);
                $getUserName = User::where(['id' => $userId])->first();
            }

            $warehouse_latest = Warehouse::where(['id' => $warehouse->id])->orderBy('id', 'desc')->first();



            // if ($getUserName->is_walkin_customer!=1)
            // {
            //     $userDetails = UserDetails::withoutGlobalScope('current_warehouse')
            //     ->where('user_id', $userId)
            //     ->where('warehouse_id', $warehouse->id)
            //     ->first();
            //     if ($userDetails->credit_limit<$userDetails->due_amount)
            //     {

            //     }
            //     if ($userDetails->credit_limit<$orderDetails['subtotal'])
            //     {

            //     }

            // }
            // this code added by kali on 02-10-2023 for mobile offline order data store

            if ($orderDetails['invoice_number'] != '') {



                if ($orderDetails['mode'] == 1) {
                    $lastorder = Order::where('warehouse_id', $warehouse->id)
                        ->where('order_type', 'sales')
                        ->where('invoice_type', '!=', 'pos-off')
                        ->where(function ($query) {
                            $query->where('notes', '!=', 'Imported Data')
                                ->orWhere('notes', '=', null); // Exclude records with 'Imported Data' or where 'notes' is NULL
                        })
                        ->orWhere('order_type', 'online-orders')
                        ->orderBy('id', 'desc')
                        ->first();

                    if ($orderDetails['user_id'] == 0) {

                        $customer = $request->customer;

                        $getUser = User::where(["phone" => $customer['phone']])->count();


                        if ($getUser == 0) {
                            $user = new User();
                            $user['company_id'] = $company->id;
                            $user['warehouse_id'] = $warehouse->id;
                            $user['user_type'] = $customer["user_type"];
                            $user['name'] = $customer["name"];
                            $user['email'] = $customer["email"];
                            $user['phone'] = $customer["phone"];
                            $user['status'] = 'enabled';

                            $user->save();


                            $allWarehouses = Warehouse::select('id')->get();
                            foreach ($allWarehouses as $allWarehouse) {
                                $userDetails = new UserDetails();
                                $userDetails->warehouse_id = $allWarehouse->id;
                                $userDetails->user_id = $user->id;
                                $userDetails->credit_period = 30;
                                $userDetails->save();
                            }


                            $orderDetails['user_id'] = $user->id;
                        }
                    }
                }
                $invoice_prefix = $warehouse_latest->prefix_invoice;
                // this code added by kali on 17/10/2023 for offline POS invoice number with timestamp
                //  $invoice_suffix=$warehouse_latest->suffix_invoice.'-OFF';
                $invoice_suffix = $orderDetails['timestamp'];
                $invoice_spliter = $warehouse_latest->invoice_spliter;
                if ($lastorder != '') {
                    $lastorder = $lastorder->invoice_number;
                    $lastorder = explode($invoice_spliter, $lastorder);
                    $lastorder = $orderDetails['invoice_number'];
                } else {
                    $lastorder = 1;
                }

                $lastorder = substr("0000" . $lastorder, -5);

                $invoiceNum = $invoice_prefix . $invoice_spliter . $lastorder . $invoice_spliter . $invoice_suffix;


                $order = new Order();
                $order->order_type = "sales";
                $order->invoice_type = "pos-off";
                $order->unique_id = Common::generateOrderUniqueId();
                $order->invoice_number =  $invoiceNum;
                $order->order_date = Carbon::now();
                $order->order_date_local = Carbon::now()->tz($timezone);
                $order->warehouse_id = $warehouse->id;
                $order->user_id = isset($orderDetails['user_id']) ? $orderDetails['user_id'] : null;
                $order->tax_id = isset($orderDetails['tax_id']) ? $orderDetails['tax_id'] : null;
                $order->tax_rate = $orderDetails['tax_rate'];
                $order->tax_amount = $orderDetails['tax_amount'];
                $order->discount_type = $orderDetails['discount_type'];
                $order->discount = $orderDetails['discount'];
                $order->shipping = $orderDetails['shipping'];
                $order->subtotal = 0;
                $order->total = $orderDetails['subtotal'];
                $order->paid_amount = 0;
                $order->due_amount = $order->total;
                $order->order_status = $orderstatus;
                $order->staff_user_id = $loggedInUser->id;
                $order->save();


                Common::storeAndUpdateOrder($order, $oldOrderId);

                // Updating Warehouse History
                Common::updateWarehouseHistory('order', $order, "add_edit");

                $allPayments = $request->all_payments;
                foreach ($allPayments as $allPayment) {
                    $lastPayment = Payment::where('warehouse_id', $warehouse->id)->where('payment_type', 'in')->orderBy('id', 'DESC')->first();
                    // Save Order Payment
                    if ($allPayment['amount'] > 0 && $allPayment['payment_mode_id'] != '') {
                        $payment = new Payment();
                        $payment->warehouse_id = $warehouse->id;
                        $payment->payment_type = "in";
                        $payment->date = Carbon::now();
                        $payment->amount = $allPayment['amount'];
                        $payment->paid_amount = $allPayment['amount'];
                        $payment->payment_mode_id = $allPayment['payment_mode_id'];
                        $payment->notes = $allPayment['notes'];
                        $payment->user_id = $order->user_id;
                        $payment->staff_user_id = $loggedInUser->id;
                        $payment->save();

                        // Generate and save payment number
                        $paymentType = 'payment-' . $payment->payment_type;
                        $payment->payment_number = Common::getTransactionNumberNew($paymentType, $lastPayment->invoice_id + 1, "", $warehouse->suffix_invoice, $warehouse->invoice_spliter);
                        $payment->invoice_id = $lastPayment->invoice_id + 1;
                        $payment->save();

                        $orderPayment = new OrderPayment();
                        $orderPayment->order_id = $order->id;
                        $orderPayment->payment_id = $payment->id;
                        $orderPayment->amount = $allPayment['amount'];
                        $orderPayment->save();
                    }
                }

                Common::updateOrderAmount($order->id);

                $savedOrder = Order::select('id', 'unique_id', 'invoice_number', 'user_id', 'staff_user_id', 'order_date', 'discount', 'shipping', 'tax_amount', 'subtotal', 'total', 'paid_amount', 'due_amount', 'total_items', 'total_quantity')
                    ->with(['user:id,name,tax_number', 'items:id,order_id,product_id,unit_id,unit_price,subtotal,quantity,mrp,total_tax,tax_rate', 'items.product:id,name', 'items.unit:id,name,short_name', 'orderPayments:id,order_id,payment_id,amount', 'orderPayments.payment:id,payment_mode_id', 'orderPayments.payment.paymentMode:id,name', 'staffMember:id,name'])
                    ->find($order->id);

                $totalMrp = 0;
                $totalTax = 0;
                foreach ($savedOrder->items as $orderItem) {
                    $totalMrp += ($orderItem->quantity * $orderItem->mrp);
                    $totalTax += $orderItem->total_tax;
                }

                $savingOnMrp = $totalMrp - $savedOrder->total;
                if ($savingOnMrp > 0) {
                    $saving_percentage = number_format((float)($savingOnMrp / $totalMrp * 100), 2, '.', '');
                } else {
                    $saving_percentage = 0;
                }

                DB::table('warehouses')
                    ->where('id', $warehouse->id)
                    ->update(['invoice_started' => 1]);

                $savedOrder->saving_on_mrp = $savingOnMrp;
                $savedOrder->saving_percentage = $saving_percentage;
                $savedOrder->total_tax_on_items = $totalTax + $savedOrder->tax_amount;



                return ApiResponse::make('Data fetched', [
                    'order' => $savedOrder, 'order_id' => $order->x_id, 'order_number' => $lastorder, 'invoice_prefix' => $invoice_prefix, 'invoice_suffix' => $invoice_suffix, 'invoice_spliter' => $invoice_spliter, 'fullno' => $lastorder, 'next_order_number' => $lastorder + 1,
                ]);
            } else {
                $invoice_spliter = $warehouse_latest->invoice_spliter;
                if ($warehouse_latest->daily_reset == 1) {
                    $invoice_prefix = $warehouse_latest->prefix_invoice;
                    $invoice_suffix = Carbon::now()->format('d') . Carbon::now()->format('m') . Carbon::now()->format('y');
                    $invoice_spliter = $warehouse_latest->invoice_spliter;

                    $todaylocaldate = Carbon::now()->tz($timezone)->toDateString();

                    $lastordercount = Order::where(['warehouse_id' => $warehouse->id])->whereDate('order_date_local', '=', $todaylocaldate)->count();

                    if ($lastordercount == 0) {
                        $lastorder = $warehouse_latest->first_invoice_no;
                    } else {
                        $lastorder = Order::where('warehouse_id', $warehouse->id)
                            ->where('order_type', 'sales')
                            ->where('invoice_type', '!=', 'pos-off')
                            ->where(function ($query) {
                                $query->where('notes', '!=', 'Imported Data')
                                    ->orWhere('notes', '=', null); // Exclude records with 'Imported Data' or where 'notes' is NULL
                            })
                            ->orWhere('order_type', 'online-orders')
                            ->orderBy('id', 'desc')
                            ->first();
                        if ($warehouse_latest->invoice_started == 1) {

                            if (!$lastorder) {
                                $lastorder = $lastorder->invoice_number;
                                $lastorder = explode($invoice_spliter, $lastorder);
                                $lastorder = $lastorder[1] + 1;
                            } else {
                                $lastorder = $warehouse_latest->first_invoice_no + 1;
                            }
                        } else {
                            $lastorder = $warehouse_latest->first_invoice_no + 1;
                        }
                    }
                } else {
                    $invoice_prefix = $warehouse_latest->prefix_invoice;
                    $invoice_suffix = $warehouse_latest->suffix_invoice;
                    $invoice_spliter = $warehouse_latest->invoice_spliter;
                    $lastorder = Order::where('warehouse_id', $warehouse->id)
                        ->where('order_type', 'sales')
                        ->where('invoice_type', '!=', 'pos-off')
                        ->where(function ($query) {
                            $query->where('notes', '!=', 'Imported Data')
                                ->orWhere('notes', '=', null); // Exclude records with 'Imported Data' or where 'notes' is NULL
                        })
                        ->orWhere('order_type', 'online-orders')
                        ->orderBy('id', 'desc')
                        ->first();


                    if ($warehouse_latest->invoice_started == 1) {
                        if ($lastorder != '') {
                            $lastorder = $lastorder->invoice_number;
                            $lastorder = explode($invoice_spliter, $lastorder);
                            $lastorder = $lastorder[1] + 1;
                        } else {
                            $lastorder = $warehouse_latest->first_invoice_no + 1;
                        }
                    } else {
                        $lastorder = $warehouse_latest->first_invoice_no + 1;
                    }
                }

                $invoice_prefix = $warehouse->prefix_invoice;

                $order = new Order();
                $order->order_type = "sales";
                $order->invoice_type = "pos";
                $order->unique_id = Common::generateOrderUniqueId();
                $order->invoice_number = "";
                $order->order_date = Carbon::now();
                $order->order_date_local = Carbon::now()->tz($timezone);
                $order->warehouse_id = $warehouse->id;
                $order->user_id = isset($orderDetails['user_id']) ? $orderDetails['user_id'] : null;
                $order->tax_id = isset($orderDetails['tax_id']) ? $orderDetails['tax_id'] : null;
                $order->tax_rate = $orderDetails['tax_rate'];
                $order->tax_amount = $orderDetails['tax_amount'];
                if (isset($orderDetails['discount_type'])) {
                    $order->discount_type = $orderDetails['discount_type'];
                }
                if (isset($orderDetails['discount'])) {
                    $order->discount = $orderDetails['discount'];
                    $orderDetails['subtotal'] = $orderDetails['subtotal'] + $orderDetails['discount'];
                }


                $order->shipping = $orderDetails['shipping'];
                $order->subtotal = 0;
                $order->total = $orderDetails['subtotal'];
                $order->paid_amount = 0;
                $order->due_amount = $order->total;
                $order->order_status = $orderstatus;
                $order->staff_user_id = $loggedInUser->id;
                $order->save();


                $currentinvoice = $lastorder;

                if ($warehouse_latest->invoice_started == 0) {
                    $currentinvoice = $warehouse_latest->first_invoice_no + 1;
                    $formattedNumber = substr("0000" . $currentinvoice, -5);
                    $order->invoice_number = Common::getTransactionNumberNew($order->order_type, $formattedNumber, $invoice_prefix, $invoice_suffix, $invoice_spliter);
                } else {
                    $formattedNumber = substr("0000" . $currentinvoice, -5);
                    $order->invoice_number = Common::getTransactionNumberNew($order->order_type, $formattedNumber, $invoice_prefix, $invoice_suffix, $invoice_spliter);
                }

                // $order->invoice_number = Common::getTransactionNumberNew($order->order_type, $order->id,$invoice_prefix);
                $order->save();



                Common::storeAndUpdateOrder($order, $oldOrderId);

                // Updating Warehouse History
                Common::updateWarehouseHistory('order', $order, "add_edit");

                $allPayments = $request->all_payments;
                foreach ($allPayments as $allPayment) {
                    $lastPayment = Payment::where('warehouse_id', $warehouse->id)->where('payment_type', 'in')->orderBy('id', 'DESC')->first();
                    // Save Order Payment
                    if ($allPayment['amount'] > 0 && $allPayment['payment_mode_id'] != '') {
                        $payment = new Payment();
                        $payment->warehouse_id = $warehouse->id;
                        $payment->payment_type = "in";
                        $payment->date = Carbon::now();
                        $payment->amount = $allPayment['amount'];
                        $payment->paid_amount = $allPayment['amount'];
                        $payment->payment_mode_id = $allPayment['payment_mode_id'];
                        $payment->notes = $allPayment['notes'];
                        $payment->user_id = $order->user_id;
                        $payment->staff_user_id = $loggedInUser->id;
                        $payment->save();

                        // Generate and save payment number
                        $paymentType = 'payment-' . $payment->payment_type;
                        $payment->payment_number = Common::getTransactionNumberNew($paymentType, $lastPayment->invoice_id + 1, "", $warehouse->suffix_invoice, $warehouse->invoice_spliter);
                        $payment->invoice_id = $lastPayment->invoice_id + 1;
                        $payment->save();

                        $orderPayment = new OrderPayment();
                        $orderPayment->order_id = $order->id;
                        $orderPayment->payment_id = $payment->id;
                        $orderPayment->amount = $allPayment['amount'];
                        $orderPayment->save();
                    }
                }
                Common::updateOrderAmount($order->id);

                $savedOrder = Order::select('id', 'unique_id', 'invoice_number', 'user_id', 'staff_user_id', 'order_date', 'discount', 'shipping', 'tax_amount', 'subtotal', 'total', 'paid_amount', 'due_amount', 'total_items', 'total_quantity')
                    ->with(['user:id,name,phone,tax_number', 'items:id,order_id,product_id,unit_id,unit_price,subtotal,quantity,mrp,total_tax,tax_rate', 'items.product:id,name', 'items.unit:id,name,short_name', 'orderPayments:id,order_id,payment_id,amount', 'orderPayments.payment:id,payment_mode_id', 'orderPayments.payment.paymentMode:id,name', 'staffMember:id,name'])
                    ->find($order->id);



                $totalMrp = 0;
                $totalTax = 0;
                foreach ($savedOrder->items as $orderItem) {
                    $totalMrp += ($orderItem->quantity * $orderItem->mrp);
                    $totalTax += $orderItem->total_tax;
                }



                $savingOnMrp = $totalMrp - $savedOrder->total;
                if ($savingOnMrp > 0) {
                    $saving_percentage = number_format((float)($savingOnMrp / $totalMrp * 100), 2, '.', '');
                } else {
                    $saving_percentage = 0;
                }

                DB::table('warehouses')
                    ->where('id', $warehouse->id)
                    ->update(['invoice_started' => 1]);

                $savedOrder->saving_on_mrp = $savingOnMrp;
                $savedOrder->saving_percentage = $saving_percentage;
                $savedOrder->total_tax_on_items = $totalTax + $savedOrder->tax_amount;

                $getUser = User::where(['id' => $order->user_id])->first();
                $message_client = "";
                //points added by pathiban
                $getUser->deposit(10);
                $savedOrder->points_balance =  $getUser->balance;
                //






                if ($warehouse->is_wainvoice != 0) {

                    if ($warehouse->id == 62) {
                        $message_client = "
                Dear " . $getUser->name . ",

                Welcome to " . $warehouse->name . "! We are delighted to have you visit our store, and we sincerely appreciate the opportunity to serve your hardware needs.

                Our shelves are stocked with a comprehensive selection of products, including:
                - Hand Tools
                - Power Tools
                - Building Materials
                - Plumbing Supplies
                - Electrical Components
                - Paints and Finishes
                - Garden and Outdoor Equipment
                - Safety Gear

                *Your Order Details:*
                - Order Number: " . $order->invoice_number . "
                - Order Date: " . $order->order_date . "
                - Total Amount: " . $order->total . "

                https://jnanaerp.com/api/v1/viewpdf/" . $order->unique_id . "/en

                Sincerely,

                " . $warehouse->name . "
                " . $warehouse->address . "
                " . $warehouse->phone . "";
                    } else {
                        $message_client = "
                    Dear " . $getUser->name . ",

                    We want to express our sincere gratitude for choosing" . $warehouse->name . " for your recent purchase. Your trust in us means a lot, and we're excited that you've decided to be a part of our valued customer community.

                    **Your Order Details:**
                    - Order Number: " . $order->invoice_number . "
                    - Order Date: " . $order->order_date . "
                    - Total Amount: " . $order->total . "

                    To help you keep track of your purchase, we've attached an invoice for your reference. You can access and download the invoice by clicking on the link below:

                    https://jnanaerp.com/api/v1/viewpdf/" . $order->unique_id . "/en

                    Please review the invoice to ensure all details are accurate. If you have any questions or concerns regarding your purchase or the invoice, feel free to reach out to our customer support team at [Customer Support Email/Phone Number]. We're here to assist you in any way we can.

                    We're committed to providing you with top-notch products and exceptional service. Your satisfaction is our utmost priority. We hope this purchase exceeds your expectations, and we look forward to serving you again in the future.

                    Thank you once again for choosing " . $warehouse->name . ". We truly appreciate your business.";
                    }

                    // $whatsapp = $this->sendWhatsApp_new($mobile, $message_client);
                    if ($orderDetails['user_id'] != '') {

                        //   $user_id=Common::getHashFromId($orderDetails['user_id']);


                        Common::sendWhatsApp_new($getUser->phone, $message_client, $loggedInUser->country_code);
                    }
                };



                // Commit the transaction if everything succeeds
                DB::commit();

                return ApiResponse::make('Data fetched', [
                    'order' => $savedOrder, 'order_id' => $order->x_id, 'order_number' => $lastorder, 'invoice_prefix' => $invoice_prefix, 'invoice_suffix' => $invoice_suffix, 'invoice_spliter' => $invoice_spliter, 'fullno' => $lastorder, 'next_order_number' => $lastorder + 1, 'wamessage' => $message_client
                ]);
            }
        } catch (\Exception $e) {
            // Rollback the transaction on any exception
            DB::rollback();

            // Log the exception
            Log::error('Error creating order: ' . $e->getMessage());

            // Return an error response
            return response()->json(['message' => 'Error creating order'], 500);
        }
    }


    public function getSquarePayments()
    {

        $request = request();
        $warehouse = warehouse();

        // do {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://connect.squareup.com/v2/terminals/checkouts/' . $request->id,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Square-Version: 2023-10-18',
                'Authorization: Bearer ' . $warehouse->square_access_key . '',
                'Cookie: __cf_bm=DeeiZblKLIOKNN7S0FmyqMjMu1fWwo3sofLfVWhhxks-1699602342-0-AVSSahP0Et7wQhn1MrmtutD9MeB4C4QuzOZxSa1QaS158fo3L2HDXFZp9GfV5jFgcHrmGIWeK1MyhBbl9hwVCrY='
            ),
        ));

        $response = curl_exec($curl);

        $response = json_decode($response, true);

        curl_close($curl);

        //  }while ($response['checkout']['status'] == 'CANCELED');


        return $response;
    }

    public function getListDevices()
    {

        $warehouse = warehouse();


        $client = new SquareClient([
            'accessToken' => $warehouse->square_access_key,
            'environment' => Environment::PRODUCTION,
        ]);
        $api_response = $client->getDevicesApi()->listDeviceCodes();

        if ($api_response->isSuccess()) {
            $result = $api_response->getResult();
            return $result;
        } else {
            $errors = $api_response->getErrors();
            return $errors;
        }
    }


    public function cancelSquarePayments()
    {

        $request = request();
        $warehouse = warehouse();

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://connect.squareup.com/v2/terminals/checkouts/' . $request->id . '/cancel',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_HTTPHEADER => array(
                'Square-Version: 2023-10-18',
                'Authorization: Bearer ' . $warehouse->square_access_key . '',
                'Cookie: __cf_bm=AunW.o.KKj3VmvRJtbCUbcCFoUeHKl9iATblqfA6C2U-1699609952-0-AQ5J6uswfgsOR8vKpumI9sw9J103vzbdd+z4E3pK1ArdcPMIL3MLHEoGRKdATTynH/ZO/n6iYnwHTymbCSWLBVk='
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $response = json_decode($response, true);
        return $response;
    }
}
