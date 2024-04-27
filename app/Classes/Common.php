<?php

namespace App\Classes;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Company;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Lang;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPayment;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductCustomField;
use App\Models\ProductDetails;
use App\Models\Settings;
use App\Models\StaffMember;
use App\Models\StockAdjustment;
use App\Models\StockHistory;
use App\Models\SubscriptionPlan;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\User;
use App\Models\UserDetails;
use App\Models\Warehouse;
use App\Models\WarehouseHistory;
use App\Scopes\CompanyScope;
use Carbon\Carbon;
use Google\Service\ManufacturerCenter\ProductDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Nwidart\Modules\Facades\Module;
use Vinkla\Hashids\Facades\Hashids;

class Common
{
    public static function getFolderPath($type = null)
    {
        $paths = [
            'companyLogoPath' => 'companies',
            'userImagePath' => 'users',
            'expenseBillPath' => 'expenses',
            'brandImagePath' => 'brands',
            'categoryImagePath' => 'categories',
            'productImagePath' => 'products',
            'orderDocumentPath' => 'orders',
            'frontBannerPath' => 'banners',
            'audioFilesPath' => 'audio',
            'langImagePath' => 'langs',
            'warehouseLogoPath' => 'warehouses',
            'websiteImagePath' => 'website',
            'offlineRequestDocumentPath' => 'offline-requests',
            'product_cards' => 'product-cards',
            'purchases' => 'purchases',
        ];

        return ($type == null) ? $paths : $paths[$type];
    }

    public static function allUnits()
    {
        return [
            'piece' => [
                'name' => 'piece',
                'short_name' => 'pc',
                'operator' => 'multiply',
                'operator_value' => '1',
            ],
            'meter' => [
                'name' => 'meter',
                'short_name' => 'm',
                'operator' => 'multiply',
                'operator_value' => '1',
            ],
            'kilogram' =>  [
                'name' => 'kilogram',
                'short_name' => 'kg',
                'operator' => 'multiply',
                'operator_value' => '1',
            ],
            'liter' =>  [
                'name' => 'liter',
                'short_name' => 'l',
                'operator' => 'multiply',
                'operator_value' => '1',
            ]
        ];
    }

    public static function allBaseUnitArray($allBaseUnits, $baseUnitId = 'all')
    {
        $allUnitArray = [];

        foreach ($allBaseUnits as $allBaseUnit) {
            $allUnitArray[$allBaseUnit->id][] = [
                'id' => $allBaseUnit->id,
                'name' => $allBaseUnit->name,
                'operator'  => $allBaseUnit->operator,
                'operator_value' => $allBaseUnit->operator_value,
                'short_name' => $allBaseUnit->short_name
            ];

            $allUnitCollections = Unit::select('id', 'name', 'short_name', 'operator', 'operator_value')->where('parent_id', $allBaseUnit->id)->get();
            foreach ($allUnitCollections as $allUnitCollection) {
                $allUnitArray[$allBaseUnit->id][] = [
                    'id' => $allUnitCollection->id,
                    'name' => $allUnitCollection->name,
                    'operator'  => $allUnitCollection->operator,
                    'operator_value' => $allUnitCollection->operator_value,
                    'short_name' => $allUnitCollection->short_name
                ];
            }
        }

        return $baseUnitId != 'all' ? $allUnitArray[$baseUnitId] : $allUnitArray;
    }

    public static function updateUserAmount($userId, $warehouseId)
    {
        if ($userId) {
            $user = Customer::withoutGlobalScope('type')->find($userId);
            $userDetails = UserDetails::withoutGlobalScope('current_warehouse')
                ->where('user_id', $userId)
                ->where('warehouse_id', $warehouseId)
                ->first();

            $totalPurchaseAmount = Order::where('user_id', '=', $user->id)
                ->where('warehouse_id', '=', $warehouseId)
                ->where('order_type', '=', 'purchases')
                ->sum('total');
            $totalPurchaseReturnAmount = Order::where('user_id', '=', $user->id)
                ->where('warehouse_id', '=', $warehouseId)
                ->where('order_type', '=', 'purchase-returns')
                ->sum('total');

            $totalSalesAmount = Order::where('user_id', '=', $user->id)
                ->where('warehouse_id', '=', $warehouseId)
                ->where('order_type', '=', 'sales')
                ->sum('total');
            $totalSalesReturnAmount = Order::where('user_id', '=', $user->id)
                ->where('warehouse_id', '=', $warehouseId)
                ->where('order_type', '=', 'sales-returns')
                ->sum('total');

            // Amount generated by payments
            $totalPaidAmountByUser = Payment::where('user_id', $user->id)->where('warehouse_id', '=', $warehouseId)->where('payment_type', "in")->sum('amount');
            $totalPaidAmountToUser = Payment::where('user_id', $user->id)->where('warehouse_id', '=', $warehouseId)->where('payment_type', "out")->sum('amount');
            $userTotalPaidPayment = $totalPaidAmountByUser - $totalPaidAmountToUser;

            // Amount generated by orders
            $userWillPay = $totalSalesAmount + $totalPurchaseReturnAmount;
            $userWillReceive = $totalPurchaseAmount + $totalSalesReturnAmount;
            $userTotalOrderAmount = $userWillPay - $userWillReceive;

            $purchaseOrderCount = Order::where('user_id', '=', $user->id)
                ->where('order_type', '=', 'purchases')
                ->where('warehouse_id', '=', $warehouseId)
                ->count();

            $purchaseReturnOrderCount = Order::where('user_id', '=', $user->id)
                ->where('order_type', '=', 'purchase-returns')
                ->where('warehouse_id', '=', $warehouseId)
                ->count();

            $salesOrderCount = Order::where('user_id', '=', $user->id)
                ->where('order_type', '=', 'sales')
                ->where('warehouse_id', '=', $warehouseId)
                ->count();

            $salesReturnOrderCount = Order::where('user_id', '=', $user->id)
                ->where('order_type', '=', 'sales-returns')
                ->where('warehouse_id', '=', $warehouseId)
                ->count();
            // Log::info([$purchaseOrderCount]);
            $userDetails->purchase_order_count = $purchaseOrderCount;
            $userDetails->purchase_return_count = $purchaseReturnOrderCount;
            $userDetails->sales_order_count = $salesOrderCount;
            $userDetails->sales_return_count = $salesReturnOrderCount;

            $userDetails->total_amount = $userTotalOrderAmount;


            if ($userDetails->opening_balance_type == "receive") {
                $userDetails->paid_amount = $userTotalPaidPayment - $userDetails->opening_balance;
            } else {
                $userDetails->paid_amount = $userTotalPaidPayment + $userDetails->opening_balance;
            }

            $userDetails->due_amount = $userDetails->total_amount - $userDetails->paid_amount;
            $userDetails->save();
        }
    }

