<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiBaseController;
use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentMode;
use App\Models\ProductDetails;
use App\Models\Product;
use App\Models\StaffCheckin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Vinkla\Hashids\Facades\Hashids;
use Carbon\Carbon;


use Examyou\RestAPI\ApiResponse;

class ReportController extends ApiBaseController
{
    public function profitLoss()
    {
        $request = request();
        $warehouse = warehouse();

        $sales = Order::where('order_type', 'sales');
        $purchases = Order::where('order_type', 'purchases');
        $salesReturns = Order::where('order_type', 'sales-returns');
        $purchaseReturns = Order::where('order_type', 'purchase-returns');
        $expenses = Expense::select('amount');

        $paymentReceived = Payment::where('payment_type', 'in');
        $paymentSent = Payment::where('payment_type', 'out');

        // Dates Filters
        if ($request->has('dates') && $request->dates != null && count($request->dates) > 0) {
            $dates = $request->dates;
            $startDate = $dates[0];
            $endDate = $dates[1];

            $sales = $sales->whereRaw('orders.order_date >= ?', [$startDate])
                ->whereRaw('orders.order_date <= ?', [$endDate]);
            $purchases = $purchases->whereRaw('orders.order_date >= ?', [$startDate])
                ->whereRaw('orders.order_date <= ?', [$endDate]);
            $salesReturns = $salesReturns->whereRaw('orders.order_date >= ?', [$startDate])
                ->whereRaw('orders.order_date <= ?', [$endDate]);
            $purchaseReturns = $purchaseReturns->whereRaw('orders.order_date >= ?', [$startDate])
                ->whereRaw('orders.order_date <= ?', [$endDate]);
            $expenses = $expenses->whereRaw('expenses.date >= ?', [$startDate])
                ->whereRaw('expenses.date <= ?', [$endDate]);

            $paymentReceived = $paymentReceived->whereRaw('payments.date >= ?', [$startDate])
                ->whereRaw('payments.date <= ?', [$endDate]);
            $paymentSent = $paymentSent->whereRaw('payments.date >= ?', [$startDate])
                ->whereRaw('payments.date <= ?', [$endDate]);
        }

        $sales = $sales->where('orders.warehouse_id', $warehouse->id);
        $purchases = $purchases->where('orders.warehouse_id', $warehouse->id);
        $salesReturns = $salesReturns->where('orders.warehouse_id', $warehouse->id);
        $purchaseReturns = $purchaseReturns->where('orders.warehouse_id', $warehouse->id);
        $expenses = $expenses->where('expenses.warehouse_id', $warehouse->id);

        $paymentReceived = $paymentReceived->where('payments.warehouse_id', $warehouse->id);
        $paymentSent = $paymentSent->where('payments.warehouse_id', $warehouse->id);

        $sales = $sales->sum('total');
        $purchases = $purchases->sum('total');
        $salesReturns = $salesReturns->sum('total');
        $purchaseReturns = $purchaseReturns->sum('total');
        $expenses = $expenses->sum('amount');

        $paymentReceived = $paymentReceived->sum('amount');
        $paymentSent = $paymentSent->sum('amount');

        $profit = $sales + $purchaseReturns - $purchases - $salesReturns - $expenses;
        $profitByPayment = $paymentReceived - $paymentSent - $expenses;

        return ApiResponse::make('Success', [
            'sales' => $sales,
            'purchases' => $purchases,
            'sales_returns' => $salesReturns,
            'purchase_returns' => $purchaseReturns,
            'expenses' => $expenses,
            'profit' => $profit,
            'payment_received' => $paymentReceived,
            'payment_sent' => $paymentSent,
            'profit_by_payment' => $profitByPayment,
        ]);
    }


    public function profitMargin()
    {

        $request = request();
        $warehouse = warehouse();

        $sales = Order::where('order_type', 'sales');

        if ($request->has('dates') && $request->dates != null && count($request->dates) > 0) {
            $dates = $request->dates;
            $startDate = $dates[0];
            $endDate = $dates[1];

            $sales = $sales->whereRaw('orders.order_date >= ?', [$startDate])
                ->whereRaw('orders.order_date <= ?', [$endDate]);
        }

        $sales = $sales->where('orders.warehouse_id', $warehouse->id)->with('items')->get();

        $data = [];
        foreach ($sales as $key => $sale) {
            foreach ($sale->items as $key => $item) {

                $timestamp = $sale->order_date_local;
                $formattedDate = Carbon::parse($timestamp)->format('Y-m-d'); // Format as 'YYYY-MM-DD'

                // Check if the key exists, if not, initialize it as an empty array
                if (!isset($data[$formattedDate])) {
                    $data[$formattedDate] = [];
                }

                // Push $item into the array with the key $formattedDate
                $data[$formattedDate][] = $item;
                # code...
            }
        }


        return ApiResponse::make('Success', [
            'sales' => $data,
        ]);
    }

