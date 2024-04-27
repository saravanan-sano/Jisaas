<?php

namespace App\Traits;

use App\Classes\Common;
use App\Classes\Notify;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPayment;
use App\Models\OrderShippingAddress;
use App\Models\Product;
use App\Models\ProductDetails;
use App\Models\StaffMember;
use App\Models\StockHistory;
use App\Models\Unit;
use App\Models\User;
use App\Models\WarehouseStock;
use App\Models\Warehouse;
use App\Models\Wholesale;
use Examyou\RestAPI\ApiResponse;
use Examyou\RestAPI\Exceptions\ApiException;
use Examyou\RestAPI\Exceptions\ResourceNotFoundException;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Nette\Utils\Json;

trait OrderTraits
{
    public $orderType = "";

    protected function modifyIndex($query)
    {
        $request = request();
        //  // Log::info('$view_all_customer' . $request);
        $warehouse = warehouse();
        $user = user();
        // kali added or condition for online orders
        // $query = $query->where('orders.order_type', $this->orderType);
        if ($this->orderType == "sales") {
            $query = $query->where(function ($query) {
                $query->where('orders.order_type', $this->orderType)
                    ->orWhere('orders.order_type', 'online-orders');
            });
        } else {
            $query = $query->where('orders.order_type', $this->orderType);
        }



        // Dates Filters
        if ($request->has('dates') && $request->dates != "") {
            $dates = explode(',', $request->dates);
            $startDate = $dates[0];
            $endDate = $dates[1];

            $query = $query->whereRaw('orders.order_date >= ?', [$startDate])
                ->whereRaw('orders.order_date <= ?', [$endDate]);
        }


        if ($request->view_all_customer == 'false') {
            $query = $query->where('orders.staff_user_id', $user->id);
        }

        // Additional filter with order_items table
        if ($request->identity_code != "undefined") {
            if ($request->filled('identity_code')) {
                $query = $query->rightJoin('order_items', function ($join) use ($request) {
                    $join->on('orders.id', '=', 'order_items.order_id')
                        ->where('order_items.identity_code', 'like',  '%' . $request->identity_code . '%');
                });
            }
        }

        // Can see only order of warehouses which is assigned to him
        if ($this->orderType == 'stock-transfers') {
            if ($request->transfer_type == 'transfered') {
                $query = $query->where('orders.from_warehouse_id', $warehouse->id);
            } else {
                $query = $query->where('orders.warehouse_id', $warehouse->id);
            }
        } else {
            $query = $query->where('orders.warehouse_id', $warehouse->id);
        }

        // $query = 'select * from `orders` where `orders`.`order_type` = ? and `orders`.`warehouse_id` = ? and `orders`.`company_id` = ? order by `order_date` desc limit 10 offset 0';


        //     $query = $query->leftJoin('order_items', 'order_items.identity_code', 'LIKE', '%' . $request->identity_code . '%' );

        return $query;
    }

