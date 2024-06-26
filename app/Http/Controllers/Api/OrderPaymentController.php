<?php

namespace App\Http\Controllers\Api;

use App\Classes\Common;
use App\Http\Controllers\ApiBaseController;
use App\Http\Requests\Api\OrderPayment\IndexRequest;
use App\Http\Requests\Api\OrderPayment\StoreRequest;
use App\Http\Requests\Api\OrderPayment\UpdateRequest;
use App\Http\Requests\Api\OrderPayment\DeleteRequest;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\Payment;
use App\Models\Warehouse;
use Examyou\RestAPI\ApiResponse;
use Examyou\RestAPI\Exceptions\ApiException;
use Examyou\RestAPI\Exceptions\UnauthorizedException;

class OrderPaymentController extends ApiBaseController
{
    protected $model = OrderPayment::class;

    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    protected function modifyIndex($query)
    {
        $user = auth('api')->user();
        $request = request();
        $warehouse = warehouse();

        $query = $query->join('orders', 'orders.id',  '=', 'order_payments.order_id');

        // If user not have admin role
        // then he can only view reords
        // of warehouse assigned to him
        $query = $query->where('orders.warehouse_id', $warehouse->id);

        // Order Type Filter
        if ($request->has('order_type') && $request->order_type != 'all') {
            if ($user->ability('admin', 'purchases_view') && $request->order_type == 'purchases') {
                $query = $query->where('orders.order_type', '=', $request->order_type);
            } else if ($user->ability('admin', 'purchase_returns_view') && $request->order_type == 'purchase-returns') {
                $query = $query->where('orders.order_type', '=', $request->order_type);
            } else if ($user->ability('admin', 'sales_view') && $request->order_type == 'sales') {
                $query = $query->where('orders.order_type', '=', $request->order_type);
            } else if ($user->ability('admin', 'sales_returns_view') && $request->order_type == 'sales-returns') {
                $query = $query->where('orders.order_type', '=', $request->order_type);
            } else {
                throw new UnauthorizedException("Don't have valid permission");
            }
        } else {
            if (
                !($user->ability('admin', 'purchases_view') ||
                    $user->ability('admin', 'purchase_returns_view') ||
                    $user->ability('admin', 'sales_view') ||
                    $user->ability('admin', 'sales_returns_view'))
            ) {
                throw new UnauthorizedException("Don't have valid permission");
            } else {
                $query = $query->where(function ($queryData) use ($user) {
                    if ($user->ability('admin', 'purchases_view')) {
                        $queryData->orWhere('orders.order_type', 'purchases');
                    }

                    if ($user->ability('admin', 'purchase_returns_view')) {
                        $queryData->orWhere('orders.order_type', 'purchase-returns');
                    }
                    if ($user->ability('admin', 'sales_view')) {
                        $queryData->orWhere('orders.order_type', 'sales');
                    }
                    if ($user->ability('admin', 'sales_returns_view')) {
                        $queryData->orWhere('orders.order_type', 'sales-returns');
                    }
                });
            }
        }

        // Dates Filters
        if ($request->has('dates') && $request->dates != "") {
            $dates = explode(',', $request->dates);
            $startDate = $dates[0];
            $endDate = $dates[1];

            $query = $query->whereRaw('DATE(order_payments.date) >= ?', [$startDate])
                ->whereRaw('DATE(order_payments.date) <= ?', [$endDate]);
        }

        return $query;
    }

    public function storing(OrderPayment $orderPayment)
    {

        $request = request();
        $warehouse = warehouse();
        $warehouseId = $warehouse->id;
        $warehouse = Warehouse::where('id', $warehouseId)->first();
        $loggedInUser = user();
        $this->notValidPermission($orderPayment);


        $order = Order::find($orderPayment->order_id);

        if ($order->order_type == 'purchases' || $order->order_type == 'sales-returns') {
            $paymentType = "out";
        } else {
            $paymentType = "in";
        }

        $lastPayment = Payment::where('warehouse_id', $warehouse->id)->where('payment_type', $paymentType)->orderBy('id', 'DESC')->first();

        $newPayment = new Payment();
        $newPayment->warehouse_id = $order->order_type == 'stock-transfers' ? $order->from_warehouse_id : $order->warehouse_id;
        $newPayment->payment_type = $paymentType;
        $newPayment->date = $request->date;
        $newPayment->amount = $request->amount;
        $newPayment->unused_amount = 0;
        $newPayment->paid_amount = $request->amount;
        $newPayment->payment_mode_id = $request->payment_mode_id;
        $newPayment->user_id = $order->user_id;
        $newPayment->notes = $request->notes;
        $newPayment->staff_user_id = $loggedInUser->id;
        $newPayment->save();

        // Saving Payment Number
        $newPayment->payment_number = Common::getTransactionNumberNew('payment-' . $paymentType, $lastPayment->invoice_id + 1, "", $warehouse->suffix_invoice, $warehouse->invoice_spliter);
        $newPayment->invoice_id = $lastPayment->invoice_id + 1;

        // $newPayment->payment_number = Common::getTransactionNumber('payment-' . $paymentType, $newPayment->id);
        $newPayment->save();

        $newOrderPayment = new OrderPayment();
        $newOrderPayment->payment_id = $newPayment->id;
        $newOrderPayment->order_id = $order->id;
        $newOrderPayment->amount = $newPayment->amount;

        return $newOrderPayment;
    }

    public function stored(OrderPayment $orderPayment)
    {
        $payment = Payment::where('id', $orderPayment->payment_id)->first();

        // Updating Warehouse History
        Common::updateWarehouseHistory('payment', $payment, "add_edit");

        return Common::updateOrderAmount($orderPayment->order_id);
    }

    public function notValidPermission($orderPayment)
    {
        $user = user();
        $warehouse = warehouse();

        $order = Order::select('warehouse_id')->find($orderPayment->order_id);
        if (
            $order && $warehouse->id != $order->warehouse_id &&
            (($user->ability('admin', 'payment_in_create') &&
                ($user->ability('admin', 'sales_view') || $user->ability('admin', 'sales_returns_view')) &&
                ($order->order_type == 'sales' ||
                    $order->order_type == 'sales-returns')) ||
                ($user->ability('admin', 'payment_out_create') &&
                    ($user->ability('admin', 'purchases_view') || $user->ability('admin', 'purchase_returns_view')) &&
                    ($order->order_type == 'purchases' ||
                        $order->order_type == 'purchase-returns')))
        ) {
            throw new ApiException("Don't have valid permission");
        }
    }
    public function getOrderByOrderPayment(){
        $request=request();
        $orderPayment = OrderPayment::where("payment_id",Common::getIdFromHash($request->payment_id))->get();

        foreach ($orderPayment as $key => $order) {
            $order->order= Order::where("id", $order->order_id)->first();
        }

        return ApiResponse::make('Data fetched successfully', ["order" => $orderPayment]);
    }

}
