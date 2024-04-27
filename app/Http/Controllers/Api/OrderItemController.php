<?php

namespace App\Http\Controllers\Api;

use App\Classes\Common;
use App\Http\Controllers\ApiBaseController;
use App\Http\Requests\Api\OrderItem\IndexRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Examyou\RestAPI\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderItemController extends ApiBaseController
{
    protected $model = OrderItem::class;

    protected $indexRequest = IndexRequest::class;

    public function modifyIndex($query)
    {
        $request = request();
        $warehouse = warehouse();
        $query = $query->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.warehouse_id', $warehouse->id);

        // Dates Filters
        if ($request->has('dates') && $request->dates != "") {
            $dates = explode(',', $request->dates);
            $startDate = $dates[0];
            $endDate = $dates[1];

            $query = $query->whereRaw('orders.order_date >= ?', [$startDate])
                ->whereRaw('orders.order_date <= ?', [$endDate]);
        }

        if ($request->has('product_sales_summary') && $request->product_sales_summary) {
            $this->modifySelect = true;

            // Old
            // $query = $query->join('products', 'products.id', '=', 'order_items.product_id')
                // ->where('orders.order_type', 'sales')
                // ->groupBy('order_items.product_id')
                // ->selectRaw("order_items.product_id, products.name, products.item_code, sum(order_items.quantity) as unit_sold")
                // ->with('product:id,name,image,unit_id', 'product.unit:id,name,short_name');

            // New
            $query = $query->join('products', 'products.id', '=', 'order_items.product_id')
                ->where('orders.order_type', 'sales')
                ->groupBy('order_items.product_id', 'products.name', 'products.item_code')
                ->selectRaw("order_items.product_id, products.name, products.item_code, sum(order_items.quantity) as unit_sold")
                ->with('product:id,name,image,unit_id', 'product.unit:id,name,short_name');
        }

        return $query;
    }

    public function ExcelImportOrder(Request $request)
    {

        $warehouse = warehouse();
        $orderData = $request->all();
        $user = user();

        $error_data = [];

        foreach ($orderData as $order) {
            try {
                $is_order_exist = order::where('invoice_number', $order['invoice_number'])->count();

                $customer = User::where('name', $order['customer_name'])->where('warehouse_id', $warehouse->id)->first();
                if (!$is_order_exist) {
                    $newOrder = new Order();
                    $newOrder->invoice_number = $order["invoice_number"];
                    $newOrder->invoice_type = "import";
                    $newOrder->order_date = $order["order_date"];
                    $newOrder->order_date_local = $order["order_date"];
                    $newOrder->warehouse_id = $warehouse->id;
                    $newOrder->user_id = $customer->id;

                    $newOrder->referral_id = null;
                    $newOrder->staff_id = $user->id;
                    $newOrder->tax_id = null;
                    $newOrder->tax_rate = $order["tax_rate"];
                    $newOrder->tax_amount = $order["tax_amount"];
                    $newOrder->discount = $order["discount"];
                    $newOrder->shipping = $order["shipping"];
                    $newOrder->subtotal = $order["subtotal"];
                    $newOrder->total = $order["total"];
                    $newOrder->paid_amount = $order["paid_amount"];
                    $newOrder->due_amount = $order["due_amount"];
                    $newOrder->order_status = $order["order_status"];
                    $newOrder->shipping_type = $order["shipping_type"];
                    $newOrder->notes = $order["notes"];
                    $newOrder->document = null;
                    $newOrder->staff_user_id = $user->id;
                    $newOrder->payment_status = $order["payment_status"];
                    $newOrder->total_items = $order["total_items"];
                    $newOrder->total_quantity = $order["total_quantity"];
                    $newOrder->terms_condition = $order["terms_condition"];
                    $newOrder->delivery_to = null;
                    $newOrder->place_of_supply = null;
                    $newOrder->reverse_charge = null;
                    $newOrder->gr_rr_no = $order["gr_rr_no"];
                    $newOrder->transport = null;
                    $newOrder->vechile_no = null;
                    $newOrder->station = null;
                    $newOrder->buyer_order_no = null;
                    $newOrder->cancelled_by = null;
                    $newOrder->created_at = $order["created_at"];
                    $newOrder->updated_at = $order["updated_at"];
                    $newOrder->unique_id = Common::generateOrderUniqueId();
                    $newOrder->save();

                    foreach ($order['product'] as $key => $item) {

                        $orderItem = new OrderItem();
                        $product = Product::where('name', $item['name'])->first();
                        $orderItem->user_id = $customer->id;
                        $orderItem->order_id = $newOrder->id;
                        $orderItem->product_id = $product->id;
                        $orderItem->unit_id = $product->unit_id;
                        $orderItem->quantity = $item['quantity'];
                        $orderItem->return_quantity = $item['quantity'];
                        $orderItem->mrp = $item['mrp'];
                        $orderItem->unit_price = $item['unit_price'];
                        $orderItem->purchase_price = $item['purchase_price'];
                        $orderItem->single_unit_price = $item['single_unit_price'];
                        $orderItem->tax_id = null;
                        $orderItem->tax_rate = $item['tax_rate'];
                        $orderItem->tax_type = "exclusive";
                        $orderItem->discount_rate = "0.00";
                        $orderItem->total_tax = $item['total_tax'];
                        $orderItem->total_discount = "0";
                        $orderItem->subtotal = $item['subtotal'];
                        $orderItem->created_at = $item['created_at'];
                        $orderItem->updated_at = $item['updated_at'];
                        $orderItem->identity_code = null;
                        $orderItem->save();
                    }
                } else {
                    $error_data[] = ['invoice_number' => $order["invoice_number"], 'status' => 'Invoice Number Already Exists'];
                }
            } catch (\Throwable $th) {
                $error_data[] = ['invoice_number' => $order["invoice_number"], 'status' => $th->getMessage()];
            }
        }
        return ApiResponse::make('Data imported successfully and error data are fallback', ["err_data" => $error_data]);
    }
}