    public static function updateWarehouseHistory($type, $typeObject, $action = "delete")
    {
        if ($type == 'order') {
            $orderType = $typeObject->order_type;

            // Deleting Order and order item
            // Before inserting new
            WarehouseHistory::where(function ($query) use ($orderType) {
                $query->where('type', 'order-items')
                    ->orWhere('type', $orderType);
            })
                ->where('order_id', $typeObject->id)
                ->delete();

            if ($action == "add_edit") {
                $warehouseHistory =  new WarehouseHistory();
                $warehouseHistory->date = $typeObject->order_date;
                $warehouseHistory->order_id = $typeObject->id;
                $warehouseHistory->warehouse_id = $typeObject->warehouse_id;
                $warehouseHistory->user_id = $typeObject->user_id;
                $warehouseHistory->amount = $typeObject->total;
                $warehouseHistory->status = $typeObject->payment_status;
                $warehouseHistory->type = $typeObject->order_type;
                $warehouseHistory->quantity = $typeObject->total_quantity;
                $warehouseHistory->updated_at = Carbon::now();
                $warehouseHistory->transaction_number = $typeObject->invoice_number;
                $warehouseHistory->save();

                // Saving order items
                $orderItems = $typeObject->items;

                foreach ($orderItems as $orderItem) {
                    $warehouseHistory =  new WarehouseHistory();
                    $warehouseHistory->date = $typeObject->order_date;
                    $warehouseHistory->order_id = $typeObject->id;
                    $warehouseHistory->order_item_id = $orderItem->id;
                    $warehouseHistory->warehouse_id = $typeObject->warehouse_id;
                    $warehouseHistory->user_id = $typeObject->user_id;
                    $warehouseHistory->product_id = $orderItem->product_id;
                    $warehouseHistory->amount = $orderItem->subtotal;
                    $warehouseHistory->status = $typeObject->payment_status;
                    $warehouseHistory->type = "order-items";
                    $warehouseHistory->quantity = $orderItem->quantity;
                    $warehouseHistory->updated_at = Carbon::now();
                    $warehouseHistory->transaction_number = $typeObject->invoice_number;
                    $warehouseHistory->save();
                }
            }

            Common::updateOrderAmount($typeObject->id);
        } else if ($type == 'payment') {
            $paymentType = 'payment-' . $typeObject->payment_type;

            // Deleting Order and order item
            // Before inserting new
            WarehouseHistory::where('type', $paymentType)
                ->where('payment_id', $typeObject->id)
                ->delete();

            if ($action == "add_edit") {
                $warehouseHistory =  new WarehouseHistory();
                $warehouseHistory->date = $typeObject->date;
                $warehouseHistory->payment_id = $typeObject->id;
                $warehouseHistory->warehouse_id = $typeObject->warehouse_id;
                $warehouseHistory->user_id = $typeObject->user_id;
                $warehouseHistory->amount = $typeObject->amount;
                $warehouseHistory->status = "paid";
                $warehouseHistory->type = $paymentType;
                $warehouseHistory->quantity = 0;
                $warehouseHistory->updated_at = Carbon::now();
                $warehouseHistory->transaction_number = $typeObject->payment_number;
                if (isset($typeObject->order_id)) {
                    $warehouseHistory->order_id = $typeObject->order_id;
                }
                $warehouseHistory->save();

                $paymentOrders = OrderPayment::where('payment_id', $typeObject->id)->get();
                foreach ($paymentOrders as $paymentOrder) {
                    $warehouseHistory =  new WarehouseHistory();
                    $warehouseHistory->date = $typeObject->date;
                    $warehouseHistory->payment_id = $typeObject->id;
                    $warehouseHistory->warehouse_id = $typeObject->warehouse_id;
                    $warehouseHistory->user_id = $typeObject->user_id;
                    $warehouseHistory->order_id = $paymentOrder->order_id;
                    $warehouseHistory->amount = $paymentOrder->amount;
                    $warehouseHistory->status = "paid";
                    $warehouseHistory->type = "payment-orders";
                    $warehouseHistory->quantity = 0;
                    $warehouseHistory->updated_at = Carbon::now();
                    $warehouseHistory->transaction_number = $typeObject->payment_number;
                    $warehouseHistory->save();
                }
            }

            // TODO - if it is order payment then
            // Enter OrderPayment::where('payment_id', $typeObject->id)->get();

            Common::updateUserAmount($typeObject->user_id, $typeObject->warehouse_id);
        }
    }

    public static function updateOrderAmount($orderId)
    {
        $order = Order::find($orderId);

        // In delete order case order will not be available
        // So no need to update order details like due, paid amount
        // But we will updateUserAmount from the OrderController
        if ($order) {

            $checkOnlineOrder = explode('_', $order->invoice_number)[0] == 'ON';
            if ($checkOnlineOrder) {
                $order->order_type = "online-orders";
            }
            $totalPaidAmount = OrderPayment::where('order_id', $order->id)->sum('amount');

            //    Log::info(["totalPaidAmount", $totalPaidAmount]);

            $ordertotal = round($order->total, 2);

            $dueAmount = round($ordertotal - $totalPaidAmount, 2);

            //    Log::info(["dueAmount", $dueAmount]);

            if ($dueAmount <= "0.01") {
                if ($dueAmount < 0) {
                    $dueAmount = 0;
                }
                $orderPaymentStatus = 'paid';
            } else if ($dueAmount >= $ordertotal) {
                $orderPaymentStatus = 'unpaid';
            } else {
                $orderPaymentStatus = 'partially_paid';
            }

            $order->due_amount = $dueAmount;
            $order->paid_amount = $totalPaidAmount;
            $order->payment_status = $orderPaymentStatus;
            $order->save();

            // Update Customer or Supplier total amount, due amount, paid amount
            self::updateUserAmount($order->user_id, $order->warehouse_id);
        }
    }

    public static function uploadFile($request)
    {
        $folder = $request->folder;
        $folderString = "";

        if ($folder == "user") {
            $folderString = "userImagePath";
        } else if ($folder == "company") {
            $folderString = "companyLogoPath";
        } else if ($folder == "brand") {
            $folderString = "brandImagePath";
        } else if ($folder == "category") {
            $folderString = "categoryImagePath";
        } else if ($folder == "product") {
            $folderString = "productImagePath";
        } else if ($folder == "banners") {
            $folderString = "frontBannerPath";
        } else if ($folder == "langs") {
            $folderString = "langImagePath";
        } else if ($folder == "expenses") {
            $folderString = "expenseBillPath";
        } else if ($folder == "warehouses") {
            $folderString = "warehouseLogoPath";
        } else if ($folder == "website") {
            $folderString = "websiteImagePath";
        } else if ($folder == "offline-requests") {
            $folderString = "offlineRequestDocumentPath";
        } else if ($folder == "product_cards") {
            $folderString = "product_cards";
        } else if ($folder == "purchase_order") {
            $folderString = "purchases";
        }
        Log::info(["folderString", $folderString]);

        $folderPath = self::getFolderPath($folderString);

        if ($request->hasFile('image') || $request->hasFile('file')) {
            $largeLogo  = $request->hasFile('image') ? $request->file('image') : $request->file('file');

            $fileName   = $folder . '_' . strtolower(Str::random(20)) . '.' . $largeLogo->getClientOriginalExtension();
            $largeLogo->storePubliclyAs($folderPath, $fileName);
        }

        return [
            'file' => $fileName,
            'file_url' => self::getFileUrl($folderPath, $fileName),
        ];
    }

    public static function checkFileExists($folderString, $fileName)
    {
        $folderPath = self::getFolderPath($folderString);

        $fullPath = $folderPath . '/' . $fileName;

        return Storage::exists($fullPath);
    }

    public static function getFileUrl($folderPath, $fileName)
    {
        if (config('filesystems.default') == 's3') {
            $path = $folderPath . '/' . $fileName;

            return Storage::url($path);
        } else {
            $path =  'uploads/' . $folderPath . '/' . $fileName;

            return asset($path);
        }
    }

    public static function generateOrderUniqueId()
    {
        return Str::random(20);
    }

    public static function getSalesOrderTax($taxRate, $salesPrice, $taxType)
    {
        if ($taxRate != 0) {
            if ($taxType == 'exclusive') {
                $taxAmount = ($salesPrice * ($taxRate / 100));
                return $taxAmount;
            } else {
                $singleUnitPrice = ($salesPrice * 100) / (100 + $taxRate);
                $taxAmount = ($singleUnitPrice) * ($taxRate / 100);
                return $taxAmount;
            }
        } else {
            return 0;
        }
    }

    public static function getSalesPriceWithTax($taxRate, $salesPrice, $taxType)
    {
        if ($taxType == 'exclusive') {
            $taxAmount = ($salesPrice * ($taxRate / 100));
            $finalSalesPrice = $salesPrice + $taxAmount;

            return $finalSalesPrice;
        } else {
            return $salesPrice;
        }
    }

