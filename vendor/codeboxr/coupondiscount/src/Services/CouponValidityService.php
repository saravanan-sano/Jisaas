<?php

namespace Codeboxr\CouponDiscount\Services;

use Carbon\Carbon;
use Codeboxr\CouponDiscount\Models\Coupon;
use Codeboxr\CouponDiscount\Exceptions\CouponException;
use Codeboxr\CouponDiscount\Exceptions\CouponValidationException;
use Codeboxr\CouponDiscount\Exceptions\CouponHistoryValidationException;

class CouponValidityService
{
    /**
     * Coupon add validation
     *
     * @param array $array
     *
     * @throws CouponValidationException|CouponException
     */
    protected function validation($array)
    {
        if (!is_array($array)) {
            throw new \TypeError("Argument must be of the type array");
        }

        if (!count($array)) {
            throw new CouponValidationException("Invalid data!", 422);
        }

        $requiredFields = ['coupon_code', 'discount_type', 'discount_amount', 'start_date', 'end_date'];
        $this->fieldValidation($array, $requiredFields);

        if (!in_array($array["discount_type"], ["fixed", "percentage"])) {
            throw new CouponValidationException("discount_type must be fixed or percentage value", 422);
        }

        if (isset($array['use_limit']) && gettype($array['use_limit']) != "integer") {
            throw new CouponValidationException("use_limit accepted integer value", 422);
        }

        if (isset($array['use_same_ip_limit']) && gettype($array['use_same_ip_limit']) != "integer") {
            throw new CouponValidationException("use_same_ip_limit accepted integer value", 422);
        }

        if (isset($array['user_limit']) && gettype($array['user_limit']) != "integer") {
            throw new CouponValidationException("user_limit accepted integer value", 422);
        }

    }//end method validation

    /**
     * Coupon update validation
     *
     * @param array $array
     *
     * @throws CouponValidationException
     */
    protected function updateValidation($array)
    {
        if (!is_array($array)) {
            throw new \TypeError("Argument must be of the type array");
        }

        if (!count($array)) {
            throw new CouponValidationException("Invalid data!", 422);
        }

        if (!in_array($array["discount_type"], ["fixed", "percentage"])) {
            throw new CouponValidationException("discount_type must be fixed or percentage value", 422);
        }

        if (isset($array['use_limit']) && gettype($array['use_limit']) != "integer") {
            throw new CouponValidationException("use_limit accepted integer value", 422);
        }

        if (isset($array['use_same_ip_limit']) && gettype($array['use_same_ip_limit']) != "integer") {
            throw new CouponValidationException("use_same_ip_limit accepted integer value", 422);
        }

        if (isset($array['user_limit']) && gettype($array['user_limit']) != "integer") {
            throw new CouponValidationException("user_limit accepted integer value", 422);
        }

    }//end method updateValidation

    /**
     * Coupon history validation
     *
     * @param array $array
     *
     * @throws CouponHistoryValidationException|CouponException|CouponValidationException
     */
    protected function historyValidation($array)
    {
        if (!count($array)) {
            throw new CouponHistoryValidationException("Invalid data!", 422);
        }

        $requiredFields = ['user_id', 'coupon_id', 'order_id', 'discount_amount'];
        $this->fieldValidation($array, $requiredFields);
    }//end method historyValidation

    /**
     * Apply coupon validation
     *
     * @param array $array
     *
     * @throws CouponException|CouponValidationException
     */
    protected function applyValidation($array)
    {
        if (!is_array($array)) {
            throw new \TypeError("Argument must be of the type array");
        }

        if (!count($array)) {
            throw new CouponException("Invalid data!", 422);
        }

        $this->fieldValidation($array, ["code", "amount", "user_id", "order_id"]);
    }//end method applyValidation

    /**
     * Array data validation
     *
     * @param array $array
     * @param array $requiredFields
     *
     * @throws CouponException|CouponValidationException
     */
    private function fieldValidation(array $array, array $requiredFields)
    {
        if (!count($array)) {
            throw new CouponException("Invalid data!", 422);
        }

        if (!count($requiredFields)) {
            throw new CouponException("Invalid data!", 422);
        }

        $requiredColumns = array_diff($requiredFields, array_keys($array));

        if (count($requiredColumns)) {
            throw new CouponValidationException($requiredColumns, 422);
        }

        foreach ($requiredFields as $filed) {
            if (isset($data[$filed]) && empty($data[$filed])) {
                throw new CouponValidationException("$filed is required", 422);
            }
        }
    }//end method fieldValidation

