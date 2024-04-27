<?php

namespace App\Http\Controllers\Api;

use App\Classes\Common;
use App\Exports\ProductExport;
use App\Http\Controllers\ApiBaseController;
use App\Http\Requests\Api\Product\IndexRequest;
use App\Http\Requests\Api\Product\StoreRequest;
use App\Http\Requests\Api\Product\UpdateRequest;
use App\Http\Requests\Api\Product\DeleteRequest;
use App\Http\Requests\Api\Product\ImportRequest;
use App\Imports\ProductImport;
use App\Imports\ProductUpdateImport;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductDetails;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Examyou\RestAPI\ApiResponse;
use Illuminate\Support\Facades\Log;
use Vinkla\Hashids\Facades\Hashids;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Models\OrderItem;
use App\Models\Wholesale;
use Illuminate\Support\Facades\DB;

class ProductController extends ApiBaseController
{
    protected $model = Product::class;

    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function modifyIndex($query)
    {
        $request = request();
        $warehouse = warehouse();

        if ($warehouse->products_visibility == 'warehouse') {
            $query->where('products.warehouse_id', '=', $warehouse->id);
        }

        $query = $query->join('product_details', 'product_details.product_id', '=', 'products.id')
            ->where('product_details.warehouse_id', $warehouse->id);

        if ($request->has('fetch_stock_alert') && $request->fetch_stock_alert) {
            $query = $query->whereNotNull('stock_quantitiy_alert')
                ->whereRaw('product_details.current_stock <= product_details.stock_quantitiy_alert');
        };

        if ($request->has('x_id') && $request->x_id != '') {
            $query = $query->where('products.id', $this->getIdFromHash($request->x_id));
        };

        $query = $query->with('items');

        return $query;
    }

    public function storing(Product $product)
    {
        $request = request();
        $loggedUser = user();
        $warehouse = warehouse();
        $company = company();

        $product->user_id = $loggedUser->id;
        $product->warehouse_id = $loggedUser->hasRole('admin') && $request->warehouse_id != '' ? $request->warehouse_id : $warehouse->id;
        // Log::info("out");
        if ($request->category_id == 0) {
            //     Log::info("IN");
            $category = Category::where('company_id', $company->id)->first();
            if ($category) {
                $product->category_id = $category->id;
            }
        }
        if ($request->unit_id == 0) {
            $unit = Unit::where('company_id', $company->id)->first();
            if ($unit) {
                $product->unit_id = $unit->id;
            }
        }


        return $product;
    }
    public function stored(Product $product)
    {
        $request = request();
        $allWarehouses = Warehouse::select('id')->get();

        foreach ($allWarehouses as $allWarehouse) {
            $productDetails = new ProductDetails();
            $productDetails->warehouse_id = $allWarehouse->id;
            $productDetails->product_id = $product->id;
            $productDetails->expiry = $request->expiry;
            $productDetails->tax_id = $request->has('tax_id') && $request->tax_id != '' ? $request->tax_id : null;
            $productDetails->mrp = $request->mrp;
            $productDetails->purchase_price = $request->purchase_price;
            $productDetails->sales_price = $request->sales_price;
            $productDetails->purchase_tax_type = $request->purchase_tax_type;
            $productDetails->sales_tax_type = $request->sales_tax_type;
            $productDetails->stock_quantitiy_alert = $request->stock_quantitiy_alert;
            $productDetails->wholesale_price = $request->wholesale_price;
            $productDetails->is_wholesale_only = $request->is_wholesale_only;
            $productDetails->wholesale_quantity = $request->wholesale_quantity;
            $productDetails->is_shipping = $request->is_shipping;
            $productDetails->shipping_price = $request->shipping_price;
            $productDetails->save();
            if ($request->wholesale) {
                if (count($request->wholesale)) {

                    foreach ($request->wholesale as $key => $value) {
                        Wholesale::create([
                            'product_id' => $product->id,
                            'warehouse_id' => $allWarehouse->id,
                            'product_details_id' => $productDetails->id,
                            'start_quantity' => $value['start_quantity'],
                            'end_quantity' => $value['end_quantity'],
                            'wholesale_price' => $value['wholesale_price'],
                        ]);
                    }
                }
            }
            Common::updateProductCustomFields($product, $allWarehouse->id);
        }

        // Saving Opening Stock and date for current product
        // Becuase these values will be different for each product
        // At warehouse
        $currentProductDetails = $product->details;
        $currentProductDetails->opening_stock = $request->opening_stock;
        $currentProductDetails->opening_stock_date = $request->opening_stock_date;
        $currentProductDetails->save();

        Common::recalculateOrderStock($currentProductDetails->warehouse_id, $product->id);
    }

