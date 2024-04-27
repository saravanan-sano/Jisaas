<?php

namespace App\SuperAdmin\Http\Controllers\Api\Admin;

use App\Http\Controllers\ApiBaseController;
use App\Models\Company;
use App\Models\Product;
use App\Models\SubscriptionPlan;
use App\Scopes\CompanyScope;
use App\SuperAdmin\Http\Requests\Api\Admin\UpiPaymentRequest;
use App\SuperAdmin\Models\UpiPayment;
use App\SuperAdmin\Models\GlobalCompany;
use Examyou\RestAPI\ApiResponse;
use App\SuperAdmin\Models\GlobalSettings;
use App\SuperAdmin\Models\Subscription;
use Examyou\RestAPI\Exceptions\ApiException;
use Razorpay\Api\Api;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Log;
use \stdClass;


class UpiSubscriptionController extends ApiBaseController
{

    public function upiSubscription(UpiPaymentRequest $request)
    {

        $UpiSettings = GlobalSettings::withoutGlobalScope(CompanyScope::class)
            ->where('setting_type', 'payment_settings')
            ->where('name_key', 'upi')
            ->first();

        $credential = (object) $UpiSettings->credentials;

        $apiKey    = $credential->upi_gateway_key;

        $company = company();

        try {

                $key = $apiKey;	// Your Api Token https://merchant.upigateway.com/user/api_credentials
                $post_data = new \stdClass();
                $post_data->key = $key;
                // Generate a random client_txn_id
                $client_txn_id = (string) rand(100000, 999999);

                // Check if the client_txn_id already exists in the table
                while (UpiPayment::where('id', $client_txn_id)->exists()) {
                    // Regenerate the client_txn_id
                    $client_txn_id = (string) rand(100000, 999999);
                }

                // Assign the unique client_txn_id to the $post_data object
                $post_data->client_txn_id = $client_txn_id;
                // you can use this field to store order id;

                // $post_data->amount = (string) $request->txnAmount;
                $post_data->amount = "1";
                $post_data->p_info = "product_name";
                $post_data->customer_name = $request['customerName'];
                $post_data->customer_email = $request['customerEmail'];
                $post_data->customer_mobile = $request['customerMobile'];
                $post_data->redirect_url = "http://127.0.0.1:8000/save-upi-invoices"; // automatically ?client_txn_id=xxxxxx&txn_id=xxxxx will be added on redirect_url
                $post_data->udf1 = $request['plan_Id'];
                $post_data->udf2 = $company->xid;
                $post_data->udf3 = "extradata";

                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => 'https://merchant.upigateway.com/api/create_order',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => json_encode($post_data),
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json'
                    ),
                ));

                $response = curl_exec($curl);
                curl_close($curl);

            return ApiResponse::make('Success',[json_decode($response)->data]);
        } catch (\Exception $e) {
            throw new ApiException($e->getMessage());
        }
    }

}
