<?php

namespace App\Http\Controllers\Api;

use App\Classes\Common;
use App\Http\Controllers\ApiBaseController;
use App\Http\Requests\Api\Customer\IndexRequest;
use App\Http\Requests\Api\Customer\StoreRequest;
use App\Http\Requests\Api\Customer\UpdateRequest;
use App\Http\Requests\Api\Customer\DeleteRequest;
use App\Models\Customer;
use App\Models\User;
use App\Traits\PartyTraits;
use Illuminate\Support\Facades\Log;
use App\Imports\PartyUpdateImport;
use App\Exports\CustomerExport;
use App\Http\Requests\Api\Customer\ImportRequest;
use App\Models\OrderItem;
use App\Models\Product;
use Examyou\RestAPI\ApiResponse;
use Maatwebsite\Excel\Facades\Excel;
use Vinkla\Hashids\Facades\Hashids;

class CustomersController extends ApiBaseController
{
    use PartyTraits;

    protected $model = Customer::class;

    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function __construct()
    {
        parent::__construct();

        $this->userType = "customers";
    }



    public function deactivateUser()
    {
        $request = request();
        // Log::info(["User", $request->xid]);

        $user  = User::where('id', Common::getIdFromHash($request->xid))->first();

        $user->is_deleted = 1;
        $user->email = $user->email . "-D";
        $user->phone = $user->phone . "-D";
        $user->save();
        return response()->json(['data' => Common::getIdFromHash($request->xid), 'message' => 'Deleted Successfully!', 'status' => 200], 200);
    }

    public function points()
    {

        $company = company();
        $companyWarehouse = $company->warehouse;

        $user = User::where('user_type', 'customers')->where('company_id', $company->id)->where('is_walkin_customer', '0')->get(["id", "name", "company_id", "warehouse_id", "role_id"]);

        foreach ($user as $key => $value) {
            $value['point_balance']  = $value->balance;
        }

        return response()->json(['data' => $user, 'message' => 'Data  Recived Successfully!', 'status' => 200], 200);
    }

    public function export()
    {
        return Excel::download(new CustomerExport, 'customers.xlsx');
    }

    public function Updateimport(ImportRequest $request)
    {
        if ($request->hasFile('file')) {
            Excel::import(new PartyUpdateImport($this->userType), request()->file('file'));
        }

        return ApiResponse::make('Imported Successfully', []);
    }


    public function customerWiseOrderReport()
    {
        $request = request();
        $warehouse = warehouse();

        Log::info([$warehouse]);
        $items = User::where('warehouse_id', $warehouse->id)->where('user_type', 'customers')->get();
        $category_orderItems = [];

        if ($request->has('dates') && $request->dates != null && count($request->dates) > 0) {
            $dates = $request->dates;
            $startDate = $dates[0];
            $endDate = $dates[1];
        }

        foreach ($items as $key => $item) {
            $orders = OrderItem::with('order:id,order_status')->where("user_id", $item->id)->whereRaw('created_at >= ?', [$startDate])
                ->whereRaw('created_at <= ?', [$endDate])->get();
            if (count($orders) > 0) {
                foreach ($orders as $key => $order) {

                    $product = Product::with('brand')->where('id', $order->product_id)->first();
                    $order->name = $product->name;
                    $order->hsn_sac_code = $product->hsn_sac_code;
                    $order->customer_name = $item->name;
                    $order->brand = $product->brand;
                    // Fetch and assign the tax_number to the order
                    $order->tax_number = $item->tax_number;
                }
                $category_orderItems[] = $orders;
            }
        }

        $productSum = [];

        $productData = [];

        foreach ($category_orderItems as $key => $items) {
            foreach ($items as $item) {
                $productData[$item->name] = ['quantity' => '0', 'subtotal' => 0, 'total' => 0, 'discount' => 0, 'tax_amount' => 0];
            }
            foreach ($items as $key => $item) {

                $productData[$item->name]['quantity'] = $productData[$item->name]['quantity'] + $item->quantity;
                $productData[$item->name]['subtotal'] = $productData[$item->name]['subtotal'] + $item->subtotal;
                $productData[$item->name]['discount'] = $productData[$item->name]['discount'] + $item->total_discount;
                $productData[$item->name]['tax_amount'] = $productData[$item->name]['tax_amount'] + $item->total_tax;
                $productData[$item->name]['total'] = $productData[$item->name]['total'] + $item->total_tax +  $item->subtotal - $item->total_discount;
                $productData[$item->name]['name'] = $item->name;
                $productData[$item->name]['hsn_sac_code'] = $item->hsn_sac_code;
                $productData[$item->name]['brand_name'] = $item->brand;
                // Assign tax_number to the product
                $productData[$item->name]['tax_number'] = $item->tax_number;


                $productSum[$items[0]->customer_name][] = $productData[$item->name];
            }
        }

        if ($request->has('customer') && $request->customer != null) {
            return ApiResponse::make('product', [$request->customer => $productSum[$request->customer]]);
        };

        return ApiResponse::make('product', $productSum);
    }

    public function customerUpdate()
    {

        $request = request();
        $company = company();

        $old_customers = User::where('id', common::getIdFromHash($request->id))->first();

        $customers = User::where('company_id', $company->id)
            ->where('name', $old_customers->name)
            ->where('email', $old_customers->email)
            ->where('phone', $old_customers->phone)
            ->get();

        foreach ($customers as $key => $customer) {
            $user = User::where('id', $customer->id)->first();

            $user->name = $request->name;
            $user->phone = $request->phone;
            $user->email = $request->email;
            $user->status = $request->status;
            $user->tax_number = $request->tax_number;
            $user->address = $request->address;
            $user->shipping_address = $request->shipping_address;
            $user->save();
        }
        return ApiResponse::make('message', 'Updated Successfully');
    }
}