    public static function recalculateOrderStock($warehouseId, $productId, $unitprice = "0.00")
    {
        //  Log::info([$warehouseId, $productId]);
        $purchaseOrderCount = self::calculateOrderCount('purchases', $warehouseId, $productId);
        $purchaseReturnsOrderCount = self::calculateOrderCount('purchase-returns', $warehouseId, $productId);
        $salesOrderCount = self::calculateOrderCount('sales',  $warehouseId, $productId);
        $salesReturnsOrderCount = self::calculateOrderCount('sales-returns', $warehouseId, $productId);

        $addStockAdjustment = StockAdjustment::where('warehouse_id', '=', $warehouseId)
            ->where('product_id', '=', $productId)
            ->where('adjustment_type', '=', 'add')
            ->sum('quantity');
        $subtractStockAdjustment = StockAdjustment::where('warehouse_id', '=', $warehouseId)
            ->where('product_id', '=', $productId)
            ->where('adjustment_type', '=', 'subtract')
            ->sum('quantity');

        // Stock Transfer
        $addStockTransfer = self::calculateOrderCount('stock-transfers', $warehouseId, $productId);
        $subtractStockTransfer = OrderItem::join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.from_warehouse_id', '=', $warehouseId)
            ->where('order_items.product_id', '=', $productId)
            ->where('orders.order_type', '=', 'stock-transfers')
            ->sum('order_items.quantity');

        $newStockQuantity = $purchaseOrderCount - $salesOrderCount + $salesReturnsOrderCount - $purchaseReturnsOrderCount + $addStockAdjustment - $subtractStockAdjustment + $addStockTransfer - $subtractStockTransfer;

        // Log::info([$purchaseOrderCount, $salesOrderCount, $salesReturnsOrderCount, $purchaseReturnsOrderCount, $addStockAdjustment, $subtractStockAdjustment, $addStockTransfer, $subtractStockTransfer]);

        // Updating Warehouse Stock
        $productDetails = ProductDetails::withoutGlobalScope('current_warehouse')
            ->where('warehouse_id', '=', $warehouseId)
            ->where('product_id', '=', $productId)
            ->first();
        $currentStock = $newStockQuantity + $productDetails->opening_stock;

        // Log::info("CURRENT STOCK" . $currentStock);
        $productDetails->current_stock = $currentStock;
        if ($unitprice != "0.00") {
            $productDetails->purchase_price = $unitprice;
        }

        // Log::info("STOCK ALERT" . $productDetails->stock_quantitiy_alert);

        if ($productDetails->stock_quantitiy_alert != null && $currentStock < $productDetails->stock_quantitiy_alert) {
            $productDetails->status = 'out_of_stock';
        } else {
            if ($currentStock < 0) {
                $productDetails->status = 'out_of_stock';
            } else {
                $productDetails->status = 'in_stock';
            }
        }





        $productDetails->save();
    }

    public static function calculateOrderCount($orderType, $warehouseId, $productId)
    {
        // this condition added by kali on 17th Jan 2024 for stock issue with online and offline. Stock count is not compaered with online orders now added the condition with sales
        if (($orderType == "sales") || ($orderType == "online-orders")) {
            // $orderCount = OrderItem::join('orders', 'orders.id', '=', 'order_items.order_id')
            // ->where('orders.order_type', '=', 'sales')->orwhere('orders.order_type', '=', 'online-orders')
            // ->where('orders.warehouse_id', '=', $warehouseId)
            // ->where('order_items.product_id', '=', $productId)
            // ->sum('order_items.quantity');


            $orderCount = OrderItem::join('orders', 'orders.id', '=', 'order_items.order_id')
                ->where(function ($query) use ($warehouseId, $productId) {
                    $query->where('orders.order_type', '=', 'sales')
                        ->orWhere('orders.order_type', '=', 'online-orders');
                })
                ->where('orders.warehouse_id', '=', $warehouseId)
                ->where('orders.cancelled', '=', 0)
                ->where('order_items.product_id', '=', $productId)
                ->where(function ($query) {
                    $query->where('orders.notes', '!=', 'Imported Data')
                        ->orWhere('orders.notes', '=', null);
                })
                ->sum('order_items.quantity');
        } else {
            $orderCount = OrderItem::join('orders', 'orders.id', '=', 'order_items.order_id')
                ->where('orders.warehouse_id', '=', $warehouseId)
                ->where('order_items.product_id', '=', $productId)
                ->where('orders.order_type', '=', $orderType)
                ->sum('order_items.quantity');
        }


        return $orderCount;
    }

    public static function moduleInformations()
    {
        $allModules = Module::all();
        $allEnabledModules = Module::allEnabled();
        $installedModules = [];
        $enabledModules = [];

        foreach ($allModules as $key => $allModule) {
            $modulePath = $allModule->getPath();
            $versionFileName = app_type() == 'multiple' ? 'superadmin_version.txt' : 'version.txt';
            $version = File::get($modulePath . '/' . $versionFileName);

            $installedModules[] = [
                'verified_name' => $key,
                'current_version' => preg_replace("/\r|\n/", "", $version)
            ];
        }

        foreach ($allEnabledModules as $allEnabledModuleKey => $allEnabledModule) {
            $enabledModules[] = $allEnabledModuleKey;
        }

        return [
            'installed_modules' => $installedModules,
            'enabled_modules' => $enabledModules,
        ];
    }

    public static function getIdFromHash($hash)
    {
        if ($hash != "") {
            $decodedIds = Hashids::decode($hash);

            if (!empty($decodedIds) && isset($decodedIds[0])) {
                $id = $decodedIds[0];
                return $id;
            }
        }

        return $hash;
    }

    public static function getHashFromId($id)
    {
        //    // Log::info("515".$id);
        $id = Hashids::encode($id);

        return $id;
    }

    public static function storeAndUpdateOrder($order, $oldOrderId)
    {
        $request = request();
        $warehouse = warehouse();
        $loggedUser = user();
        $user = User::where('email', $loggedUser->email)->first();
        $userId = $user->id;

        try {
            $productItemss = [];
            foreach ($request['product_items'] as $key => $req) {

                $companyId = warehouse()->company_id;
                $productName = $req['name'];
                // $product = Product::where('item_code', $req['item_code'])->first();
                //  $product = Product::where('name', 'like', '%' . $productName . '%')->first();
                $product = Product::where('name', $productName)->where('company_id', $companyId)->first();
                if ($product) {
                    $productId = $product->id;
                } else {
                    $newProduct = new Product();
                    $newProduct->company_id = $companyId;
                    $newProduct->warehouse_id = $warehouse->id;
                    $newProduct->name = $req['name'];
                    $newProduct->slug = strtolower(str_replace(' ', '-', preg_replace('/[^a-zA-Z0-9\s]/', '', $req['name'])));
                    $newProduct->barcode_symbology = "CODE128";
                    // $newProduct->hsn_sac_code = $req['hsn_sac_code'];
                    $newProduct->item_code = $req['item_code'];
                    $newProduct->image = null;
                    // Check if category exists or create a new one
                    $category = Category::where('name', $req['category'])->first();
                    if ($category) {
                        $newProduct->category_id = $category->id;
                    } else {
                        $newCategory = Category::create([
                            'company_id' => $companyId,
                            'name' => $req['category'],
                            'slug' => $req['category'] . random_int(1, 3),
                        ]);
                        $newProduct->category_id = $newCategory->id;
                    }
                    // Check if brand exists or create a new one
                    $brand = Brand::where('name', $req['brand'])->first();

                    if ($brand) {
                        $newProduct->brand_id = $brand->id;
                    } else {
                        $newBrand = Brand::create([
                            'company_id' => $companyId,
                            'name' => $req['brand'],
                            'slug' => strtolower(str_replace(' ', '-', preg_replace('/[^a-zA-Z0-9\s]/', '', $req['brand']))),
                        ]);
                        $newProduct->brand_id = $newBrand->id;
                    }

                    // Check if unit exists or create a new one
                    $unit = Unit::where('name', $req['unit'])->first();
                    if ($unit) {
                        $newProduct->unit_id = $unit->id;
                    } else {
                        $newUnit = Unit::create([
                            'name' => $req['unit'],
                            'short_name' => $req['unit'],
                            'slug' => $req['unit'] . random_int(1, 3),
                            'operator' => "multiply",
                            'operator_value' => 1,
                        ]);
                        $newProduct->unit_id = $newUnit->id;
                    }
                    $newProduct->description = null;
                    $newProduct->user_id = $userId;
                    $newProduct->save();
                    $productId = $newProduct->id;
                }
                $chekFirstProduct = ProductDetails::where('product_id', $productId)
                    ->where('warehouse_id', $warehouse->id)
                    ->first();
                // Log::info("CHECK FIRST PRODUCT" . $chekFirstProduct);
                // Log::info("REQUEST TAX RATE" . $req['tax_rate']);
                $taxRate = $req['tax_rate'];
                $existingTax = Tax::where('rate', $taxRate)->first();
                $taxId = null;
                if ($existingTax) {
                    $taxId = $existingTax->id;
                } else {
                    // Tax does not exist, create a new one
                    $newTax = Tax::create([
                        'company_id' => $companyId,
                        'name' => 'GST',
                        'rate' => $taxRate,
                    ]);

                    $taxId = $newTax->id;
                }

                //                 if ($chekFirstProduct == null) {
                // // Log::info("INSIDE THE 672");
                //                     $chekFirstProduct->update([
                //                         'warehouse_id' => $warehouse->id,
                //                         'product_id' => $productId,
                //                         'tax_id' => $taxId,
                //                         // 'mrp' => $req['mrp'],
                //                         // 'purchase_price' => $req['purchase_price'],
                //                         // 'sales_price' => $req['sales_price'],
                //                         // 'current_stock' => $req['opening_stock'],
                //                     ]);

                //                     $productDetails = ProductDetails::withoutGlobalScope('current_warehouse')
                //                         ->where('warehouse_id', '=',  $warehouse->id)
                //                         ->where('product_id', '=', $productId)
                //                         ->first();

                //                     if ($productDetails->stock_quantitiy_alert !== null && $productDetails->current_stock < $productDetails->stock_quantitiy_alert) {
                //                         $productDetails->status = 'out_of_stock';
                //                     } else {
                //                         $productDetails->status = 'in_stock';
                //                     }

                //                     $req['xid'] = $chekFirstProduct['product_id'];
                //                 }
                // else {

                //     $product = ProductDetails::create([
                //         'warehouse_id' => $warehouse->id,
                //         'product_id' => $productId,
                //         'tax_id' => $taxId,
                //         'mrp' => $req['mrp'],
                //         'purchase_price' => $req['purchase_price'],
                //         'sales_price' => $req['sales_price'],
                //     ]);
                //     $req['xid'] = $product['product_id'];
                // }
                array_push($productItemss, $req);
            }
        } catch (\Exception $e) {
            Log::error("An error occurred: " . $e->getMessage());
        }



        $productItems = $request->has('product_items') ? $productItemss : [];

        // If Order item removed when editing an order
        if ($oldOrderId != "") {
            $removedOrderItems = $request->removed_items;
            foreach ($removedOrderItems as $removedOrderItem) {
                if ($removedOrderItem != "") {
                    $removedItem = OrderItem::find(self::getIdFromHash($removedOrderItem));
                    $removedItem->delete();
                }
            }
        }

        return self::storeAndUpdateOrderItem($order, $productItems, $oldOrderId);
    }