    public function show(...$args)
    {
        $xid = last(func_get_args());
        $id = Common::getIdFromHash($xid);

        $orderDetails = Order::find($id);
        $orderShippingAddress = OrderShippingAddress::where('order_id', $id)->first();
        // $orderDetails = Order::with('orderPayments')->find($id);
        // $orderDetails = Order::with('orderPayments1.payment1.paymentMode')->find($id);
        //   $orderDetails = Order::with('user2', 'orderPayments.payment.paymentMode')->find($id);



        $orderType = $orderDetails->order_type;
        $allProducs = [];
        $selectProductIds = [];
        $sn = 1;

        $allOrderIteams = OrderItem::with('product')->where('order_id', $id)->get();
        foreach ($allOrderIteams as $allOrderIteam) {
            $productDetails = ProductDetails::withoutGlobalScope('current_warehouse')
                ->where('warehouse_id', '=', $orderDetails->warehouse_id)
                ->where('product_id', '=', $allOrderIteam->product_id)
                ->first();
            $producthsncode = Product::where('id', $allOrderIteam->product_id)->first();
            $maxQuantity = $productDetails->current_stock;
            $unit = $allOrderIteam->unit_id != null ? Unit::find($allOrderIteam->unit_id) : null;
            $wholesale = Wholesale::where('product_id', $allOrderIteam->product_id)->get();

            if ($orderType == 'purchase-returns' || $orderType == 'sales') {
                $maxQuantity = $allOrderIteam->quantity + $maxQuantity;
            }

            $allProducs[] = [
                'sn' => $sn,
                'xid' => $allOrderIteam->x_product_id,
                'item_id' => $allOrderIteam->xid,
                'name' => $allOrderIteam->product->name,
                'image' => $allOrderIteam->product->image,
                'image_url' => $allOrderIteam->product->image_url,
                'x_tax_id' => $allOrderIteam->x_tax_id,
                'discount_rate' => $allOrderIteam->discount_rate,
                'total_discount' => $allOrderIteam->total_discount,
                'total_tax' => $allOrderIteam->total_tax,
                'unit_price' => $allOrderIteam->unit_price,
                'mrp' => $allOrderIteam->mrp,
                'single_unit_price' => $allOrderIteam->single_unit_price,
                'subtotal' => $allOrderIteam->subtotal,
                'quantity' => $allOrderIteam->quantity,
                'tax_rate' => $allOrderIteam->tax_rate,
                'tax_type' => $allOrderIteam->tax_type,
                'x_unit_id' => $allOrderIteam->x_unit_id,
                'unit' => $unit,
                "identity_code" => $allOrderIteam->identity_code,
                "discount_type" => $allOrderIteam->discount_type,
                "discount" => $allOrderIteam->discount,
                'stock_quantity' => $maxQuantity,
                'unit_short_name' => $unit->short_name,
                'hsn_sac_code' => $producthsncode->hsn_sac_code,
                'wholesale' => $wholesale
            ];

            $userid = Common::getIdFromHash($allOrderIteam->x_user_id);


            $selectProductIds[] = $allOrderIteam->x_product_id;
            $sn++;
        }

        // // Log::info($orderDetails->staff_user_id);
        $staffid = Common::getIdFromHash($orderDetails->staff_user_id);

        $userdetails = User::where('id', $userid)->first();
        $staffdetails = User::where('id', $staffid)->first();
        // // Log::info($staffdetails);
        $orderDetails['x_staff_id'] = Common::getHashFromId($orderDetails->staff_id);
        $orderDetails['x_referral_id'] = Common::getHashFromId($orderDetails->referral_id);


        return ApiResponse::make('Data fetched', [
            'orderShippingAddress' => $orderShippingAddress,
            'order' => $orderDetails,
            'items' => $allProducs,
            'ids' => $selectProductIds,
            'user' => $userdetails,
            'staff_member' => $staffdetails
        ]);
    }