    public function getWarehouseId()
    {
        $waehouse = warehouse();
        $warehouseId = $waehouse->id;

        return $warehouseId;
    }

    public function generateReport()
    {

        $request = request();
        $company = company();
        $timezone = $company->timezone;
        $warehouseId = $this->getWarehouseId();


        $sales = DB::table('orders')
            ->selectRaw('HOUR(order_date_local) as hour, ROUND(SUM(subtotal), 2) as total_sales, COUNT(*) as order_count, ROUND(AVG(subtotal), 2) as average_order');

        if ($request->has('dates') && $request->dates != null && count($request->dates) > 1) {
            $dates = $request->dates;

            //$paymentModes = $paymentModes->whereBetween(DB::raw('DATE(order_date1)'), $dates);
            $sales = $sales->whereBetween('order_date_local',  $dates);
        }

        if ($request->has('staff')) {
            $staff = $this->getIdFromHash($request->staff);

            //$paymentModes = $paymentModes->whereBetween(DB::raw('DATE(order_date1)'), $dates);
            $sales = $sales->where('staff_user_id',  $staff);
        }

        //->whereDate('order_date_local',  Carbon::now()->tz($timezone)->toDateString()) // Filter by today's date
        $sales = $sales->where('orders.warehouse_id', $warehouseId)
            ->where('orders.order_type', 'sales')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $averageOrderValue = DB::table('orders');
        if ($request->has('dates') && $request->dates != null && count($request->dates) > 1) {
            $dates = $request->dates;

            //$paymentModes = $paymentModes->whereBetween(DB::raw('DATE(order_date1)'), $dates);
            $averageOrderValue = $averageOrderValue->whereBetween('order_date_local',  $dates);
        }

        if ($request->has('staff')) {
            $staff = $this->getIdFromHash($request->staff);

            //$paymentModes = $paymentModes->whereBetween(DB::raw('DATE(order_date1)'), $dates);
            $averageOrderValue = $averageOrderValue->where('orders.staff_user_id',  $staff);
        }

        //  ->whereDate('order_date_local', Carbon::now()->tz($timezone)->toDateString())
        $averageOrderValue = $averageOrderValue->where('orders.warehouse_id', $warehouseId)
            ->where('orders.order_type', 'sales')
            ->avg('subtotal');

        $sales_category = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('categories', 'products.category_id', '=', 'categories.id');
        if ($request->has('dates') && $request->dates != null && count($request->dates) > 1) {
            $dates = $request->dates;

            //$paymentModes = $paymentModes->whereBetween(DB::raw('DATE(order_date1)'), $dates);
            $sales_category = $sales_category->whereBetween('orders.created_at',  $dates);
        }

        if ($request->has('staff')) {
            $staff = $this->getIdFromHash($request->staff);

            //$paymentModes = $paymentModes->whereBetween(DB::raw('DATE(order_date1)'), $dates);
            $sales_category = $sales_category->where('orders.staff_user_id',  $staff);
        }

        // ->whereDate('orders.created_at', Carbon::now()->tz($timezone))
        $sales_category = $sales_category->where('orders.warehouse_id', $warehouseId)
            ->where('orders.order_type', 'sales')
            ->select(
                'categories.name as category_name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.subtotal) as total_value')
            )
            ->groupBy('categories.name')
            ->get();

        $sales_products = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('categories', 'products.category_id', '=', 'categories.id');

        if ($request->has('dates') && $request->dates != null && count($request->dates) > 1) {
            $dates = $request->dates;

            //$paymentModes = $paymentModes->whereBetween(DB::raw('DATE(order_date1)'), $dates);
            $sales_products = $sales_products->whereBetween('orders.created_at',  $dates);
        }

        if ($request->has('staff')) {
            $staff = $this->getIdFromHash($request->staff);

            //$paymentModes = $paymentModes->whereBetween(DB::raw('DATE(order_date1)'), $dates);
            $sales_products = $sales_products->where('orders.staff_user_id',  $staff);
        }