    public static function storeAndUpdateOrderFront($order, $oldOrderId)
    {
        $request = request();
        $warehouse = Warehouse::where('slug', $request->warehouse)->first();
        // $loggedUser = user();
        // $user = User::where('email', $loggedUser->email)->first();
        // $userId = $user->id;
        $user = auth('api_front')->user();
        $userId = $user->id;

        try {
            $productItemss = [];
            foreach ($request['products'] as $key => $req) {

                $companyId = $warehouse->company_id;
                $productName = $req['name'];
                // $product = Product::where('item_code', $req['item_code'])->first();
                //  $product = Product::where('name', 'like', '%' . $productName . '%')->first();
                $product = Product::where('name', $productName)->where('company_id', $companyId)->first();
                if ($product) {
                    $productId = $product->id;
                } else {
                    $newProduct = new Product();
                    $newProduct->company_id = $companyId;
                    $newProduct->warehouse_id = $warehouse->id;
                    $newProduct->name = $req['name'];
                    $newProduct->slug = strtolower(str_replace(' ', '-', preg_replace('/[^a-zA-Z0-9\s]/', '', $req['name'])));
                    $newProduct->barcode_symbology = "CODE128";
                    // $newProduct->hsn_sac_code = $req['hsn_sac_code'];
                    $newProduct->item_code = $req['item_code'];
                    $newProduct->image = null;
                    // Check if category exists or create a new one
                    $category = Category::where('name', $req['category'])->first();
                    if ($category) {
                        $newProduct->category_id = $category->id;
                    } else {
                        $newCategory = Category::create([
                            'company_id' => $companyId,
                            'name' => $req['category'],
                            'slug' => $req['category'] . random_int(1, 3),
                        ]);
                        $newProduct->category_id = $newCategory->id;
                    }
                    // Check if brand exists or create a new one
                    $brand = Brand::where('name', $req['brand'])->first();

                    if ($brand) {
                        $newProduct->brand_id = $brand->id;
                    } else {
                        $newBrand = Brand::create([
                            'company_id' => $companyId,
                            'name' => $req['brand'],
                            'slug' => strtolower(str_replace(' ', '-', preg_replace('/[^a-zA-Z0-9\s]/', '', $req['brand']))),
                        ]);
                        $newProduct->brand_id = $newBrand->id;
                    }

                    // Check if unit exists or create a new one
                    $unit = Unit::where('name', $req['unit'])->first();
                    if ($unit) {
                        $newProduct->unit_id = $unit->id;
                    } else {
                        $newUnit = Unit::create([
                            'name' => $req['unit'],
                            'short_name' => $req['unit'],
                            'slug' => $req['unit'] . random_int(1, 3),
                            'operator' => "multiply",
                            'operator_value' => 1,
                        ]);
                        $newProduct->unit_id = $newUnit->id;
                    }
                    $newProduct->description = null;
                    $newProduct->user_id = $userId;
                    $newProduct->save();
                    $productId = $newProduct->id;
                }
                // $chekFirstProduct = ProductDetails::where('product_id', $productId)
                //     ->where('warehouse_id', $warehouse->id)
                //     ->first();

                // $taxId = $chekFirstProduct->tax_id;

                array_push($productItemss, $req);
            }
        } catch (\Exception $e) {
            Log::error("An error occurred: " . $e->getMessage());
        }



        $productItems = $request->has('products') ? $productItemss : [];

        // If Order item removed when editing an order
        if ($oldOrderId != "") {
            $removedOrderItems = $request->removed_items;
            foreach ($removedOrderItems as $removedOrderItem) {
                if ($removedOrderItem != "") {
                    $removedItem = OrderItem::find(self::getIdFromHash($removedOrderItem));
                    $removedItem->delete();
                }
            }
        }

        return self::storeAndUpdateOrderItemFront($order, $productItems, $oldOrderId);
    }

