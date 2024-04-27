<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiBaseController;
use Illuminate\Http\Request;
use App\Models\DeleteLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Http;

class MasterDeleteController extends ApiBaseController
{


    public function deleteData(Request $request)
    {

        $input = $request->input('name');
        $company = company();
        $warehouse = warehouse();
        $user = user();

        $allowedModelNames = [
            'Product',
            'Order',
            'Brand',
            'Customer',
        ];

        try {

            foreach ($input as $modelName) {

                if (in_array($modelName, $allowedModelNames)) {

                    $updatedOTP = DB::table('warehouses')->where('id', $warehouse->id)->value('otp');

                    if ($request->otp == $updatedOTP) {

                        if ($modelName == "Brand") {
                            $className = "App\\Models\\$modelName";
                            $object = new $className;
                            $object->Where('company_id', $company->id)
                                ->delete();

                        }
                        else if ($modelName == "Customer") {
                            $className = "App\\Models\\$modelName";
                            $object = new $className;
                            $customer = $object->where('warehouse_id', $warehouse->id)->where('is_walkin_customer', 0)->first();

                            if ($customer) {
                                $orders = $customer->orders;

                                // Loop through each order and delete order items
                                foreach ($orders as $order) {
                                    $order->items()->delete();
                                }

                                // Delete the customer and related orders
                                $customer->orders()->delete();
                                $customer->payment()->delete();
                                $customer->userdetails()->delete();
                                DB::table('wallets')->where('holder_id', $customer->id)->delete();
                                $customer->delete();
                            }

                        }
                         else {

                            $className = "App\\Models\\$modelName";

                            $object = new $className;
                            if ($modelName == "Brand") {
                                $className = "App\\Models\\$modelName";
                                $object = new $className;
                                $object->Where('company_id', $company->id)
                                    ->delete();
                            }
                            $object->where('warehouse_id', $warehouse->id)
                                ->orWhere('company_id', $company->id)
                                ->delete();

                        }


                        $Deletelogs = new DeleteLog();
                        $Deletelogs->user_id = $user->id;
                        $Deletelogs->user_agent = $request->userAgent();
                        $Deletelogs->ip = $request->ip();
                        $Deletelogs->field = $modelName;
                        $Deletelogs->save();

                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => "Invalid Otp."
                        ]);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Data for selected models has been deleted successfully."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    public function sendDeleteOtp()
    {
        $company = company();
        $warehouse = warehouse();
        $user = user();
        $otp = mt_rand(100000, 999999);
        DB::table('warehouses')->where('id', $warehouse->id)->update(['otp' => $otp]);
        $mobile = DB::table('warehouses')->where('id', $warehouse->id)->value('phone');
        $countryCode = $user->country_code;
        $smsStatus = $this->sendSMS($mobile, $otp, $countryCode);

        if ($smsStatus === 1) {
            return response()->json([
                'success' => true,
                'message' => 'OTP sent successfully to the warehouse registered mobile number.'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.',
            ]);
        }
    }


    private function sendSMS($phone, $otp, $countryCode)
    {
        $warehouse = warehouse();
        $updatedOTP = DB::table('warehouses')->where('id', $warehouse->id)->value('otp');
        $url = 'https://api.authkey.io/request';
        $authkey = 'e543b8ad701e8670';
        $mobile = $phone;
        $country_code = $countryCode;
        $sid = '9102';
        $name = 'URESTR';
        $otpMessage = 'Hi, ' . $otp . ' is your One Time Password. Please use this password to delete data. Jnana In';
        $otp1 = $otp;
        $mob = 'jierp';

        $response = Http::get($url, [
            'authkey' => $authkey,
            'mobile' => $mobile,
            'country_code' => $country_code,
            'sid' => $sid,
            'name' => $name,
            'otp' => $otpMessage,
            'otp1' => $otp1,
            'mob' => $mob,
        ]);

        if ($response->failed()) {
            return 0;
        } else {
            return 1;
        }
    }

    public function getdeletelogs()
    {
        $data = DeleteLog::with('user')
            ->get()
            ->map(function ($log) {
                $log['deleted_on'] = $log['created_at'];
                unset($log['created_at'], $log['updated_at']);
                return $log;
            });

        return $data;
    }
}