        $sales_products = $sales_products->where('orders.warehouse_id', $warehouseId)
            ->where('orders.order_type', 'sales')
            ->select(
                'products.name as product_name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.subtotal) as total_value')
            )
            ->groupBy('products.name')
            ->orderBy('total_quantity', 'desc')
            ->get();






        $paymentModes = DB::table('payments')->where('payments.warehouse_id', $warehouseId)
            ->join('payment_modes', 'payments.payment_mode_id', '=', 'payment_modes.id')
            ->select(
                'payment_modes.name as payment_mode',
                DB::raw('DATE(payments.created_at) as order_date'),
                DB::raw('COUNT(payments.company_id) as total_orders'),
                DB::raw('SUM(payments.paid_amount) as total_amount')
            );


        if ($request->has('dates') && $request->dates != null && count($request->dates) > 1) {
            $dates = $request->dates;

            //$paymentModes = $paymentModes->whereBetween(DB::raw('DATE(order_date1)'), $dates);
            $paymentModes = $paymentModes->whereBetween('payments.created_at',  $dates);
        }

        if ($request->has('staff')) {
            $staff = $this->getIdFromHash($request->staff);

            //$paymentModes = $paymentModes->whereBetween(DB::raw('DATE(order_date1)'), $dates);
            $paymentModes = $paymentModes->where('payments.staff_user_id',  $staff);
        }

        $paymentModes = $paymentModes->groupBy('payment_modes.name', 'order_date')
            ->orderBy('payment_modes.name', 'ASC')
            ->orderBy('order_date', 'ASC')
            ->get();

        //   return $paymentModes;
        return [
            'totalSalesData' => $sales,
            'average' => round($averageOrderValue, 2),
            'category_sale' => $sales_category,
            'product_sales' => $sales_products,
            'payments' => $paymentModes,

        ];
    }

    public function expiryProductReport()
    {
        $request = request();
        $company = company();
        $timezone = $company->timezone;
        $warehouseId = $this->getWarehouseId();
        $nextTwoDays = now()->addDays($request->days);
        $products = ProductDetails::whereDate('expiry', '<=', $nextTwoDays)->where('product_details.warehouse_id', $warehouseId)
            ->with('product')
            ->orderBy('expiry', 'ASC')
            ->get();

        return ApiResponse::make('Success', ['products' => $products]);
    }

    public function assignedStaffReport()
    {
        $request = request();
        $company = company();
        $timezone = $company->timezone;
        $warehouseId = $this->getWarehouseId();

        $query = User::where('user_type', 'staff_members')
            ->where('warehouse_id', $warehouseId)
            ->whereHas('role', function ($q) {
                $q->where('name', '!=', 'admin');
            });

        // Check if the staff key is not present or null in the request
        if (!$request->has('staff') || is_null($request->staff)) {
            $staffMembers = $query->get();
        } else {
            // Assuming staff key exists and is not null, filter by the provided staff xid
            $staffMembers = $query->where('id', $this->getIdFromHash($request->staff))->get();
        }

        $report = [];
        foreach ($staffMembers as $staffMember) {
            $customers = User::where('user_type', 'customers')
                ->where('warehouse_id', $warehouseId)
                ->where('assign_to', $staffMember->xid)
                ->where('status', 'enabled')
                ->whereBetween('created_at', [$request->dates[0], $request->dates[1]])
                ->get();

            $customersCount = $customers->count();
            $report[] = [
                'staff_member' => $staffMember->name,
                'customer_count' => $customersCount,
                'customers' => $customers
            ];
        }
        return response()->json($report);
    }

    public function checkInStaffReport()
    {
        $request = request();
        $warehouseId = warehouse()->id;
        $startDate = Carbon::createFromFormat('Y-m-d', $request->dates[0]);
        $endDate = Carbon::createFromFormat('Y-m-d', $request->dates[1]);

        $checkinReports = [];

        $currentDate = clone $startDate;
        while ($currentDate->lte($endDate)) {
            $query = User::where('user_type', 'staff_members')->where('name', '!=', 'Admin')->where('is_deleted', 0)->where('warehouse_id', $warehouseId)
                ->with(['staffCheckins' => function ($query) use ($warehouseId, $currentDate) {
                    $query->whereDate('date', $currentDate)
                        ->where('warehouse_id', $warehouseId);
                }]);

            // Check if staff is present in the request
            if ($request->staff) {
                $query->where('id', Hashids::decode($request->staff));
            }

            $staffCheckin = $query->get();

            $report = [
                'date' => $currentDate->format('d-m-Y'),
                'details' => $staffCheckin->map(function ($staff) {
                    return [
                        'name' => $staff->name,
                        'xid' => $staff->xid,
                        'checkin' => $staff->staffCheckins->first() ?? null
                    ];
                })
            ];

            // Add the report for the current day to the checkinReports array
            $checkinReports[] = $report;

            // Move to the next day
            $currentDate->addDay();
        }

        return response()->json(["checkin_report" => $checkinReports]);
    }
}