    public static function storeAndUpdateOrderItemFront($order, $productItems, $oldOrderId = "")
    {


        $orderType = $order->order_type;
        $orderDeletable = true;
        $actionType = $oldOrderId != "" ? "edit" : "add";

        $orderSubTotal = 0;
        $totalQuantities = 0;
        if (count($productItems) > 0) {

            foreach ($productItems as $productItem) {


                $productItem = (object) $productItem;



                if ($productItem->details['xid'] == '' || $productItem->details['xid'] == null) {
                    $orderItem = new OrderItem();
                    $stockHistoryQuantity = $productItem->cart_quantity;
                    $oldStockQuantity = 0;

                    // For inserting MRP
                    $productId = self::getIdFromHash($productItem->xid);
                    $warehouseId = $order->order_type == 'stock-transfers' ? $order->from_warehouse_id : $order->warehouse_id;
                    $productDetails = ProductDetails::withoutGlobalScope('current_warehouse')
                        ->where('warehouse_id', '=', $warehouseId)
                        ->where('product_id', '=', $productItem->xid)
                        ->first();

                    $orderItem->mrp = $productDetails->mrp;
                } else {
                    // $productItemId =self::getIdFromHash($productItem->details['xid']);

                    //  // Log::info("879 item id : ".$productItem->xid);
                    //  $orderItem = OrderItem::find($productItem->xid);
                    //  // Log::info($orderItem);
                    //    $orderItem = OrderItem::where('product_id', $productItem->xid)->first();
                    $orderItem = OrderItem::where('product_id', $productItem->xid)->latest('updated_at')->first();



                    if (isset($orderItem)) {
                        //    // Log::info("INNN 928");
                        $stockHistoryQuantity = $orderItem->quantity != $productItem->cart_quantity ? $productItem->cart_quantity : 0;
                        if ($orderType != "stock-transfers") {
                            $orderItem->user_id = self::getHashFromId($order->user_id);
                        }
                    } else {
                        //  // Log::info("INNN 931");
                        // Handle the case where $orderItem is not set
                        $stockHistoryQuantity = 0;
                    }
                    $oldStockQuantity = 0;
                }






                // sales return quantity update by surya
                if ($orderType == "sales-returns") {
                    $request = request();
                    $company = company();
                    $returnupdate = Order::where('invoice_number', $request->sales_invoice_no)->where('order_type', "sales")->where('company_id', $company->id)
                        ->first();
                    $order_itemId = self::getIdFromHash($returnupdate->xid);
                    $order_productId = self::getIdFromHash($productItem->xid);
                    OrderItem::where('order_id', $order_itemId)->where('product_id', $order_productId)->update(['return_quantity' => $productItem->return_quantity - $productItem->cart_quantity]);
                }

                if ($orderType == "purchase-returns") {
                    $request = request();
                    $company = company();
                    $returnupdate = Order::where('invoice_number', $request->purchase_invoice_no)->where('order_type', "purchases")->where('company_id', $company->id)
                        ->first();
                    $order_itemId = self::getIdFromHash($returnupdate->xid);
                    $order_productId = self::getIdFromHash($productItem->xid);
                    OrderItem::where('order_id', $order_itemId)->where('product_id', $order_productId)->update(['return_quantity' => $productItem->return_quantity - $productItem->cart_quantity]);
                }
                // end
                if (isset($orderItem)) {
                    $orderItem->order_id = $order->xid;
                    $orderItem->product_id = $productItem->xid;
                    $orderItem->unit_price = $productItem->details['sales_price'];
                    $orderItem->unit_id = $productItem->x_unit_id != '' ? $productItem->x_unit_id : null;
                    $orderItem->quantity = $productItem->cart_quantity;

                    //if (isset($productItem->return_quantity)) {
                    // sales return quantity update by surya
                    $orderItem->return_quantity = $orderType == "sales-returns" || $orderType == "purchase-returns" ? "0.00" : $productItem->cart_quantity;
                    $productId = $orderItem->product_id;
                } else {
                    $productId = self::getIdFromHash($productItem->xid);
                }

                // end
                //  }
                // $orderItem->tax_id = isset($productItem->x_tax_id) && $productItem->x_tax_id != '' ? $productItem->x_tax_id : null;
                // $orderItem->tax_rate = $productItem->tax['rate'];
                // $orderItem->discount_rate = 0;
                // $orderItem->total_discount = 0;
                // $orderItem->total_tax = $productItem->total_tax;
                // $orderItem->tax_type = $productItem->tax_type;
                // $orderItem->subtotal = $productItem->subtotal;
                // $orderItem->single_unit_price = $productItem->single_unit_price;
                // $orderItem->identity_code = isset($productItem->identity_code) && $productItem->identity_code != '' ? $productItem->identity_code : null;
                // if (isset($productItem->purchase_price)) {
                //     $orderItem->purchase_price = $productItem->purchase_price;
                //     }
                // $orderItem->save();

                $warehouseId = $order->warehouse_id;


                // Update warehouse stock for product


                if ($orderType == "purchases") {
                    self::recalculateOrderStock($warehouseId, $productId, $productItem->unit_price);
                } else if ($orderType == "stock-transfers") {
                    self::recalculateOrderStock($order->from_warehouse_id, $productId);
                } else {
                    self::recalculateOrderStock($warehouseId, $productId);
                }




                if (isset($orderItem)) {
                    $orderSubTotal += $orderItem->subtotal;
                    $totalQuantities += $orderItem->quantity;
                } else {
                    $orderSubTotal += 0;
                    $totalQuantities += 0;
                }

                // Tracking Stock History
                if ($stockHistoryQuantity != 0 && $orderType != 'quotations') {
                    $stockHistory = new StockHistory();
                    $stockHistory->warehouse_id = $order->warehouse_id;
                    $stockHistory->product_id = $orderItem->product_id;
                    $stockHistory->quantity = $stockHistoryQuantity;
                    $stockHistory->old_quantity = $oldStockQuantity;
                    $stockHistory->order_type = $order->order_type;
                    $stockHistory->stock_type = $orderType == 'online-orders' || $orderType == 'purchase-returns' ? 'out' : 'in';
                    $stockHistory->action_type = $actionType;
                    $stockHistory->created_by = $order->staff_user_id;
                    $stockHistory->save();
                }
            }

            $order->total_items = count($productItems);
        }

        //! This line commented out because subtotal overwrite with Zero.
        // $order->total_quantity = $totalQuantities;
        // $order->subtotal = $orderSubTotal;
        // $order->due_amount = $orderSubTotal;
        // $order->is_deletable = $orderDeletable;

        // $order->save();

        // Update Customer or Supplier total amount, due amount, paid amount
        self::updateOrderAmount($order->id);

        return $order;
    }

    public static function updateStockHistory($order, $productItems, $oldOrderId = "")
    {

        $orderType = $order->order_type;
        $orderDeletable = true;
        $actionType = $oldOrderId != "" ? "edit" : "add";

        $orderSubTotal = 0;
        $totalQuantities = 0;
        if (count($productItems) > 0) {

            foreach ($productItems as $productItem) {
                $productItem = (object) $productItem;

                $taxid_bk = self::getIdFromHash($productItem->x_tax_id);
                $existingTax = Tax::where('id', $taxid_bk)->first();

                if ($existingTax) {
                    $productId = self::getIdFromHash($productItem->x_product_id);
                    $warehouseId = $order->order_type == 'stock-transfers' ? $order->from_warehouse_id : $order->warehouse_id;
                }

                self::recalculateOrderStock($warehouseId, $productId);

                $productItemId = self::getIdFromHash($productItem->xid);
                $orderItem = OrderItem::find($productItemId);

                $stockHistoryQuantity = $orderItem->quantity != $productItem->quantity ? $productItem->quantity : 0;
                $oldStockQuantity = $orderItem->quantity;

                // Tracking Stock History
                if ($stockHistoryQuantity != 0 && $orderType != 'quotations') {
                    $stockHistory = new StockHistory();
                    $stockHistory->warehouse_id = $order->warehouse_id;
                    $stockHistory->product_id = $productId;
                    $stockHistory->quantity = $stockHistoryQuantity;
                    $stockHistory->old_quantity = $oldStockQuantity;
                    $stockHistory->order_type = $order->order_type;
                    $stockHistory->stock_type = $orderType == 'sales' || $orderType == 'purchase-returns' ? 'out' : 'in';
                    $stockHistory->action_type = $actionType;
                    $stockHistory->created_by = $order->staff_user_id;
                    $stockHistory->save();
                }
            }

            // $order->total_items = count($productItems);
        }

        // $order->total_quantity = $totalQuantities;
        // $order->subtotal = $orderSubTotal;
        // $order->total = $orderSubTotal - $order->discount;
        // $order->due_amount = $orderSubTotal;
        // $order->is_deletable = $orderDeletable;
        // $order->discount_type = $order->discount_type;
        // $order->save();

        // Update Customer or Supplier total amount, due amount, paid amount
        self::updateOrderAmount($order->id);

        return $order;
    }