    /**
     * Check coupon validity
     *
     * @param string $couponCode {coupon code}
     * @param float $amount (cart sub total amount)
     *
     * @param string $userId {user id}
     * @param string|null $deviceName {device ref, example: web, android, ios etc, default empty}
     * @param string|null $ipaddress {ip address }
     * @param string $vendorId {vendor id/shop id}
     * @param array $skip {some functionality need to skip from different scope while applying the coupon}
     *
     * @return array|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object
     * @throws CouponException
     */
    public function validity($couponCode, float $amount, string $userId, string $deviceName = null, string $ipaddress = null, string $vendorId = null, array $skip = [])
    {
        $coupon = Coupon::query()
            ->where("code", $couponCode)
            ->withCount([
                "couponHistories as user_use_coupon" => function ($q) use ($userId) {
                    $q->selectRaw("COUNT(*)")->where('user_id', $userId);
                }
            ])
            ->first();

        //01.Check if coupon exists
        if (!$coupon) {
            throw new CouponException("Invalid coupon code!", 500);
        }

        //02. Check coupon status
        if ($coupon->status != 1) {
            throw new CouponException("Coupon apply failed. This coupon is inactive.", 500);
        }

        //03. Check coupon start date validity
        if ($coupon->start_date > Carbon::today()->toDateTimeString()) {
            throw new CouponException("Coupon apply failed! Invalid coupon code.", 500);
        }

        //04. Check coupon end date validity
        if ($coupon->end_date && $coupon->end_date < Carbon::today()->toDateTimeString()) {
            throw new CouponException("Coupon apply failed! This coupon has expired.", 500);
        }

        //05. check coupon per user use limitation
        if ($coupon->use_limit_per_user && $coupon->use_limit_per_user > 0) {
            $couponHistories = $coupon->couponHistories->where("user_id", $userId);
            if ($couponHistories && $couponHistories->count() >= $coupon->use_limit_per_user) {
                throw new CouponException("Coupon apply failed! You have overcome the usage limit.", 500);
            }
        }

        //06. Check total coupon applied limitation
        if ($coupon->use_limit && $coupon->use_limit > 0) {
            if ($coupon->couponHistories->count() && $coupon->couponHistories->count() >= $coupon->use_limit) {
                throw new CouponException("The coupon apply failed! Because of overcoming the total usage limit.", 500);
            }
        }

        //07. Check minimum order amount to applied  this coupon
        if ($coupon->minimum_spend > 0 && $coupon->minimum_spend > $amount) {
            throw new CouponException("Invalid Amount! To apply this coupon minimum {$coupon->minimum_spend} amount is required", 500);
        }

        //08. Check maximum order amount to applied  this coupon
        if ($coupon->maximum_spend > 0 && $coupon->maximum_spend < $amount) {
            throw new CouponException("Invalid Amount! To apply this coupon maximum {$coupon->maximum_spend} amount is required", 500);
        }

        // check coupon code using device
        if ($coupon->use_device && !in_array("device_name", $skip)) {
            if (empty($deviceName)) {
                throw new CouponException("Coupon apply failed! Not found any device name.");
            }

            if ($coupon->use_device != $deviceName) {
                throw new CouponException("Coupon apply failed! This coupon only apply to " . ucfirst($coupon->use_device));
            }
        }

        // check same ip restriction
        if ($coupon->same_ip_limit && $coupon->same_ip_limit > 0 && !in_array("ip_address", $skip)) {
            if (empty($ipaddress)) {
                throw new CouponException("Coupon apply failed! Not found any IP address");
            }

            if (!filter_var($ipaddress, FILTER_VALIDATE_IP)) {
                throw new CouponException("Invalid IP address!");
            }

            $couponHistories = $coupon->couponHistories->where("user_ip", $ipaddress);
            if ($couponHistories && $coupon->same_ip_limit <= $couponHistories->count()) {
                throw new CouponException("Sorry, there are lots of order happened from your ip location using this coupon, we are not accepting more orders from your ip location for this coupon.");
            }
        }

        // check vendor restriction
        if ($coupon->vendor_id && $coupon->vendor_id > 0 && !in_array("vendor_id", $skip)) {
            if (empty($vendorId)) {
                throw new CouponException("Coupon apply failed! Not found any vendor id");
            }

            if ($coupon->vendor_id != $vendorId) {
                throw new CouponException("Coupon apply failed! This coupon can not be applied for this shop.");
            }
        }

        // calculate discount amount
        $discount_amount = 0;
        if ($coupon->type == 'fixed') {
            $discount_amount += floatval($coupon->amount);
        } else {
            $discount_percentage = floatval($coupon->amount);
            $discount_amount     += ($discount_percentage / 100) * floatval($amount);
        }

        $coupon->discount_amount = $discount_amount;

        return $coupon;
    }//end method validity

}//end class CouponValidityService
