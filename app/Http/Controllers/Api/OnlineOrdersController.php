<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiBaseController;
use App\Http\Requests\Api\OnlineOrders\IndexRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductDetails;
use App\Models\StockHistory;
use Examyou\RestAPI\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OnlineOrdersController extends ApiBaseController
{
    protected $model = Order::class;

    protected $indexRequest = IndexRequest::class;

    protected function modifyIndex($query)
    {
        $request = request();
        $warehouse = warehouse();

        $query = $query->where('orders.order_type', "online-orders");

        // Dates Filters
        if ($request->has('dates') && $request->dates != "") {
            $dates = explode(',', $request->dates);
            $startDate = $dates[0];
            $endDate = $dates[1];

            $query = $query->whereRaw('orders.order_date >= ?', [$startDate])
                ->whereRaw('orders.order_date <= ?', [$endDate]);
        }

        // Can see only order of warehouses which is assigned to him
        $query = $query->where('orders.warehouse_id', $warehouse->id);

        return $query;
    }

    public function cancelOrder($id)
    {
        $order = Order::where('unique_id', $id)->first();

        if ($order->order_type == "online-orders" && $order->order_status != 'delivered') {
            $order->cancelled = 1;
            $order->cancelled_by = auth('api')->user()->id;

            $orderItems = OrderItem::where('order_id', $order->id)->get();
            foreach ($orderItems as $item) {
                $product = ProductDetails::where('product_id', $item->product_id)->first();
                $product->current_stock = $product->current_stock + $item->quantity;
                $product->save();
                // Tracking Stock History
                $stockHistory = new StockHistory();
                $stockHistory->warehouse_id = $product->warehouse_id;
                $stockHistory->product_id = $item->product_id;
                $stockHistory->quantity = $item->quantity;
                $stockHistory->old_quantity = $product->current_stock - $item->quantity;
                $stockHistory->order_type = $order->order_type;
                $stockHistory->stock_type =  'in';
                $stockHistory->action_type = "add";
                $stockHistory->created_by = $order->user_id;
                $stockHistory->save();
            }

            $order->save();

            return ApiResponse::make('Success', []);
        }
    }

    public function confirmOrder(Request $request, $id)
    {
        $order = Order::where('unique_id', $id)->first();
        // // Log::info([$id, $order]);
        if ($order->order_type == "online-orders" && $order->order_status == 'ordered' && $order->cancelled != 1) {
            $order->order_status = "confirmed";

            // this code commeted on 17th Jan 2024 by kali i have changed to stock when place the order itself
            // $orderItems = OrderItem::where('order_id', $order->id)->get();
            // foreach ($orderItems as $item) {
            //     $product = ProductDetails::where('product_id', $item->product_id)->first();
            //     $product->current_stock = $product->current_stock - $item->quantity;
            //     $product->save();
            //     // Tracking Stock History
            //     $stockHistory = new StockHistory();
            //     $stockHistory->warehouse_id = $product->warehouse_id;
            //     $stockHistory->product_id = $item->product_id;
            //     $stockHistory->quantity = $item->quantity;
            //     $stockHistory->old_quantity = $product->current_stock + $item->quantity;
            //     $stockHistory->order_type = $order->order_type;
            //     $stockHistory->stock_type =  'out';
            //     $stockHistory->action_type = "add";
            //     $stockHistory->created_by = $order->user_id;
            //     $stockHistory->save();
            // }

            // On February 14th, 2024, Saravanan uncommented the line '$order->save();' as the order status was changing to confirmed but not being saved.
            $order->save();

            return ApiResponse::make('Success', []);
        }
    }

    public function changeOrderStatus(Request $request, $id)
    {
        $order = Order::where('unique_id', $id)->first();

        if (
            $order->order_type == "online-orders" &&
            $order->order_status != 'ordered' &&
            $order->order_status != 'delivered' &&
            $order->cancelled != 1 &&
            ($request->order_status == 'confirmed' || $request->order_status == 'processing' || $request->order_status == 'shipping')
        ) {
            $order->order_status = $request->order_status;
            $order->save();

            return ApiResponse::make('Success', []);
        }
    }

    public function markAsDelivered(Request $request, $id)
    {
        $order = Order::where('unique_id', $id)->first();

        if (
            $order->order_type == "online-orders" &&
            $order->cancelled != 1 &&
            ($order->order_status == 'confirmed' || $order->order_status == 'processing' || $order->order_status == 'shipping')
        ) {
            $order->order_status = "delivered";
            $order->save();

            return ApiResponse::make('Success', []);
        }
    }
}