    public static function storeAndUpdateOrderItem($order, $productItems, $oldOrderId = "")
    {

        $orderType = $order->order_type;
        $orderDeletable = true;
        $actionType = $oldOrderId != "" ? "edit" : "add";

        $orderSubTotal = 0;
        $orderTotal = 0;
        $totalQuantities = 0;
        $company = company();
        $timezone = $company->timezone;

        if (count($productItems) > 0) {

            foreach ($productItems as $productItem) {
                $productItem = (object) $productItem;

                // Use json_encode() to convert the object to a JSON string
                // Log::info("PRODUCT ITEM " . json_encode($productItem));

                $taxid_bk = self::getIdFromHash($productItem->x_tax_id);
                $existingTax = Tax::where('id', $taxid_bk)->first();
                $taxId = null;
                $tax_rate = $productItem->tax_rate;
                if ($existingTax) {
                    $tax_rate = $existingTax->rate;

                    $productId = self::getIdFromHash($productItem->xid);
                    $warehouseId = $order->order_type == 'stock-transfers' ? $order->from_warehouse_id : $order->warehouse_id;
                    $productDetails1 = ProductDetails::withoutGlobalScope('current_warehouse')
                        ->where('warehouse_id', '=', $warehouseId)
                        ->where('product_id', '=', $productId)
                        ->first();
                    // Log::info("TAX RATE FROM DB " . $tax_rate);
                }

                if ($productItem->item_id == '' || $productItem->item_id == null) {
                    $orderItem = new OrderItem();
                    $stockHistoryQuantity = $productItem->quantity;
                    $oldStockQuantity = 0;

                    // For inserting MRP
                    $productId = self::getIdFromHash($productItem->xid);
                    $warehouseId = $order->order_type == 'stock-transfers' ? $order->from_warehouse_id : $order->warehouse_id;
                    $productDetails = ProductDetails::withoutGlobalScope('current_warehouse')
                        ->where('warehouse_id', '=', $warehouseId)
                        ->where('product_id', '=', $productId)
                        ->first();

                    $orderItem->mrp = $productDetails->mrp;
                } else {
                    $productItemId = self::getIdFromHash($productItem->item_id);
                    $orderItem = OrderItem::find($productItemId);

                    $stockHistoryQuantity = $orderItem->quantity != $productItem->quantity ? $productItem->quantity : 0;
                    $oldStockQuantity = $orderItem->quantity;
                }




                if ($orderType != "stock-transfers") {
                    $orderItem->user_id = self::getHashFromId($order->user_id);
                }

                // sales return quantity update by surya
                if ($orderType == "sales-returns") {
                    $request = request();
                    $company = company();
                    $returnupdate = Order::where('invoice_number', $request->sales_invoice_no)->where('order_type', "sales")->where('company_id', $company->id)
                        ->first();
                    $order_itemId = self::getIdFromHash($returnupdate->xid);
                    $order_productId = self::getIdFromHash($productItem->xid);
                    OrderItem::where('order_id', $order_itemId)->where('product_id', $order_productId)->update(['return_quantity' => $productItem->return_quantity - $productItem->quantity]);
                }

                if ($orderType == "purchase-returns") {
                    $request = request();
                    $company = company();
                    $returnupdate = Order::where('invoice_number', $request->purchase_invoice_no)->where('order_type', "purchases")->where('company_id', $company->id)
                        ->first();
                    $order_itemId = self::getIdFromHash($returnupdate->xid);
                    $order_productId = self::getIdFromHash($productItem->xid);
                    OrderItem::where('order_id', $order_itemId)->where('product_id', $order_productId)->update(['return_quantity' => $productItem->return_quantity - $productItem->quantity]);
                }
                // end

                $orderItem->order_id = $order->xid;
                $orderItem->product_id = $productItem->xid;
                $orderItem->unit_price = $productItem->unit_price;
                $orderItem->unit_id = $productItem->x_unit_id != '' ? $productItem->x_unit_id : null;
                $orderItem->quantity = $productItem->quantity;

                //if (isset($productItem->return_quantity)) {
                // sales return quantity update by surya
                $orderItem->return_quantity = $orderType == "sales-returns" || $orderType == "purchase-returns" ? "0.00" : $productItem->quantity;
                // end
                //  }
                $orderItem->tax_id = isset($productItem->x_tax_id) && $productItem->x_tax_id != '' ? $productItem->x_tax_id : null;

                if ($productItem->total_discount == 0) {
                    if (($tax_rate == '') || ($tax_rate == 0)) {
                        $orderItem->tax_rate = $productItem->tax_rate;
                        $orderItem->total_tax = $productItem->total_tax;
                    } else {
                        $orderItem->tax_rate = $tax_rate;
                        if ($productDetails1->sales_tax_type == 'inclusive') {
                            $singleUnitPrice = $productItem->unit_price;
                            $singleUnitPrice = ($singleUnitPrice * 100) / (100 + $tax_rate);
                            $taxAmount = ($singleUnitPrice) * ($tax_rate / 100);
                        } else {
                            $singleUnitPrice = $productItem->unit_price;
                            $taxAmount = ($singleUnitPrice * ($tax_rate / 100));
                            $subTotal = $singleUnitPrice + $taxAmount;
                            $singleUnitPrice = $subTotal;
                        }
                        $orderItem->total_tax = $taxAmount * $productItem->quantity;
                    }
                } else {
                    $orderItem->tax_rate = $productItem->tax_rate;
                    $orderItem->total_tax = $productItem->total_tax;
                    // this code added by kali on 6th apr 2024 based on the log error
                    if (isset($productItem->discount_type)) {
                        $orderItem->discount_type = $productItem->discount_type;
                    }
                    if (isset($productItem->discount)) {
                        $orderItem->discount = $productItem->discount;
                    }
                    // $orderItem->discount_type = $productItem->discount_type;
                    // $orderItem->discount = $productItem->discount;
                }



                $orderItem->discount_rate = $productItem->discount_rate;
                $orderItem->total_discount = $productItem->total_discount;
                // if ($productItem->total_tax!=0)
                // {
                //     $orderItem->total_tax = $productItem->total_tax;
                // }



                $orderItem->tax_type = $productItem->tax_type;
                $orderItem->subtotal = $productItem->subtotal;
                $orderItem->single_unit_price = $productItem->single_unit_price;
                $orderItem->identity_code = isset($productItem->identity_code) && $productItem->identity_code != '' ? $productItem->identity_code : null;
                if (isset($productItem->purchase_price)) {
                    $orderItem->purchase_price = $productItem->purchase_price;
                }
                $orderItem->save();

                $warehouseId = $order->warehouse_id;
                $productId = $orderItem->product_id;

                // Update warehouse stock for product

                if ($orderType == "purchases") {
                    $product = ProductDetails::where('product_id', $productId)->first();
                    if ($product->sales_price != $productItem->sales_price || $product->purchase_price != $productItem->unit_price) {
                        if ($product->price_history != '') {
                            $oldPrice = json_decode($product->price_history);
                            array_push($oldPrice, ['sale_price' => $product->sales_price, 'purchase_price' => $product->purchase_price, 'mrp' => $product->mrp, 'date' => Carbon::now()->tz($timezone)]);
                            $product->price_history = $oldPrice;
                        } else {
                            $product->price_history = json_encode([['sale_price' => $product->sales_price, 'purchase_price' => $product->purchase_price, 'mrp' => $product->mrp, 'date' => Carbon::now()->tz($timezone)]]);
                        }
                    }
                    self::recalculateOrderStock($warehouseId, $productId, $productItem->unit_price);

                    $product->purchase_price = $productItem->unit_price;
                    $product->sales_price = $productItem->sales_price;
                    $product->save();
                } else if ($orderType == "stock-transfers") {
                    self::recalculateOrderStock($order->from_warehouse_id, $productId);
                } else if ($orderType != "quotations") {
                    //    Log::info("ïnside the 1101 line");
                    self::recalculateOrderStock($warehouseId, $productId);
                }



                $orderSubTotal += $orderItem->subtotal;
                $orderTotal += $orderItem->total;
                $totalQuantities += $orderItem->quantity;

                // Tracking Stock History
                if ($stockHistoryQuantity != 0 && $orderType != 'quotations') {
                    $stockHistory = new StockHistory();
                    $stockHistory->warehouse_id = $order->warehouse_id;
                    $stockHistory->product_id = $orderItem->product_id;
                    $stockHistory->quantity = $stockHistoryQuantity;
                    $stockHistory->old_quantity = $oldStockQuantity;
                    $stockHistory->order_type = $order->order_type;
                    $stockHistory->stock_type = $orderType == 'sales' || $orderType == 'purchase-returns' ? 'out' : 'in';
                    $stockHistory->action_type = $actionType;
                    $stockHistory->created_by = $order->staff_user_id;
                    $stockHistory->save();
                }
            }

            $order->total_items = count($productItems);
        }

        $order->total_quantity = $totalQuantities;
        $order->subtotal = $orderSubTotal;
        $order->total = $orderSubTotal - $order->discount;
        $order->due_amount = $orderSubTotal;
        $order->is_deletable = $orderDeletable;
        $order->discount_type = $order->discount_type;
        $order->save();

        // Update Customer or Supplier total amount, due amount, paid amount
        self::updateOrderAmount($order->id);

        return $order;
    }

