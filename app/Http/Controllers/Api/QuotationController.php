<?php

namespace App\Http\Controllers\Api;

use App\Classes\Common;
use App\Http\Controllers\ApiBaseController;
use App\Http\Requests\Api\Quotations\IndexRequest;
use App\Http\Requests\Api\Quotations\StoreRequest;
use App\Http\Requests\Api\Quotations\UpdateRequest;
use App\Http\Requests\Api\Quotations\DeleteRequest;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Warehouse;
use App\Traits\OrderTraits;
use Carbon\Carbon;
use Examyou\RestAPI\ApiResponse;
use Illuminate\Support\Facades\Log;

class QuotationController extends ApiBaseController
{
    use OrderTraits;

    protected $model = Order::class;

    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function __construct()
    {
        parent::__construct();

        $this->orderType = "quotations";
    }

    public function convertToSale(Request $request, $id)
    {
        $order = Order::where('unique_id', $id)->first();
        $company = company();
        $timezone = $company->timezone;
        if ($order->order_type == "quotations") {
            $order->order_type = 'sales';
            $order->order_status = 'confirmed';

            // While Converting Setting the Current Date & Invoice type as Sales for Viewing Invoice. By saravanan
            $order->order_date = Carbon::now();
            $order->order_date_local = Carbon::now()->tz($timezone);
            $order->invoice_type = 'sales';

            $warehouse = warehouse();
            $warehouse_latest = Warehouse::where(['id' => $warehouse->id])->orderBy('id', 'desc')->first();
            //
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


            $currentinvoice = $lastorder;

            if ($warehouse_latest->invoice_started == 0) {
                $currentinvoice = $warehouse_latest->first_invoice_no + 1;
                $formattedNumber = substr("0000" . $currentinvoice, -5);
                $order->invoice_number = Common::getTransactionNumberNew($order->order_type, $formattedNumber, $invoice_prefix, $invoice_suffix, $invoice_spliter);
            } else {
                $formattedNumber = substr("0000" . $currentinvoice, -5);
                $order->invoice_number = Common::getTransactionNumberNew($order->order_type, $formattedNumber, $invoice_prefix, $invoice_suffix, $invoice_spliter);
            }
            $order->save();

            // Log::info(['order_id', $order['id']]);
            $productItems = OrderItem::where('order_id', $order['id'])->get();

            // Log::info([$productItems]);

            Common::updateStockHistory($order, $productItems, "");

            Common::updateUserAmount($order->user_id, $order->warehouse_id);

            // Updating Warehouse History
            Common::updateWarehouseHistory('order', $order, "add_edit");

            return ApiResponse::make('Success', []);
        }
    }
}