    public function updating($product)
    {
        $request = request();
        $loggedUser = user();

        if ($loggedUser->hasRole('admin') && $request->warehouse_id != '') {
            $product->warehouse_id = $request->warehouse_id;
        }

        return $product;
    }

    public function updated(Product $product)
    {
        $request = request();
        $company = company();
        $timezone = $company->timezone;

        $currentProductDetails = $product->details;
        if ($currentProductDetails->sales_price != $request->sales_price) {
            if ($currentProductDetails->price_history != '') {
                $oldPrice = json_decode($currentProductDetails->price_history);
                array_push($oldPrice, ['sale_price' => $currentProductDetails->sales_price, 'purchase_price' => $currentProductDetails->purchase_price, 'mrp' => $currentProductDetails->mrp, 'date' => Carbon::now()->tz($timezone)]);
                $currentProductDetails->price_history = $oldPrice;
            } else {
                $currentProductDetails->price_history = json_encode([['sale_price' => $currentProductDetails->sales_price, 'purchase_price' => $currentProductDetails->purchase_price, 'mrp' => $currentProductDetails->mrp, 'date' => Carbon::now()->tz($timezone)]]);
            }
        }
        $currentProductDetails->tax_id = $request->has('tax_id') && $request->tax_id != '' ? $request->tax_id : null;
        $currentProductDetails->mrp = $request->mrp;
        $currentProductDetails->purchase_price = $request->purchase_price;
        $currentProductDetails->sales_price = $request->sales_price;
        $currentProductDetails->purchase_tax_type = $request->purchase_tax_type;
        $currentProductDetails->sales_tax_type = $request->sales_tax_type;
        $currentProductDetails->stock_quantitiy_alert = $request->stock_quantitiy_alert;
        $currentProductDetails->wholesale_price = $request->wholesale_price;
        $currentProductDetails->is_wholesale_only = $request->is_wholesale_only;
        $currentProductDetails->wholesale_quantity = $request->wholesale_quantity;
        $currentProductDetails->is_shipping = $request->is_shipping;
        $currentProductDetails->shipping_price = $request->shipping_price;
        $currentProductDetails->opening_stock = $request->opening_stock;
        $currentProductDetails->expiry = $request->expiry;
        $currentProductDetails->opening_stock_date = $request->opening_stock_date;
        $currentProductDetails->save();
        if (count($request->wholesale)) {
            foreach ($request->wholesale as $key => $value) {
                if ($value['id'] != "") {
                    DB::table('wholesales')
                        ->where('id', $value['id'])
                        ->update([
                            'start_quantity' => $value['start_quantity'],
                            'end_quantity' => $value['end_quantity'],
                            'wholesale_price' => $value['wholesale_price'],
                        ]);
                } else {
                    Wholesale::create([
                        'product_id' => $product->id,
                        'warehouse_id' =>  $product->warehouse_id,
                        'product_details_id' => $currentProductDetails->id,
                        'start_quantity' => $value['start_quantity'],
                        'end_quantity' => $value['end_quantity'],
                        'wholesale_price' => $value['wholesale_price'],
                    ]);
                }
            }
        } else {
            DB::table('wholesales')
                ->where('product_id', $product->id)
                ->where('warehouse_id', $product->warehouse_id)
                ->delete();
        }
        if ($request->deleted_wholesale) {
            if (count($request->deleted_wholesale)) {
                foreach ($request->deleted_wholesale as $key => $value) {

                    DB::table('wholesales')
                        ->where('id', $value)
                        ->delete();
                }
            }
        }

        Common::updateProductCustomFields($product, $currentProductDetails->warehouse_id);
        Common::recalculateOrderStock($currentProductDetails->warehouse_id, $product->id);
    }

