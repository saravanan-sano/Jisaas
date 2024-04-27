<?php

namespace App\Http\Controllers\Api;

use App\Classes\Common;
use App\Http\Controllers\ApiBaseController;
use App\Http\Requests\Api\Payments\IndexRequest;
use App\Http\Requests\Api\Payments\StoreRequest;
use App\Http\Requests\Api\Payments\UpdateRequest;
use App\Http\Requests\Api\Payments\DeleteRequest;
use App\Models\Payment;
use App\Traits\PaymentTraits;

class PaymentInController extends ApiBaseController
{
    use PaymentTraits;

    protected $model = Payment::class;
    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;
    public function __construct()
    {
        parent::__construct();
        $this->paymentType = "in";
    }


    public function paymentInReport()
    {

        $request = request();
        $company = company();

        $user = user();

        $startDate = null;
        $endDate = null;

        if ($request->has('dates') && $request->dates != null && count($request->dates) > 0) {
            $dates = $request->dates;
            $startDate = $dates[0];
            $endDate = $dates[1];
        }


        if ($user->role->name == "admin") {
            $paymentIn = Payment::with("paymentMode", "user", "customer")->where('company_id', $company->id)->where('staff_user_id',  Common::getIdFromHash($request->staff_user_id))->whereRaw('date >= ?', [$startDate])
                ->whereRaw('date <= ?', [$endDate])->get();
        } else {
            $paymentIn = Payment::with("paymentMode", "user", "customer")->where('company_id', $company->id)->where('staff_user_id', $user->id)->whereRaw('date >= ?', [$startDate])
                ->whereRaw('date <= ?', [$endDate])->get();
        }
        $totalPaymentIn = (object)[];
        foreach ($paymentIn as $key => $payment) {
            $temp =  $paymentIn[$key]->paymentMode->name;
            $totalPaymentIn->$temp = 0;
        }
        foreach ($paymentIn as $key => $payment) {
            $temp =  $paymentIn[$key]->paymentMode->name;
            $totalPaymentIn->$temp  +=  $payment->paid_amount;
        }

        return ['paymentList' => $paymentIn, "total" => $totalPaymentIn];
    }
}
