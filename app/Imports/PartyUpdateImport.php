<?php

namespace App\Imports;

use App\Models\Customer;
use App\Models\User;
use Examyou\RestAPI\Exceptions\ApiException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\ToArray;
use Vinkla\Hashids\Facades\Hashids;

class PartyUpdateImport implements ToArray, WithHeadingRow
{
    public $userType = "";

    public function __construct($userType)
    {
        $this->userType = $userType;
    }

    public function array(array $parties)
    {
        DB::transaction(function () use ($parties) {
            $user = user();
            $warehouse = warehouse();

            foreach ($parties as $party) {
                if (
                    !array_key_exists('name', $party) || !array_key_exists('email', $party) || !array_key_exists('phone', $party) || !array_key_exists('address', $party) || !array_key_exists('status', $party) || !array_key_exists('tax_number', $party) || !array_key_exists('is_wholesale_customer', $party) || !array_key_exists('pincode', $party) || !array_key_exists('location', $party) || !array_key_exists('business_type', $party) || !array_key_exists('id', $party)
                ) {
                    throw new ApiException('Field missing from header.');
                }

                $name = trim($party['name']);
                $userId = Hashids::decode(trim($party['id']));
                $email = trim($party['email']);
                if ($email) {
                    $emailCount = Customer::withoutGlobalScope('type')->where('email', $email)->where('user_type', $this->userType)->where('warehouse_id', $warehouse->id)->where('id', '!=', $userId)->count();
                    if ($emailCount > 0) {
                    //    Log::info([$party]);
                        throw new ApiException('Email ' . $email . ' Already Exists');
                    }
                }

                $phone = trim($party['phone']);
                $phoneCount = Customer::withoutGlobalScope('type')->where('phone', $phone)->where('user_type', $this->userType)->where('warehouse_id', $warehouse->id)->where('id', '!=', $userId)->count();
                if ($phoneCount > 0) {
                    throw new ApiException('Phone ' . $phone . ' Already Exists');
                }

                $address = trim($party['address']);
                $taxNumber = trim($party['tax_number']);
                if ($this->userType == 'customers') {
                    $isWholesaleCustomer = trim($party['is_wholesale_customer']) == 'Yes' ? 1 : 0;
                } else {
                    $isWholesaleCustomer = 0;
                }

                $status = trim($party['status']);
                $status = strtolower($status);
                if (!in_array($status, ['enabled', 'disabled'])) {
                    throw new ApiException('Status type must be enabled or disabled');
                }
                $pincode = trim($party['pincode']);
                $warehousePincode = explode(',', $warehouse->pincode);
                if ($pincode != "" && !in_array($pincode, $warehousePincode)) {
                    throw new ApiException('Invalid Pincode');
                }

                $location = trim($party['location']);
                $warehouseLocation = explode(',', $warehouse->location);
                if ($location != "" && !in_array($location, $warehouseLocation)) {
                    throw new ApiException('Invalid Location');
                }

                $businessType = trim($party['business_type']);
                $warehouseBusinessType = explode(',', $warehouse->business_type);
                if ($businessType != "" && !in_array($businessType, $warehouseBusinessType)) {
                    throw new ApiException('Invalid Business Type');
                }

                $user = User::where('id', $userId)->first();

                $user->name = $name;
                $user->email = $email;
                $user->phone = $phone;
                $user->address = $address != "" ? $address : "";
                $user->tax_number = $taxNumber;
                $user->is_wholesale_customer = $isWholesaleCustomer;
                $user->status = $status;
                $user->pincode = $pincode;
                $user->location = $location;
                $user->business_type = $businessType;
                $user->save();
            }
        });
    }
}
