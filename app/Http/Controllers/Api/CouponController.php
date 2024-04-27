<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiBaseController;
use Examyou\RestAPI\ApiResponse;
use Codeboxr\CouponDiscount\Facades\Coupon;
use Illuminate\Support\Facades\Log;

class CouponController extends ApiBaseController
{
    public function addCoupon()
    {

        $request = request();
        $warehouse = warehouse();

        $coupon = Coupon::add([
            'coupon_code'       => $request->coupon_code, // (required) Coupon code
            'discount_type'     => $request->discount_type, // (required) coupon discount type. two type are accepted (1. percentage and 2. fixed)
            'discount_amount'   => $request->discount_amount, // (required) discount amount or percentage value
            'start_date'        => \Carbon\Carbon::parse($request->start_date)->format('Y-m-d H:i'), // (required) coupon start date
            'end_date'          => \Carbon\Carbon::parse($request->end_date)->format('Y-m-d H:i'), // (required) coupon end date
            'status'            => $request->status ? $request->status : "1", // (required) two status are accepted. (for active 1 and for inactive 0)
            'minimum_spend'     => $request->minimum_spend ? $request->minimum_spend : null, // (optional) for apply this coupon minimum spend amount. if set empty then it's take unlimited
            'maximum_spend'     => $request->maximum_spend ? $request->maximum_spend : null, // (optional) for apply this coupon maximum spend amount. if set empty then it's take unlimited
            'use_limit'         => $request->use_limit ? $request->use_limit : null, // (optional) how many times are use this coupon. if set empty then it's take unlimited
            'use_same_ip_limit' => $request->use_same_ip_limit ? $request->use_same_ip_limit : null, // (optional) how many times are use this coupon in same ip address. if set empty then it's take unlimited
            'user_limit'        => $request->user_limit ? $request->user_limit : null, // (optional) how many times are use this coupon a user. if set empty then it's take unlimited
            'use_device'        => $request->use_device ? $request->use_device : null, // (optional) This coupon can be used on any device
            'multiple_use'      => $request->multiple_use ? $request->multiple_use : "no", // (optional) you can check manually by this multiple coupon code use or not
            'vendor_id'         => $warehouse->id // (optional) if coupon code use specific shop or vendor
        ]);

        return ApiResponse::make('coupon Created', $coupon);
    }

    public function updateCoupon()
    {
        $request = request();
        $warehouse = warehouse();

        $couponId = $request->id;

        $coupon = Coupon::update([
            'coupon_code'       => $request->coupon_code, // (required) Coupon code
            'discount_type'     => $request->discount_type, // (required) coupon discount type. two type are accepted (1. percentage and 2. fixed)
            'discount_amount'   => $request->discount_amount, // (required) discount amount or percentage value
            'start_date'        => \Carbon\Carbon::parse($request->start_date)->format('Y-m-d H:i'), // (required) coupon start date
            'end_date'          => \Carbon\Carbon::parse($request->end_date)->format('Y-m-d H:i'), // (required) coupon end date
            'status'            => $request->discount_amount ? $request->discount_amount : "1", // (required) two status are accepted. (for active 1 and for inactive 0)
            'minimum_spend'     => $request->minimum_spend ? $request->minimum_spend : "", // (optional) for apply this coupon minimum spend amount. if set empty then it's take unlimited
            'maximum_spend'     => $request->maximum_spend ? $request->maximum_spend : "", // (optional) for apply this coupon maximum spend amount. if set empty then it's take unlimited
            'use_limit'         => $request->use_limit ? $request->use_limit : "", // (optional) how many times are use this coupon. if set empty then it's take unlimited
            'use_same_ip_limit' => $request->use_same_ip_limit ? $request->use_same_ip_limit : "", // (optional) how many times are use this coupon in same ip address. if set empty then it's take unlimited
            'user_limit'        => $request->user_limit ? $request->user_limit : "", // (optional) how many times are use this coupon a user. if set empty then it's take unlimited
            'use_device'        => $request->use_device ? $request->use_device : "", // (optional) This coupon can be used on any device
            'multiple_use'      => $request->multiple_use ? $request->multiple_use : "", // (optional) you can check manually by this multiple coupon code use or not
            'vendor_id'         => $warehouse->id // (optional) if coupon code use specific shop or vendor
        ], $couponId);

        return ApiResponse::make('coupon Updated', $coupon);
    }

    public function listCoupon()
    {

        $warehouse = warehouse();
        $coupons = Coupon::where('vendor_id', $warehouse->id)->get();
        return ApiResponse::make('coupon_list', ["coupons" => $coupons]);
    }

    public function validateCoupon()
    {

        $loggedInUser = user();

        //validity($couponCode, float $amount, string $userId, string $deviceName = null, string $ipaddress = null, string $vendorId = null)
        $request = request();

        $couponCode = $request->coupon_code;
        $amount = $request->amount;
        $userId = $loggedInUser->id;

        $validate =  Coupon::validity($couponCode, $amount, $userId);

        return ApiResponse::make('coupon_list', ["coupon" =>  $validate]);
    }

    public function applyCoupon()
    {

        $request = request();
        $loggedInUser = user();
        $userId = $loggedInUser->id;

        $appliedCoupon =  Coupon::apply([
            "code"        => $request->coupon_code, // coupon code. (required)
            "amount"      => $request->amount, // total amount to apply coupon. must be a numberic number (required)
            "user_id"     => $userId, // user id (required)
            "order_id"    => $request->order_id, // order id (required)
            "device_name" => "", $request->device_name ? $request->device_name : "", //(optional)
            "ip_address"  => "", $request->ip_address ? $request->ip_address : "" // (optional)
        ]);

        return ApiResponse::make('coupon_list', ["coupon" =>  $appliedCoupon]);
    }

    public function usedCouponHistory()
    {

        $couponHistory = Coupon::history()->get();

        return ApiResponse::make('coupon_history', ["history" =>  $couponHistory]);
    }

    public function CouponHistoryDelete()
    {

        $request = request();
        $historyId = $request->history_id;
        Coupon::historyDelete($historyId);


        return ApiResponse::make('coupon_history_deleted');
    }
}
