<?php

namespace App\Http\Controllers\Api\Front;

use App\Classes\Common;
use App\Http\Controllers\ApiBaseController;
use App\Http\Requests\Api\Front\Dashboard\UserOrderRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderShippingAddress;
use App\Models\Product;
use App\Models\ProductDetails;
use App\Models\StockHistory;
use App\Models\UserAddress;
use App\Models\Warehouse;
use App\Models\Company;
use Carbon\Carbon;
use Examyou\RestAPI\ApiResponse;
use Examyou\RestAPI\Exceptions\ApiException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\SuperAdmin\Notifications\Front\NewOrder;
use App\SuperAdmin\Models\GlobalCompany;
use Illuminate\Support\Facades\Log;

class DashboardController extends ApiBaseController
{
    public function dashboard(Request $request)
    {
        $user = auth('api_front')->user();
        $totalOrder = Order::where('order_type', '=', 'online-orders')
            ->where('user_id', '=', $user->id)
            ->count();

        $pendingOrder = Order::where('order_type', '=', 'online-orders')
            ->where('order_status', '=', 'ordered')
            ->where('cancelled', '!=', 1)
            ->where('user_id', '=', $user->id)
            ->count();

        $processingOrder = Order::where('order_type', '=', 'online-orders')
            ->where('order_status', '=', 'processing')
            ->where('cancelled', '!=', 1)
            ->where('user_id', '=', $user->id)
            ->count();

        $completedOrders = Order::where('order_type', '=', 'online-orders')
            ->where('order_status', '=', 'delivered')
            ->where('cancelled', '!=', 1)
            ->where('user_id', '=', $user->id)
            ->count();

        $recentOrders = Order::with(['user:id,name,email,address', 'warehouse:id,name,address', 'items', 'items.product:id,name', 'shippingAddress'])
            ->where('order_type', '=', 'online-orders')
            ->where('user_id', '=', $user->id)
            ->take(10)
            ->get();

        return ApiResponse::make('Data fetched', [
            'totalOrders' => $totalOrder,
            'pendingOrders' => $pendingOrder,
            'processingOrders' => $processingOrder,
            'completedOrders' => $completedOrders,
            'recentOrders' => $recentOrders,
        ]);
    }

    public function orders(UserOrderRequest $request)
    {
        $user = auth('api_front')->user();

        $orders = Order::with(['user:id,name,email,address', 'warehouse:id,name,address', 'items', 'items.product:id,name,image', 'shippingAddress']);


        // Order Status Filter
        if ($request->has('order_status_type') && $request->order_status_type != 'all') {
            if ($request->order_status_type == 'pending') {
                $orders = $orders->where(function ($query) {
                    return $query->where('payment_status', '=', 'pending')
                        ->orWhere('payment_status', '=', 'partially_paid')
                        ->orWhere('payment_status', '=', 'unpaid');
                })
                    ->where('cancelled', '!=', 1);
            } else if ($request->order_status_type == 'paid') {
                $orders = $orders->where('payment_status', '=', 'paid')
                    ->where('cancelled', '!=', 1);
            } else if ($request->order_status_type == 'cancelled') {
                $orders = $orders->where('cancelled', '=', 1);
            }
        }

        // Date Filter
        if ($request->has('dates') && $request->dates != null && count($request->dates) > 1) {
            $dates = $request->dates;

            $orders = $orders->whereBetween(DB::raw('DATE(order_date)'), $dates);
        }

        $orders = $orders->where(function ($query) use ($user) {
            $query->where('order_type', '=', 'online-orders')
                ->where('user_id', '=', $user->id);
        })
            ->orWhere(function ($query) use ($user) {
                $query->where('order_type', '=', 'sales')
                    ->where('user_id', '=', $user->id);
            })
            ->orderBy('order_date', 'desc')
            ->get();



        return ApiResponse::make('Data fetched', [
            'orders' => $orders,
        ]);
    }

    public function cancelOrder($orderUniqueId)
    {
        $user = auth('api_front')->user();

        //  $order = Order::where('unique_id', $orderUniqueId)->first();

        // if ($order && $order->user_id == $user->id && $order->order_type == 'online-orders' && $order->order_status == 'ordered') {
        //     $order->cancelled = 1;
        //     $order->cancelled_by = $user->id;
        //     $order->save();

        //     return ApiResponse::make('Data fetched');
        // }

        $order = Order::where('unique_id', $orderUniqueId)->first();

        if ($order && $order->user_id == $user->id && $order->order_type == "online-orders" && $order->order_status != 'delivered' && $order->order_status == 'ordered') {
            $order->cancelled = 1;
            $order->cancelled_by = $user->id;

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
            return ApiResponse::make('Data fetched');
        }
        // TODO - Error Response
    }