    public static function updateProductCustomFields($product, $warehouseId)
    {
        $request = request();

        if ($request->has('custom_fields') && count($request->custom_fields) > 0) {
            $customFields = $request->custom_fields;

            foreach ($customFields as $customFieldKey => $customFieldValue) {
                $newCustomField = ProductCustomField::withoutGlobalScope('current_warehouse')
                    ->where('field_name', $customFieldValue)
                    ->where('product_id', $product->id)
                    ->where('warehouse_id', $warehouseId)
                    ->first();

                if (!$newCustomField) {
                    $newCustomField = new ProductCustomField();
                    $newCustomField->warehouse_id = $warehouseId;
                }

                $newCustomField->product_id = $product->id;
                $newCustomField->field_name = $customFieldKey;
                $newCustomField->field_value = $customFieldValue;
                $newCustomField->save();
            }
        }
    }

    public static function getTransactionNumber($type, $number)
    {
        $prefixs = [
            'payment-in' => 'PAY_IN_',
            'payment-out' => 'PAY_OUT_',
            'quotations' => 'QUOT_',
            'sales' => 'SALE_',
            'purchases' => 'PUR_',
            'purchase-returns' => 'PURRET_',
            'sales-returns' => 'SALERET_',
            'stock-transfers' => 'STKTRANS_',
            'online-orders' => 'ON_',
        ];

        return $prefixs[$type] . $number;
    }

    public static function getTransactionNumberNew($type, $number, $invoice_prefix, $invoice_suffix, $invoice_spliter)
    {
        $prefixs = [
            'payment-in' => 'PAY_IN',
            'payment-out' => 'PAY_OUT',
            'quotations' => 'QUOT_',
            'sales' => 'SALE_',
            'purchases' => 'PUR_',
            'purchase-returns' => 'PURRET_',
            'sales-returns' => 'SALERET_',
            'stock-transfers' => 'STKTRANS_',
            'online-orders' => 'ON_',
        ];
        $formattedNumber = substr("0000" . $number, -5);

        // Log::info("853" . $type);

        if ($type == "sales") {
            $invoice_prefix = $invoice_prefix;
        } else if ($type == "payment-in" || $type == "payment-out") {
            return $prefixs[$type] . $invoice_spliter . $formattedNumber . $invoice_spliter . $invoice_suffix;
        } else {
            $invoice_prefix = $prefixs[$type] . $invoice_prefix;
        }

        return $invoice_prefix . $invoice_spliter . $number . $invoice_spliter . $invoice_suffix;
        //   return $prefixs[$type] . $number;
    }