    public function searchProduct(Request $request)
    {
        $warehouse = warehouse();
        $searchTerm = $request->search_term;
        $orderType = $request->order_type;
        $userid = $request->userid;
        $warehouseId = $warehouse->id;
        $warehouse = Warehouse::where('id', $warehouseId)->first();

        if ($warehouse->is_billing != 1 && $orderType != 'purchases') {
            return response()->json(["message" => 'Billing Is Temperately Blocked by Admin'], 422);
        }

        $products = Product::select('products.id', 'products.name', 'products.image', 'products.unit_id', 'products.item_code')->with(['customFields:field_name,field_value'])->with('wholesale1')
            ->where(function ($query) use ($searchTerm) {
                $query->where('products.name', 'LIKE', "%$searchTerm%")
                    ->orWhere('products.item_code', trim($searchTerm))->orWhere('products.product_code', 'LIKE', "%$searchTerm%");
            });


        if ($warehouse->products_visibility == 'warehouse') {
            $products->where('products.warehouse_id', '=', $warehouse->id);
        }

        // if ($request->has('products')) {
        //     $selectedProducts = $request->products;
        //     $convertedSelectedProducts = [];
        //     if (count($selectedProducts) > 0) {
        //         foreach ($selectedProducts as $selectedProduct) {
        //             $convertedSelectedProducts[] = $this->getIdFromHash($selectedProduct);
        //         }
        //     }
        //     $products = $products->whereNotIn('products.id', $convertedSelectedProducts);
        // }

        $products = $products->orderByRaw("CASE WHEN name = '$searchTerm' THEN 0 ELSE 1 END")->take(100)->get();

        // // Log::info("message" . $products);
        $allProducs = [];

        if ($warehouseId == '') {
            return $allProducs;
        } else {
            $warehouseHashId = Hashids::decode($warehouseId);
            if (count($warehouseHashId) > 0) {
                $warehouseId = $warehouseHashId[0];
            }
        }

        foreach ($products as $product) {
            $productDetails = $product->details;
            $tax = Tax::find($productDetails->tax_id);
            $prodId = $this->getIdFromHash($product->x_id);
            $lastsellprice = 0;
            if ($userid != '') {
                $getlastRate = OrderItem::where('user_id', $this->getIdFromHash($userid))->where('product_id', $prodId)->orderBy('id', 'desc')->first();
                // // Log::info("order details" . $getlastRate->unit_price );
                if (isset($getlastRate))
                    $lastsellprice = $getlastRate->unit_price;
            }

            // if ($orderType == 'purchases' || $orderType == 'quotations' || ($orderType == 'sales' && $productDetails->current_stock > 0) || ($orderType == 'sales-returns') || ($orderType == 'purchase-returns' && $productDetails->current_stock > 0) || ($orderType == 'stock-transfers' && $productDetails->current_stock > 0))
            if ($orderType == 'purchases' || $orderType == 'quotations' || ($orderType == 'sales') || ($orderType == 'sales-returns') || ($orderType == 'purchase-returns') || ($orderType == 'stock-transfers')) {
                $stockQuantity = $productDetails->current_stock;
                $unit = $product->unit_id != null ? Unit::find($product->unit_id) : null;

                if ($orderType == 'purchases' || $orderType == 'purchase-returns' || $orderType == 'stock-transfers') {
                    $unitPrice = $productDetails->purchase_price;
                    $taxType = $productDetails->purchase_tax_type;
                } else if ($orderType == 'sales' || $orderType == 'sales-returns' || $orderType == 'quotations') {
                    $unitPrice = $productDetails->sales_price;
                    $taxType = $productDetails->sales_tax_type;
                }

                $singleUnitPrice = $unitPrice;

                if ($tax && $tax->rate != '') {
                    $taxRate = $tax->rate;

                    if ($taxType == 'inclusive') {
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


                $allProducs[] = [
                    'item_id'    =>  '',
                    'xid'    =>  $product->xid,
                    'item_code' => $product->item_code,
                    'name'    =>  $product->name,
                    'image'    =>  $product->image,
                    'image_url'    =>  $product->image_url,
                    'discount_rate'    =>  0,
                    'total_discount'    =>  0,
                    'x_tax_id'    =>  $tax ? $tax->xid : null,
                    'tax_type'    =>  $taxType,
                    'tax_rate'    =>  $taxRate,
                    'total_tax'    =>  $taxAmount,
                    'x_unit_id'    =>  Hashids::encode($product->unit_id),
                    'unit'    =>  $unit,
                    'unit_price'    =>  $unitPrice,
                    'single_unit_price'    =>  $singleUnitPrice,
                    'subtotal'    =>  $subTotal,
                    'quantity'    =>  1,
                    'stock_quantity'    =>  $stockQuantity,
                    'unit_short_name'    =>  $unit ? $unit->short_name : '',
                    'custom_fields' => $product->customfields,
                    // Latest Added Fields
                    'wholesale_price' => $productDetails->wholesale_price,
                    'is_wholesale_only' => $productDetails->is_wholesale_only,
                    'wholesale_quantity' => $productDetails->wholesale_quantity,
                    'price_history' => $productDetails->price_history,
                    'Last_selling' => $lastsellprice,
                    'wholesale' => $product->wholesale1,
                    'is_shipping' => $productDetails->is_shipping,
                    'shipping_price' => $productDetails->shipping_price,
                    'purchase_price' => $productDetails->purchase_price,
                    "sales_price" => $productDetails->sales_price,
                    'mrp' => $productDetails->mrp
                ];
            }
            // All Type products
            if (!$request->has('order_type')) {
                $allProducs[] = [
                    'xid'    =>  $product->xid,
                    'name'    =>  $product->name,
                    'image'    =>  $product->image,
                    'image_url'    =>  $product->image_url,
                    'stock_quantity'    =>  $productDetails->current_stock,
                ];
            }
        }


        return ApiResponse::make('Fetched Successfully', $allProducs);
    }


    public function Product(Request $request)
    {
        $warehouse = warehouse();
        $orderType = $request->order_type;
        $warehouseId = $warehouse->id;

        $products = Product::select('products.id', 'products.name', 'products.image', 'products.unit_id');

        if ($warehouse->products_visibility == 'warehouse') {
            $products->where('products.warehouse_id', '=', $warehouse->id);
        }

        if ($request->has('products')) {
            $selectedProducts = $request->products;
            $convertedSelectedProducts = [];
            if (count($selectedProducts) > 0) {
                foreach ($selectedProducts as $selectedProduct) {
                    $convertedSelectedProducts[] = $this->getIdFromHash($selectedProduct);
                }
            }
            $products = $products->whereNotIn('products.id', $convertedSelectedProducts);
        }

        $products = $products->get();

        $allProducs = [];

        if ($warehouseId == '') {
            return $allProducs;
        } else {
            $warehouseHashId = Hashids::decode($warehouseId);
            if (count($warehouseHashId) > 0) {
                $warehouseId = $warehouseHashId[0];
            }
        }

        foreach ($products as $product) {
            $productDetails = $product->details;
            $tax = Tax::find($productDetails->tax_id);



            // if ($orderType == 'purchases' || $orderType == 'quotations' || ($orderType == 'sales' && $productDetails->current_stock > 0) || ($orderType == 'sales-returns') || ($orderType == 'purchase-returns' && $productDetails->current_stock > 0) || ($orderType == 'stock-transfers' && $productDetails->current_stock > 0))
            if ($orderType == 'purchases' || $orderType == 'quotations' || ($orderType == 'sales') || ($orderType == 'sales-returns') || ($orderType == 'purchase-returns') || ($orderType == 'stock-transfers')) {
                $stockQuantity = $productDetails->current_stock;
                $unit = $product->unit_id != null ? Unit::find($product->unit_id) : null;

                if ($orderType == 'purchases' || $orderType == 'purchase-returns' || $orderType == 'stock-transfers') {
                    $unitPrice = $productDetails->purchase_price;
                    $taxType = $productDetails->purchase_tax_type;
                } else if ($orderType == 'sales' || $orderType == 'sales-returns' || $orderType == 'quotations') {
                    $unitPrice = $productDetails->sales_price;
                    $taxType = $productDetails->sales_tax_type;
                }

                $singleUnitPrice = $unitPrice;

                if ($tax && $tax->rate != '') {
                    $taxRate = $tax->rate;

                    if ($taxType == 'inclusive') {
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

                $allProducs[] = [
                    'item_id'    =>  '',
                    'xid'    =>  $product->xid,
                    'name'    =>  $product->name,
                    'image'    =>  $product->image,
                    'image_url'    =>  $product->image_url,
                    'discount_rate'    =>  0,
                    'total_discount'    =>  0,
                    'x_tax_id'    =>  $tax ? $tax->xid : null,
                    'tax_type'    =>  $taxType,
                    'tax_rate'    =>  $taxRate,
                    'total_tax'    =>  $taxAmount,
                    'x_unit_id'    =>  Hashids::encode($product->unit_id),
                    'unit'    =>  $unit,
                    'unit_price'    =>  $unitPrice,
                    'single_unit_price'    =>  $singleUnitPrice,
                    'subtotal'    =>  $subTotal,
                    'quantity'    =>  1,
                    'stock_quantity'    =>  $stockQuantity,
                    'unit_short_name'    =>  $unit ? $unit->short_name : '',
                    'purchase_price' => $productDetails->purchase_price,
                    "sales_price" => $productDetails->sales_price,
                    'mrp' => $productDetails->mrp
                ];
            }

            // All Type products
            if (!$request->has('order_type')) {
                $allProducs[] = [
                    'xid'    =>  $product->xid,
                    'name'    =>  $product->name,
                    'image'    =>  $product->image,
                    'image_url'    =>  $product->image_url,
                    'stock_quantity'    =>  $productDetails->current_stock,
                ];
            }
        }

        return ApiResponse::make('Fetched Successfully', $allProducs);
    }

    public function searchBarcodeProduct(Request $request)
    {
        $warehouse = warehouse();
        $searchTerm = $request->search_term;
        $orderType = $request->order_type;
        $warehouseId = $warehouse->id;
        $userid = $request->userid;

        $warehouse = Warehouse::where('id', $warehouseId)->first();

        if ($warehouse->is_billing != 1 && $orderType != 'purchases') {
            return response()->json(["message" => 'Billing Is Temperately Blocked by Admin'], 422);
        }

        $product = Product::select('products.id', 'products.name', 'products.image', 'products.unit_id')->with('wholesale1')
            ->where('products.item_code', trim($searchTerm));

        if ($warehouse->products_visibility == 'warehouse') {
            $product->where('products.warehouse_id', '=', $warehouse->id);
        }


        // if ($request->has('products')) {
        //     $selectedProducts = $request->products;
        //     $convertedSelectedProducts = [];
        //     if (count($selectedProducts) > 0) {
        //         foreach ($selectedProducts as $selectedProduct) {
        //             $convertedSelectedProducts[] = $this->getIdFromHash($selectedProduct);
        //         }
        //     }
        //     $product = $product->whereNotIn('products.id', $convertedSelectedProducts);
        // }

        $product = $product->first();

        $allProduct = [];

        if ($warehouseId == '') {
            return $allProduct;
        } else {
            $warehouseHashId = Hashids::decode($warehouseId);
            if (count($warehouseHashId) > 0) {
                $warehouseId = $warehouseHashId[0];
            }
        }

        if ($product) {
            $productDetails = $product->details;
            $tax = Tax::find($productDetails->tax_id);

            if ($orderType == 'purchases' || $orderType == 'quotations' || ($orderType == 'sales' && $productDetails->current_stock > 0) || ($orderType == 'sales-returns') || ($orderType == 'purchase-returns' && $productDetails->current_stock > 0) || ($orderType == 'stock-transfers' && $productDetails->current_stock > 0)) {
                $stockQuantity = $productDetails->current_stock;
                $unit = $product->unit_id != null ? Unit::find($product->unit_id) : null;

                if ($orderType == 'purchases' || $orderType == 'purchase-returns' || $orderType == 'stock-transfers') {
                    $unitPrice = $productDetails->purchase_price;
                    $taxType = $productDetails->purchase_tax_type;
                } else if ($orderType == 'sales' || $orderType == 'sales-returns' || $orderType == 'quotations') {
                    $unitPrice = $productDetails->sales_price;
                    $taxType = $productDetails->sales_tax_type;
                }

                $singleUnitPrice = $unitPrice;

                if ($tax && $tax->rate != '') {
                    $taxRate = $tax->rate;

                    if ($taxType == 'inclusive') {
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

                $prodId = $this->getIdFromHash($product->x_id);
                $lastsellprice = 0;
                if ($userid != '') {
                    $getlastRate = OrderItem::where('user_id', $this->getIdFromHash($userid))->where('product_id', $prodId)->orderBy('id', 'desc')->first();
                    // // Log::info("order details" . $getlastRate->unit_price );
                    if (isset($getlastRate))
                        $lastsellprice = $getlastRate->unit_price;
                }

                $allProduct = [
                    'item_id'    =>  '',
                    'xid'    =>  $product->xid,
                    'name'    =>  $product->name,
                    'image'    =>  $product->image,
                    'image_url'    =>  $product->image_url,
                    'discount_rate'    =>  0,
                    'total_discount'    =>  0,
                    'x_tax_id'    =>  $tax ? $tax->xid : null,
                    'tax_type'    =>  $taxType,
                    'tax_rate'    =>  $taxRate,
                    'total_tax'    =>  $taxAmount,
                    'x_unit_id'    =>  Hashids::encode($product->unit_id),
                    'unit'    =>  $unit,
                    'unit_price'    =>  $unitPrice,
                    'single_unit_price'    =>  $singleUnitPrice,
                    'subtotal'    =>  $subTotal,
                    'quantity'    =>  1,
                    'stock_quantity'    =>  $stockQuantity,
                    'unit_short_name'    =>  $unit ? $unit->short_name : '',
                    'wholesale_price' => $productDetails->wholesale_price,
                    'is_wholesale_only' => $productDetails->is_wholesale_only,
                    'wholesale_quantity' => $productDetails->wholesale_quantity,
                    'price_history' => $productDetails->price_history,
                    'Last_selling' => $lastsellprice,
                    'wholesale' => $product->wholesale1,
                    'is_shipping' => $productDetails->is_shipping,
                    'shipping_price' => $productDetails->shipping_price,
                    'purchase_price' => $productDetails->purchase_price,
                    "sales_price" => $productDetails->sales_price,
                    'mrp' => $productDetails->mrp
                ];
            }

            // All Type products
            if (!$request->has('order_type')) {
                $allProduct = [
                    'xid'    =>  $product->xid,
                    'name'    =>  $product->name,
                    'image'    =>  $product->image,
                    'image_url'    =>  $product->image_url,
                    'stock_quantity'    =>  $productDetails->current_stock,
                ];
            }
        }

        return ApiResponse::make('Fetched Successfully', [
            'product' => $allProduct
        ]);
    }

    public function getWarehouseStock(Request $request)
    {
        $warehouse = warehouse();
        $stockValue = "-";

        $warehouseId = $warehouse->id;
        $productId = $this->getIdFromHash($request->product_id);

        if ($warehouseId != '' && $productId != '') {
            $stock = ProductDetails::withoutGlobalScope('current_warehouse')
                ->where('warehouse_id', '=', $warehouseId)
                ->where('product_id', '=', $productId)
                ->first();

            if ($stock) {
                $stockValue = $stock->current_stock;
            }
        }

        return ApiResponse::make('Fetched Successfully', ['stock' => $stockValue]);
    }

    public function import(ImportRequest $request)
    {
        if ($request->hasFile('file')) {
            Excel::import(new ProductImport, request()->file('file'));
        }

        return ApiResponse::make('Imported Successfully', []);
    }

    public function Updateimport(ImportRequest $request)
    {
        if ($request->hasFile('file')) {
            Excel::import(new ProductUpdateImport, request()->file('file'));
        }

        return ApiResponse::make('Imported Successfully', []);
    }

    public function export()
    {
        return Excel::download(new ProductExport, 'product.xlsx');
    }

    public function updatePrice()
    {
        $request = request();
        $company = company();
        $timezone = $company->timezone;
        $product_id = $this->getIdFromHash($request->id);
        $newPrice = $request->sales_price;
        $warehouse = warehouse();
        $warehouseId = $warehouse->id;
        $productDetail = ProductDetails::where('id', $product_id)->where('product_details.warehouse_id', $warehouseId)->first();



        if (!$productDetail) {
            return ApiResponse::make('Not Found');
        }



        if ($productDetail->sales_price != $newPrice) {
            if ($productDetail->price_history != '') {
                $oldPrice = json_decode($productDetail->price_history);
                array_push($oldPrice, ['sale_price' => $newPrice, 'purchase_price' => $productDetail->purchase_price, 'mrp' => $productDetail->mrp, 'date' => Carbon::now()->tz($timezone)]);
                $productDetail->price_history = $oldPrice;
            } else {
                $productDetail->price_history = json_encode([['sale_price' => $newPrice, 'purchase_price' => $productDetail->purchase_price, 'mrp' => $productDetail->mrp, 'date' => Carbon::now()->tz($timezone)]]);
            }
        }

        $productDetail->sales_price = $newPrice;
        $productDetail->save();


        return ApiResponse::make('Success');
    }
}