    public function storing(Order $order)
    {
        $request = request();
        $warehouse = warehouse();
        $company = company();
        // [$refferalId] = Hashids::decode($request->referral_id);

        $timezone = $company->timezone;
        $warehouse_latest = Warehouse::where(['id' => $warehouse->id])->orderBy('id', 'desc')->first();
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
        // // Log::info("orderType :" . $request->order_type);
        if ($lastorder) {
            if ($request->order_type == "sales") {
                if ($warehouse_latest->invoice_started == 1) {

                    $lastorder = $lastorder->invoice_number;
                    $lastorder = explode($invoice_spliter, $lastorder);
                    if ($lastorder[1] == "undefined") {
                        $lastorder = $lastorder[0];
                    } else {
                        $lastorder = $lastorder[1];
                    }
                } else {
                    $lastorder = $warehouse_latest->first_invoice_no + 1;
                }
                // if (!$request->has('invoice_number') || ($request->has('invoice_number') && $request->invoice_number == "")) {
                //     $order->invoice_number = $lastorder;
                // }
                $warehouse_latest = Warehouse::where(['id' => $warehouse->id])->orderBy('id', 'desc')->first();
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
                        $lastorder = Order::where(['warehouse_id' => $warehouse->id])->orderBy('id', 'desc')->first();
                        if ($warehouse_latest->invoice_started == 1) {

                            $lastorder = $lastorder->invoice_number;
                            $lastorder = explode($invoice_spliter, $lastorder);
                            $lastorder = $lastorder[1] + 1;
                        } else {
                            $lastorder = $warehouse_latest->first_invoice_no + 1;
                        }
                    }
                } else {
                    $invoice_prefix = $warehouse_latest->prefix_invoice;
                    $invoice_suffix = $warehouse_latest->suffix_invoice;
                    $invoice_spliter = $warehouse_latest->invoice_spliter;

                    // $lastorder=Order::where(['warehouse_id' => $warehouse->id]) -> orderBy('id', 'desc')->first();
                    $lastorder = Order::where(['warehouse_id' => $warehouse->id])->where('order_type', '!=', 'online-orders')->where('order_type', $request->order_type)->orderBy('id', 'desc')->first();

                    if ($warehouse_latest->invoice_started == 1) {

                        $lastorder = $lastorder->invoice_number;

                        $lastorder = explode($invoice_spliter, $lastorder);
                        $lastorder = $lastorder[1] + 1;
                    } else {
                        $lastorder = $warehouse_latest->first_invoice_no + 1;
                    }
                }
                $currentinvoice = $lastorder;
                $formattedNumber = substr("0000" . $currentinvoice, -5);
                if ($order->order_type == '') {
                    $ordertype = $request->order_type;
                } else {
                    $ordertype = $order->order_type;
                }

                $order->invoice_number = Common::getTransactionNumberNew($ordertype, $formattedNumber, $invoice_prefix, $invoice_suffix, $invoice_spliter);
            } else {
                $lastfilterorder = Order::where(['warehouse_id' => $warehouse->id])->where('order_type', $request->order_type)->where('is_auto_invoice', 1)->orderBy('id', 'desc')->first();

                if ($warehouse_latest->invoice_started == 0) {
                    $invoice_prefix = $warehouse_latest->prefix_invoice;
                    $invoice_suffix = $warehouse_latest->suffix_invoice;
                    $invoice_spliter = $warehouse_latest->invoice_spliter;
                    $currentinvoice = $warehouse_latest->first_invoice_no + 1;
                    $formattedNumber = substr("0000" . $currentinvoice, -5);
                    $order->invoice_number = Common::getTransactionNumberNew($order->order_type, $formattedNumber, $invoice_prefix, $invoice_suffix, $invoice_spliter);
                } else if ($request->invoice_number == "") {
                    $invoice_prefix = $warehouse_latest->prefix_invoice;
                    $invoice_suffix = $warehouse_latest->suffix_invoice;
                    $invoice_spliter = $warehouse_latest->invoice_spliter;
                    if ($lastfilterorder) {
                        $lastfilterorder = $lastfilterorder->invoice_number;
                        $lastfilterorder = explode($invoice_spliter, $lastfilterorder);

                        try {
                            $lastfilterorder = $lastfilterorder[1] + 1;
                        } catch (\Throwable $th) {
                            $lastfilterorder = 1;
                        }

                        $formattedNumber = substr("0000" . $lastfilterorder, -5);
                        $order->invoice_number = Common::getTransactionNumberNew($this->orderType, $formattedNumber, $invoice_prefix, $invoice_suffix, $invoice_spliter);
                    } else {

                        $formattedNumber = substr("0000" . 1, -5);
                        $order->invoice_number = Common::getTransactionNumberNew($this->orderType, $formattedNumber, $invoice_prefix, $invoice_suffix, $invoice_spliter);
                    }
                } else if ($request->invoice_number != "") {
                    $order->invoice_number = $request->invoice_number;
                    $order->is_auto_invoice = 0;
                }
            }
        } else {
            if ($request->invoice_number != "") {
                $order->invoice_number = $request->invoice_number;
                $order->is_auto_invoice = 0;
            } else if ($request->invoice_number == "") {
                $invoice_prefix = $warehouse_latest->prefix_invoice;
                $invoice_suffix = $warehouse_latest->suffix_invoice;
                $invoice_spliter = $warehouse_latest->invoice_spliter;
                $formattedNumber = substr("0000" . 1, -5);
                $order->invoice_number = Common::getTransactionNumberNew($this->orderType, $formattedNumber, $invoice_prefix, $invoice_suffix, $invoice_spliter);
            }
        }

        $order->unique_id = Common::generateOrderUniqueId();
        $order->order_type = $this->orderType;
        $order->invoice_type = $this->orderType;
        $order->warehouse_id = $this->orderType == 'stock-transfers' ? $request->warehouse_id : $warehouse->id;
        $order->from_warehouse_id = $this->orderType == 'stock-transfers' ? $warehouse->id : null;
        $order->user_id = $this->orderType == 'stock-transfers' ? null : $request->user_id;

        if ($this->orderType == "quotations") {
            $order->order_status = "pending";
        }

        return $order;
    }

    public function stored(Order $order)
    {

        $oldOrderId = "";

        $warehouse = warehouse();
        $company = company();
        $timezone = $company->timezone;


        // $warehouse_latest=Warehouse::where(['id' => $warehouse->id]) -> orderBy('id', 'desc')->first();
        // $invoice_prefix=$warehouse_latest->prefix_invoice;
        // $invoice_suffix=$warehouse_latest->suffix_invoice;
        // $invoice_spliter=$warehouse_latest->invoice_spliter;
        // $currentinvoice=$order->invoice_number+1;

        // if($warehouse_latest->invoice_started==0)
        // {
        //     $currentinvoice=$warehouse_latest->first_invoice_no+1;
        //     $formattedNumber = substr("0000" . $currentinvoice, -5);
        //     $order->invoice_number = Common::getTransactionNumberNew($order->order_type, $formattedNumber,$invoice_prefix,$invoice_suffix,$invoice_spliter);
        // }
        // else
        // {
        //     $formattedNumber = substr("0000" . $currentinvoice, -5);
        //     $order->invoice_number = Common::getTransactionNumberNew($order->order_type, $formattedNumber,$invoice_prefix,$invoice_suffix,$invoice_spliter);
        // }

        $order->order_date_local = Carbon::now()->tz($timezone);

        // if ($order->invoice_number == '') {
        // $order->invoice_number = Common::getTransactionNumberNew($order->order_type, $currentinvoice,$invoice_prefix);
        // }


        if ($order->referral_id) {
            [$refferalId] = Hashids::decode($order->referral_id);
            $order->referral_id = $refferalId;
        }
        if ($order->staff_id) {
            [$staffId] = Hashids::decode($order->staff_id);
            $order->staff_id = $staffId;
        }


        // Created by user
        $order->staff_user_id = auth('api')->user()->id;
        $order->save();

        $order = Common::storeAndUpdateOrder($order, $oldOrderId);

        // Updating Warehouse History
        Common::updateWarehouseHistory('order', $order, "add_edit");

        // Notifying to Warehouse
        Notify::send(str_replace('-', '_', $order->order_type) . '_create', $order);

        DB::table('warehouses')
            ->where('id', $warehouse->id)
            ->update(['invoice_started' => 1]);

        return $order;
    }

    public function updating(Order $order)
    {
        $loggedUser = user();
        $warehouse = warehouse();

        // If logged in user is not admin
        // then cannot update order who are
        // of other warehouse
        if (!$loggedUser->hasRole('admin') && $order->warehouse_id != $warehouse->id) {
            throw new ApiException("Don't have valid permission");
        }

        $order->order_type = $this->orderType;

        return $order;
    }

    public function update(...$args)
    {
        \DB::beginTransaction();

        // Geting id from hashids
        $xid = last(func_get_args());
        $convertedId = Hashids::decode($xid);
        $id = $convertedId[0];

        $this->validate();

        // Get object for update
        $this->query = call_user_func($this->model . "::query");

        /** @var ApiModel $object */
        $object = $this->query->find($id);

        if (!$object) {
            throw new ResourceNotFoundException();
        }

        $oldUserId = $object->user_id;

        $orderPaymentCount = OrderPayment::where('order_id', $id)->count();
        $request = request();

        if ($orderPaymentCount > 0) {
            $object->order_status = $request->order_status;
        } else {
            $object->fill(request()->all());
        }
        if(isset($request->order_type)){
            Log::info(["Order type", $object]);
            $object->order_type = $request->order_type;
        }

        if (method_exists($this, 'updating')) {
            $object = call_user_func([$this, 'updating'], $object);
        }

        $object->save();

        $meta = $this->getMetaData(true);

        \DB::commit();

        if (method_exists($this, 'updated')) {
            call_user_func([$this, 'updated'], $object);
        }

        // If user changed then
        // Update his order_count & order_return_count
        if ($oldUserId != $object->user_id) {
            Common::updateUserAmount($oldUserId, $object->warehouse_id);
        }

        // Updating Warehouse History
        Common::updateWarehouseHistory('order', $object, "add_edit");

        return ApiResponse::make("Resource updated successfully", ["xid" => $object->xid], $meta);
    }

    public function updated(Order $order)
    {
        $oldOrderId = $order->id;
        Common::storeAndUpdateOrder($order, $oldOrderId);

        // Notifying to Warehouse
        Notify::send(str_replace('-', '_', $order->order_type) . '_update', $order);
    }

    public function destroy(...$args)
    {
        \DB::beginTransaction();

        // Geting id from hashids
        $xid = last(func_get_args());
        $convertedId = Hashids::decode($xid);
        $id = $convertedId[0];

        $this->validate();

        // Get object for update
        $this->query = call_user_func($this->model . "::query");

        /** @var Model $object */
        $object = $this->query->find($id);

        if (!$object) {
            throw new ResourceNotFoundException();
        }

        if (method_exists($this, 'destroying')) {
            $object = call_user_func([$this, 'destroying'], $object);
        }

        $order = $object;
        $loggedUser = user();
        $orderItems = $order->items;
        $orderType = $order->order_type;
        $warehouseId = $order->warehouse_id;
        $fromWarehouseId = $order->from_warehouse_id;
        $orderUserId = $order->user_id;

        // If logged in user is not admin
        // then cannot delete order who are
        // of other warehouse
        if (!$loggedUser->hasRole('admin') && $order->warehouse_id != $loggedUser->warehouse_id) {
            throw new ApiException("Don't have valid permission");
        }

        foreach ($orderItems as $orderItem) {

            $stockHistory = new StockHistory();
            $stockHistory->warehouse_id = $warehouseId;
            $stockHistory->product_id = $orderItem->x_product_id;
            $stockHistory->quantity = $orderItem->quantity;
            $stockHistory->old_quantity = $orderItem->quantity;
            $stockHistory->order_type = $orderType;
            $stockHistory->stock_type = $orderType == 'sales' || $orderType == 'purchase-returns' ? 'in' : 'out';
            $stockHistory->action_type = "delete";
            $stockHistory->created_by = $loggedUser->x_id;
            $stockHistory->save();
        }

        // Notifying to Warehouse
        Notify::send(str_replace('-', '_', $object->order_type) . '_delete', $object);

        $object->delete();

        foreach ($orderItems as $orderItem) {
            $productId = $orderItem->product_id;

            // Update warehouse stock for product
            Common::recalculateOrderStock($warehouseId, $productId);

            if ($orderType == "stock-transfers") {
                Common::recalculateOrderStock($fromWarehouseId, $productId);
            }
        }

        // Update Customer or Supplier total amount, due amount, paid amount
        Common::updateUserAmount($orderUserId, $order->warehouse_id);

        // Updating Warehouse History
        Common::updateWarehouseHistory('order', $order);

        $meta = $this->getMetaData(true);

        \DB::commit();

        return ApiResponse::make("Resource deleted successfully", null, $meta);
    }
};