    public function checkoutOrders(Request $request)
    {
        $user = auth('api_front')->user();
        if ($user->status === 'disabled') {
            return response([
                'message' => 'You are disabled by admin. Please contact Administrator',
                "is_active" => false,
            ], 400);
        }

        $allProducts = $request->products;
        $totalTax = 0;
        $totalItems = 0;
        $totalQuantities = 0;
        $subtotal = 0;
        $total = 0;
        $oldOrderId = "";

        if ($request->address_id != 0) {
            $addressId = $this->getIdFromHash($request->address_id);
        } else {
            $addressId = 0;
        }

        $warehouse = Warehouse::where('slug', $request->warehouse)->first();

        if (!$warehouse) {
            throw new ApiException("Not a valid store.");
        }
        Log::info($request->address_id);
        if ($request->address_id != 0) {
            $address = UserAddress::where('user_id', $user->id)->where('id', $addressId)->first();
            if (!$address) {
                throw new ApiException("Address not valid.");
            }
            $shipping_type = 0;
        } else {
            $shipping_type = 1;
        }

        // added for order number generate

        $warehouse_latest = Warehouse::where(['id' => $warehouse->id])->orderBy('id', 'desc')->first();
        $invoice_spliter = $warehouse_latest->invoice_spliter;

        $invoice_prefix = $warehouse_latest->prefix_invoice;
        $invoice_suffix = $warehouse_latest->suffix_invoice;
        $invoice_spliter = $warehouse_latest->invoice_spliter;
        $lastorder = Order::where(['warehouse_id' => $warehouse->id, 'order_type' => 'sales'])->orWhere('order_type', 'online-orders')->orderBy('id', 'desc')->first();

        if ($warehouse_latest->invoice_started == 1) {

            $lastorder = $lastorder->invoice_number;
            $lastorder = explode($invoice_spliter, $lastorder);
            $lastorder = $lastorder[1] + 1;
        } else {
            $lastorder = $warehouse_latest->first_invoice_no + 1;
        }

        $currentinvoice = $lastorder;

        DB::beginTransaction();

        // try {
        $order = new Order();
        $order->order_type = 'online-orders';
        $order->invoice_number = "";
        $order->unique_id = Common::generateOrderUniqueId();
        $order->order_date = Carbon::now();
        $order->warehouse_id = $warehouse->id;
        $order->company_id = $warehouse->company_id;
        $order->user_id = $user->id;
        $order->subtotal = 0;
        $order->total = 0;
        $order->order_status = "ordered";
        $order->shipping = $request->shipping;
        $order->shipping_type = $shipping_type;
        $order->save();


        if (isset($request->shipping)) {
            $shippingAmt = $request->shipping;
        } else {
            $shippingAmt = 0;
        }



        if ($warehouse_latest->invoice_started == 0) {
            $currentinvoice = $warehouse_latest->first_invoice_no + 1;
            $formattedNumber = substr("0000" . $currentinvoice, -5);
            $order->invoice_number = Common::getTransactionNumberNew($order->order_type, $formattedNumber, $invoice_prefix, $invoice_suffix, $invoice_spliter);
        } else {
            $formattedNumber = substr("0000" . $currentinvoice, -5);
            $order->invoice_number = Common::getTransactionNumberNew($order->order_type, $formattedNumber, $invoice_prefix, $invoice_suffix, $invoice_spliter);
        }

        //      $order->invoice_number = Common::getTransactionNumber($order->order_type, $order->id);
        $order->save();

        foreach ($allProducts as $allProduct) {
            $productId = $this->getIdFromHash($allProduct['xid']);
            $product = Product::select('id', 'name', 'slug', 'image', 'description', 'category_id', 'brand_id', 'unit_id')
                ->with(['details:id,product_id,sales_price,mrp,tax_id,sales_tax_type,purchase_price', 'details.tax:id,rate', 'front_wholesale', 'category:id,name,slug', 'brand:id,name,slug'])
                ->find($productId);

            $productDet = ProductDetails::where('product_id', $productId)->first();
            $productDet->current_stock = $productDet->current_stock -  $allProduct['cart_quantity'];
            $productDet->save();
            // Tracking Stock History
            $stockHistory = new StockHistory();
            $stockHistory->company_id = $warehouse->company_id;
            $stockHistory->warehouse_id = $productDet->warehouse_id;
            $stockHistory->product_id = $productId;
            $stockHistory->quantity =  $allProduct['cart_quantity'];
            $stockHistory->old_quantity = 0;
            $stockHistory->order_type =  'online-orders';
            $stockHistory->stock_type =  'out';
            $stockHistory->action_type = "add";
            $stockHistory->created_by = $user->id;
            $stockHistory->save();
            if ($product) {
                $is_wholesale_price_updated = true;
                $orderItem = new OrderItem();
                $orderItem->user_id = $user->id;
                $orderItem->order_id = $order->id;
                $orderItem->product_id = $product->id;
                $orderItem->unit_id = $product->unit_id;
                $orderItem->quantity = $allProduct['cart_quantity'];
                $orderItem->return_quantity = $allProduct['cart_quantity'];
                if (count($product['front_wholesale']) > 0 && $user->is_wholesale_customer) {
                    foreach ($product['front_wholesale'] as $key => $wholesale) {
                        if (($wholesale['start_quantity'] <= $orderItem->quantity) && ($wholesale['end_quantity'] >= $orderItem->quantity)) {

                            $orderItem->unit_price = $wholesale['wholesale_price'];
                            $is_wholesale_price_updated = false;
                        }
                    }
                }

                if ($is_wholesale_price_updated) {
                    $orderItem->unit_price = $allProduct['details']['sales_price'];
                }

                Log::info($product->details);
                $orderItem->purchase_price = $product->details->purchase_price;

                $orderItem->tax_id = $product->details->tax_id;
                $orderItem->tax_rate = $product->details->tax_id != null ? $product->details->tax->rate : 0;
                $orderItem->tax_type = $product->details->sales_tax_type;
                $orderItem->mrp = isset($product->details['mrp']) ? $product->details->mrp : null;
                $orderItem->discount_rate = 0;
                $orderItem->total_discount = 0;



                $taxAmount = Common::getSalesOrderTax($orderItem->tax_rate, $orderItem->unit_price, $product->details->sales_tax_type);
                $salesPriceWithTax = Common::getSalesPriceWithTax($orderItem->tax_rate, $orderItem->unit_price, $product->details->sales_tax_type);

                $orderItem->single_unit_price = $product->details->sales_price - $taxAmount;
                $orderItem->total_tax = $taxAmount * $orderItem->quantity;
                $orderItem->subtotal = $salesPriceWithTax * $orderItem->quantity;
                $orderItem->save();
                $totalTax += $taxAmount * $orderItem->quantity;
                $totalItems += 1;
                $totalQuantities += $orderItem->quantity;
                $subtotal += $orderItem->subtotal;
                $total += $orderItem->subtotal;
            }
        }

        $order->tax_amount = 0;
        $order->tax_rate = 0;
        $order->discount_type = 'percentage';
        $order->discount = 0;
        $order->tax_rate = 0;
        $order->tax_rate = 0;
        $order->subtotal = $subtotal;
        $order->total = $total + $shippingAmt;
        $order->due_amount = $total;
        $order->total_items = $totalItems;
        $order->total_quantity = $totalQuantities;
        $order->save();
        // return $order;

        Common::storeAndUpdateOrderFront($order, $oldOrderId);

        if ($request->address_id != 0) {
            $orderShippingAddress = new OrderShippingAddress();
            $orderShippingAddress->company_id = $warehouse->company_id;
            $orderShippingAddress->order_id = $order->id;
            $orderShippingAddress->name = $address->name;
            $orderShippingAddress->email = $address->email;
            $orderShippingAddress->phone = $address->phone;
            $orderShippingAddress->address = $address->address;
            $orderShippingAddress->shipping_address = $address->shipping_address;
            $orderShippingAddress->city = $address->city;
            $orderShippingAddress->state = $address->state;
            $orderShippingAddress->country = $address->country;
            $orderShippingAddress->zipcode = $address->zipcode;
            $orderShippingAddress->save();
        }
        DB::commit();

        $globalCompany = GlobalCompany::first();
        $company = Company::where('id', $warehouse->company_id)->first();

        $notficationData = [
            'customer_name' => $user->name,
            'orderid' => $order->invoice_number,

        ];
        Notification::route('mail', $company->email)->notify(new NewOrder($notficationData));

        return ApiResponse::make('Order placed successfull', [
            'unique_id' => $order->unique_id,
            // Added for mobile app to show the Invoice number.
            'order_id' => $order->invoice_number,
        ]);
        // } catch (\Exception $e) {
        //     DB::rollback();

        //     throw new ApiException($e->getMessage());
        //     // throw new ApiException("Something went wrong. Please contact to administrator.");
        // }
    }

    public function checkoutSuccess($orderUniqueId)
    {
        $user = auth('api_front')->user();
        $order = Order::with(['user:id,name,email,address', 'warehouse:id,name,address', 'items', 'items.product:id,name', 'shippingAddress'])
            ->where('order_type', '=', 'sales')
            ->where('unique_id', '=', $orderUniqueId)
            ->where('user_id', '=', $user->id)
            ->first();

        return ApiResponse::make('Data fetched', [
            'order' => $order,
        ]);
    }
}