    public static function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }

    public static function calculateTotalUsers($companyId, $update = false)
    {
        $totalUsers =  StaffMember::withoutGlobalScope(CompanyScope::class)
            ->where('company_id', $companyId)
            ->count('id');

        if ($update) {
            DB::table('companies')
                ->where('id', $companyId)
                ->update([
                    'total_users' => $totalUsers
                ]);
        }


        return $totalUsers;
    }

    public static function addWebsiteImageUrl($settingData, $keyName)
    {
        if ($settingData && array_key_exists($keyName, $settingData)) {
            if ($settingData[$keyName] != '') {
                $imagePath = self::getFolderPath('websiteImagePath');

                $settingData[$keyName . '_url'] = Common::getFileUrl($imagePath, $settingData[$keyName]);
            } else {
                $settingData[$keyName] = null;
                $settingData[$keyName . '_url'] = asset('images/website.png');
            }
        }

        return $settingData;
    }

    public static function convertToCollection($data)
    {
        $data = collect($data)->map(function ($item) {
            return (object) $item;
        });

        return $data;
    }

    public static function addCurrencies($company)
    {
        $newCurrency = new Currency();
        $newCurrency->company_id = $company->id;
        $newCurrency->name = 'Dollar';
        $newCurrency->code = 'USD';
        $newCurrency->symbol = '$';
        $newCurrency->position = 'front';
        $newCurrency->is_deletable = false;
        $newCurrency->save();

        $rupeeCurrency = new Currency();
        $rupeeCurrency->company_id = $company->id;
        $rupeeCurrency->name = 'Rupee';
        $rupeeCurrency->code = 'INR';
        $rupeeCurrency->symbol = '₹';
        $rupeeCurrency->position = 'front';
        $rupeeCurrency->is_deletable = false;
        $rupeeCurrency->save();

        $enLang = Lang::where('key', 'en')->first();

        $company->currency_id = $newCurrency->id;
        $company->lang_id = $enLang->id;
        $company->save();

        return $company;
    }

    public static function checkSubscriptionModuleVisibility($itemType)
    {
        $visible = true;
        //// Log::info($warehouseId);

        if (app_type() == 'multiple') {
            if ($itemType == 'product') {
                $company = company();
                if ($company)
                    $productsCounts = Product::where('company_id', '=', $company->id)
                        ->count();


                $visible = $company && $company->subscriptionPlan && $productsCounts < $company->subscriptionPlan->max_products ? true : false;
            }

            if ($itemType == 'order') {
                $company = company();
                if ($company)
                    $salesOrderCount = Order::where('order_type', '=', 'sales')
                        ->where('company_id', '=', $company->id)
                        ->count();
                $visible = $company && $company->subscriptionPlan && $salesOrderCount < $company->subscriptionPlan->max_orders ? true : false;
            }
        }

        return $visible;
    }

    public static function allVisibleSubscriptionModules()
    {
        $visibleSubscriptionModules = [];

        if (self::checkSubscriptionModuleVisibility('order')) {
            $visibleSubscriptionModules[] = 'order';
        }
        if (self::checkSubscriptionModuleVisibility('product')) {
            $visibleSubscriptionModules[] = 'product';
        }


        return $visibleSubscriptionModules;
    }

    public static function insertInitSettings($company)
    {
        if ((app_type() == 'multiple' && $company->is_global == 1) || (app_type() == 'single' && $company->is_global == 0)) {

            $storageLocalSettingCount = Settings::withoutGlobalScope(CompanyScope::class)
                ->where('setting_type', 'storage')
                ->where('name_key', 'local')
                ->where('is_global', $company->is_global)
                ->where('company_id', $company->id)
                ->count();
            if ($storageLocalSettingCount == 0) {
                $local = new Settings();
                $local->company_id = $company->id;
                $local->setting_type = 'storage';
                $local->name = 'Local';
                $local->name_key = 'local';
                $local->status = true;
                $local->is_global = $company->is_global;
                $local->save();
            }


            $storageAwsSettingCount = Settings::withoutGlobalScope(CompanyScope::class)
                ->where('setting_type', 'storage')
                ->where('name_key', 'aws')
                ->where('is_global', $company->is_global)
                ->where('company_id', $company->id)
                ->count();
            if ($storageAwsSettingCount == 0) {
                $aws = new Settings();
                $aws->company_id = $company->id;
                $aws->setting_type = 'storage';
                $aws->name = 'AWS';
                $aws->name_key = 'aws';
                $aws->credentials = [
                    'driver' => 's3',
                    'key' => '',
                    'secret' => '',
                    'region' => '',
                    'bucket' => '',

                ];
                $aws->is_global = $company->is_global;
                $aws->save();
            }

            $smtpEmailSettingCount = Settings::withoutGlobalScope(CompanyScope::class)
                ->where('setting_type', 'email')
                ->where('name_key', 'smtp')
                ->where('is_global', $company->is_global)
                ->where('company_id', $company->id)
                ->count();
            if ($smtpEmailSettingCount == 0) {
                $smtp = new Settings();
                $smtp->company_id = $company->id;
                $smtp->setting_type = 'email';
                $smtp->name = 'SMTP';
                $smtp->name_key = 'smtp';
                $smtp->credentials = [
                    'from_name' => '',
                    'from_email' => '',
                    'host' => '',
                    'port' => '',
                    'encryption' => '',
                    'username' => '',
                    'password' => '',

                ];
                $smtp->is_global = $company->is_global;
                $smtp->save();
            }
        }

        if ($company->is_global == 0) {

            $sendMailSettingCount = Settings::withoutGlobalScope(CompanyScope::class)
                ->where('setting_type', 'send_mail_settings')
                ->where('name_key', 'warehouse')
                ->where('is_global', 0)
                ->where('company_id', $company->id)
                ->count();
            if ($sendMailSettingCount == 0) {
                $sendMailSettings = new Settings();
                $sendMailSettings->company_id = $company->id;
                $sendMailSettings->setting_type = 'send_mail_settings';
                $sendMailSettings->name = 'Send mail to warehouse';
                $sendMailSettings->name_key = 'warehouse';
                $sendMailSettings->credentials = [];
                $sendMailSettings->save();
            }


            $shortcutMenuSettingCount = Settings::withoutGlobalScope(CompanyScope::class)
                ->where('setting_type', 'shortcut_menus')
                ->where('name_key', 'shortcut_menus')
                ->where('is_global', 0)
                ->where('company_id', $company->id)
                ->count();
            if ($shortcutMenuSettingCount == 0) {
                // Create Menu Setting
                $setting = new Settings();
                $setting->company_id = $company->id;
                $setting->setting_type = 'shortcut_menus';
                $setting->name = 'Add Menu';
                $setting->name_key = 'shortcut_menus';
                $setting->credentials = [
                    'staff_member',
                    'customer',
                    'supplier',
                    'brand',
                    'category',
                    'product',
                    'purchase',
                    'sales',
                    'expense_category',
                    'expense',
                    'warehouse',
                    'currency',
                    'unit',
                    'language',
                    'role',
                    'tax',
                    'payment_mode',
                ];
                $setting->status = 1;
                $setting->save();
            }

            // Seed for quotations
            NotificationSeed::seedAllModulesNotifications($company->id);
        }
    }

    public static function assignCompanyForNonSaas($company)
    {
        DB::table('payment_modes')->update(['company_id' => $company->id]);
        DB::table('currencies')->update(['company_id' => $company->id]);
        DB::table('warehouses')->update(['company_id' => $company->id]);
        DB::table('users')->update(['company_id' => $company->id]);
        DB::table('roles')->update(['company_id' => $company->id]);
        DB::table('brands')->update(['company_id' => $company->id]);
        DB::table('categories')->update(['company_id' => $company->id]);
        DB::table('products')->update(['company_id' => $company->id]);
        DB::table('taxes')->update(['company_id' => $company->id]);
        DB::table('units')->update(['company_id' => $company->id]);
        DB::table('expense_categories')->update(['company_id' => $company->id]);
        DB::table('expenses')->update(['company_id' => $company->id]);
        DB::table('custom_fields')->update(['company_id' => $company->id]);
        DB::table('orders')->update(['company_id' => $company->id]);
        DB::table('payments')->update(['company_id' => $company->id]);
        DB::table('order_payments')->update(['company_id' => $company->id]);
        DB::table('warehouse_stocks')->update(['company_id' => $company->id]);
        DB::table('stock_history')->update(['company_id' => $company->id]);
        DB::table('stock_adjustments')->update(['company_id' => $company->id]);
        DB::table('settings')->update(['company_id' => $company->id]);
        DB::table('warehouse_history')->update(['company_id' => $company->id]);
        DB::table('order_shipping_address')->update(['company_id' => $company->id]);
        DB::table('user_address')->update(['company_id' => $company->id]);
        DB::table('front_product_cards')->update(['company_id' => $company->id]);
        DB::table('front_website_settings')->update(['company_id' => $company->id]);
        DB::table('settings')->update(['company_id' => $company->id]);


        $adminUser = User::first();
        $company->admin_id = $adminUser->id;
        // Setting Trial Plan
        if (app_type() == 'multiple') {
            $trialPlan = SubscriptionPlan::where('default', 'trial')->first();
            if ($trialPlan) {
                $company->subscription_plan_id = $trialPlan->id;
                // set company license expire date
                $company->licence_expire_on = Carbon::now()->addDays($trialPlan->duration)->format('Y-m-d');
            }
        }
        $company->save();

        // Insert records in settings table
        // For inital settings like email, storage
        Common::insertInitSettings($company);
    }

    public static function insertInitSettingsForAllCompanies()
    {
        $allCompanies = Company::all();
        foreach ($allCompanies as $company) {
            Common::insertInitSettings($company);
        }
    }

    public static function createCompanyWalkInCustomer($company)
    {
        $isalkinCustomerExists = Customer::withoutGlobalScope(CompanyScope::class)
            ->where('is_walkin_customer', '=', 1)
            ->where('company_id', '=', $company->id)
            ->count();

        if ($isalkinCustomerExists == 0) {
            $customer = new Customer();
            $customer->company_id = $company->id;
            $customer->login_enabled = 0;
            $customer->name = 'Walk In Customer';
            $customer->email = 'walkin@email.com';
            $customer->phone = '+911111111111';
            $customer->user_type = 'customers';
            $customer->address = 'address';
            $customer->shipping_address = 'shipping address';
            $customer->is_walkin_customer = 1;
            $customer->save();

            $allWarehouses = Warehouse::select('id')->withoutGlobalScope(CompanyScope::class)->get();
            foreach ($allWarehouses as $allWarehouse) {
                $userDetails = new UserDetails();
                $userDetails->warehouse_id = $allWarehouse->id;
                $userDetails->user_id = $customer->id;
                $userDetails->credit_period = 30;
                $userDetails->save();
            }
        }

        return $company;
    }

    public static function createAllCompaniesWalkInCustomer()
    {
        $allCompanies = Company::all();
        foreach ($allCompanies as $company) {
            self::createCompanyWalkInCustomer($company);
        }
    }

    public static function getCountryCode()
    {
        $host = $_SERVER['HTTP_HOST'];

        if ($host == "localhost:8000" || $host == "7f44-183-82-179-90.ngrok-free.app") {
            $country_code = "IN";
        } else {
            $country_code = isset($_SERVER["HTTP_CF_IPCOUNTRY"]) ? $_SERVER["HTTP_CF_IPCOUNTRY"] : "IN";
        }

        if ($country_code == "IN") {
            $country_code = "IN";
        } else {
            $country_code = $country_code;
        }

        return $country_code;
    }

    public static function sendWhatsApp_new($phone, $message, $countrycode)
    {
        $url = 'https://wa.yourestore.in/chat/sendmessage';
        $data = [
            'number' => $phone,
            'message' => $message,
            'client' => '7092727092',
            'country_code' => $countrycode,
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            // Handle the cURL error
            return 0;
        } else {
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($statusCode >= 200 && $statusCode < 300) {
                // Request was successful
                return 1;
            } else {
                // Request failed
                return 0;
            }
        }

        curl_close($ch);
    }

    public function getTopProducts()
    {
        $request = request();
        $waehouse = warehouse();
        $warehouseId = $waehouse->id;

        $colors = ["#20C997", "#5F63F2", "#ffa040", "#FFCD56", "#ff6385"];

        $maxSellingProducts = OrderItem::select('order_items.product_id', DB::raw('sum(order_items.subtotal) as total_amount'))
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.order_type', 'sales');

        if ($warehouseId && $warehouseId != null) {
            $maxSellingProducts = $maxSellingProducts->where('orders.warehouse_id', $warehouseId);
        }

        if ($request->has('dates') && $request->dates != null && count($request->dates) > 0) {
            $dates = $request->dates;
            $startDate = $dates[0];
            $endDate = $dates[1];

            $maxSellingProducts = $maxSellingProducts->whereRaw('DATE(orders.order_date) >= ?', [$startDate])
                ->whereRaw('DATE(orders.order_date) <= ?', [$endDate]);
        }

        $maxSellingProducts = $maxSellingProducts->groupBy('order_items.product_id')
            ->orderByRaw("sum(order_items.subtotal) desc")
            ->take(5)
            ->get();

        $topSellingProductsNames = [];
        $topSellingProductsValues = [];
        $topSellingProductsColors = [];
        $counter = 0;
        foreach ($maxSellingProducts as $maxSellingProduct) {
            $product = Product::select('*')->find($maxSellingProduct->product_id);

            $topSellingProductsNames[] = $product->name;
            $topSellingProductsValues[] = $maxSellingProduct->total_amount;
            $topSellingProductsColors[] = $colors[$counter];
            $counter++;
        }

        return [
            'labels' => $topSellingProductsNames,
            'values' => $topSellingProductsValues,
            'colors' => $topSellingProductsColors,
        ];
    }
}
