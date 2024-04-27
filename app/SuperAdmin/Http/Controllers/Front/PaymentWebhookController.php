<?php

namespace App\SuperAdmin\Http\Controllers\Front;

use App\Models\Company;
use App\SuperAdmin\Models\Subscription;
use App\SuperAdmin\Models\UpiPayment;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Scopes\CompanyScope;
use App\SuperAdmin\Models\PaymentTranscation;
use App\SuperAdmin\Traits\StripeSettings;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Stripe\Stripe;
use Stripe\Webhook;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Vinkla\Hashids\Facades\Hashids;

class PaymentWebhookController extends Controller
{
    use StripeSettings;

    public function saveStripeInvoices(Request $request)
    {
        // Log::debug('request' . $request->all());
        $this->setStripConfigs();
        $stripeCredentials = config('cashier.webhook.secret');

        Stripe::setApiKey(config('cashier.secret'));

        // You can find your endpoint's secret in your webhook settings
        $endpoint_secret = $stripeCredentials;

        $payload = @file_get_contents("php://input");
        $sig_header = $_SERVER["HTTP_STRIPE_SIGNATURE"];
        $event = null;

        try {

            $event = Webhook::constructEvent(
                $payload,
                $sig_header,
                $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            return response('Invalid Payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
            return response('Invalid signature', 400);
        }

        $payload = json_decode($request->getContent(), true);

        // Do something with $event
        // Log::debug('payload' . $payload);

        if ($payload['type'] == 'invoice.payment_succeeded') {
            $planId = $payload['data']['object']['lines']['data'][0]['plan']['id'];
            $customerId = $payload['data']['object']['customer'];
            $amount = $payload['data']['object']['amount_paid'];
            $transactionId = $payload['data']['object']['lines']['data'][0]['id'];
            //            $invoiceId = $payload['data']['object']['number'];
            $invoiceRealId = $payload['data']['object']['id'];

            $company = Company::where('stripe_id', $customerId)->first();
            //  // Log::info($company);

            $package = SubscriptionPlan::where(function ($query) use ($planId) {
                $query->where('stripe_annual_plan_id', '=', $planId)
                    ->orWhere('stripe_monthly_plan_id', '=', $planId);
            })->first();

            if ($company) {
                // Store invoice details
                $stripeInvoice = new PaymentTranscation();
                $stripeInvoice->payment_method = 'stripe';
                $stripeInvoice->company_id = $company->id;
                $stripeInvoice->invoice_id = $invoiceRealId;
                $stripeInvoice->transcation_id = $transactionId;
                $stripeInvoice->total = $amount / 100;
                $stripeInvoice->subscription_plan_id = $package->id;
                $stripeInvoice->paid_on = \Carbon\Carbon::now()->format('Y-m-d');
                $stripeInvoice->next_payment_date = \Carbon\Carbon::createFromTimeStamp($company->upcomingInvoice()->next_payment_attempt)->format('Y-m-d');

                $stripeInvoice->save();

                // Change company status active after payment
                $company->status = 'active';
                $company->save();

                // $generatedBy = User::whereNull('company_id')->get();
                // $lastInvoice = StripeInvoice::where('company_id')->first();

                // TODO - Notifications
                //                if($lastInvoice){
                //                    Notification::send($generatedBy, new CompanyUpdatedPlan($company, $package->id));
                //                }else{
                //                    Notification::send($generatedBy, new CompanyPurchasedPlan($company, $package->id));
                //                }

                return response('Webhook Handled', 200);
            }

            return response('Customer not found', 200);
        } elseif ($payload['type'] == 'invoice.payment_failed') {
            $customerId = $payload['data']['object']['customer'];

            $company = Company::where('stripe_id', $customerId)->first();
            $subscription = Subscription::where('payment_method', 'stripe')->where('company_id', $company->id)->first();

            if ($subscription) {
                $subscription->ends_at = \Carbon\Carbon::createFromTimeStamp($payload['data']['object']['current_period_end'])->format('Y-m-d');
                $subscription->save();
            }

            if ($company) {

                $company->licence_expire_on = \Carbon\Carbon::createFromTimeStamp($payload['data']['object']['current_period_end'])->format('Y-m-d');
                $company->save();

                return response('Company subscription canceled', 200);
            }

            return response('Customer not found', 200);
        }
    }

    public function verifyBillingIPN(Request $request)
    {
        $txnType = $request->get('txn_type');
        if ($txnType == 'recurring_payment') {

            $recurringPaymentId = $request->get('recurring_payment_id');
            $eventId = $request->get('ipn_track_id');

            $event = PaymentTranscation::online()->withoutGlobalScope(CompanyScope::class)->paypal()->where('invoice_id', $eventId)->count();

            if ($event == 0) {
                $payment =  PaymentTranscation::online()->withoutGlobalScope(CompanyScope::class)->paypal()->where('transcation_id', $recurringPaymentId)->first();

                $today = Carbon::now();
                $company = Company::findOrFail($payment->company_id);
                if ($company->package_type == 'annual') {
                    $nextPaymentDate = $today->addYear();
                } else if ($company->package_type == 'monthly') {
                    $nextPaymentDate = $today->addMonth();
                }

                $paypalInvoice = new PaymentTranscation();
                $paypalInvoice->payment_method = 'paypal';
                $paypalInvoice->transcation_id = $recurringPaymentId;
                $paypalInvoice->company_id = $payment->company_id;
                // $paypalInvoice->currency_id = $payment->currency_id;
                $paypalInvoice->total = $payment->total;
                $paypalInvoice->invoice_id = $eventId;
                $paypalInvoice->paid_on = $today;
                $paypalInvoice->next_payment_date = $nextPaymentDate;

                $paypalInvoice->response_data = [
                    'status' => 'paid',
                    'plan_id' => $payment->plan_id,
                    'billing_frequency' => $payment->billing_frequency,
                    'billing_interval' => 1,
                ];
                // $paypalInvoice->status = 'paid';
                // $paypalInvoice->plan_id = $payment->plan_id;
                // $paypalInvoice->billing_frequency = $payment->billing_frequency;
                // $paypalInvoice->billing_interval = 1;

                $paypalInvoice->save();

                // Change company status active after payment
                $company->status = 'active';
                $company->save();

                // TODO - Notification
                //                $generatedBy = User::whereNull('company_id')->get();
                //                $lastInvoice = PaypalInvoice::where('company_id')->count();
                //                if($lastInvoice > 1){
                //                    Notification::send($generatedBy, new CompanyUpdatedPlan($company, $payment->plan_id));
                //                }else{
                //                    Notification::send($generatedBy, new CompanyPurchasedPlan($company, $payment->plan_id));
                //                }

                return response('IPN Handled', 200);
            }
        }
    }

    public function saveAuthorizeInvoices(Request $request)
    {

        if ($request->eventType == 'net.authorize.customer.subscription.created') {
            //   // Log::info('PaymentTranscation');
            $subscription = Subscription::authorize()->where('subscription_id', $request->payload['id'])->first();

            $package = SubscriptionPlan::find($subscription->plan_id);

            $company = Company::findOrFail($subscription->company_id);
            $authorizeInvoices = new PaymentTranscation();
            $authorizeInvoices->payment_method = 'authorize';
            $authorizeInvoices->company_id = $subscription->company_id;
            $authorizeInvoices->subscription_plan_id = $subscription->plan_id;
            $authorizeInvoices->transcation_id = $request->payload['profile']['customerPaymentProfileId'];
            $authorizeInvoices->total = $package->{$subscription->plan_type . '_price'};
            $authorizeInvoices->paid_on = Carbon::now()->format('Y-m-d');

            $packageType = $subscription->plan_type;

            if ($packageType == 'monthly') {
                $authorizeInvoices->next_payment_date = Carbon::now()->addMonth()->format('Y-m-d');
            } else {
                $authorizeInvoices->next_payment_date = Carbon::now()->addYear()->format('Y-m-d');
            }
            $authorizeInvoices->save();

            $company->subscription_plan_id = $authorizeInvoices->subscription_plan_id;
            $company->package_type = ($packageType == 'annual') ? 'annual' : 'monthly';
            $company->status = 'active';
            $company->licence_expire_on = null;
            $company->save();

            // TODO - Notification
            //send superadmin notification
            //            $generatedBy = User::allSuperAdmin();
            //            Notification::send($generatedBy, new CompanyUpdatedPlan($company, $company->package_id));
        }
    }

    public function savePaystackInvoices(Request $request)
    {
        // Log::debug($request->all());

        switch ($request['event']) {
            case "subscription.create":
                $user = User::where('email', $request['data']['customer']['email'])->first();

                $subscription = Subscription::paystack()->where('company_id', $user->company_id)->where('customer_id', $request['data']['customer']['customer_code'])->first();
                if ($subscription) {
                    $subscription->subscription_id = $request['data']['subscription_code'];
                    $subscription->token = $request['data']['email_token'];
                    $subscription->plan_id = $request['data']['plan']['plan_code'];
                    $subscription->status = 'active';
                } else {
                    $subscription = new Subscription();
                    $subscription->payment_method = 'paystack';
                    $subscription->company_id = $user->company_id;
                    $subscription->subscription_id = $request['data']['subscription_code'];
                    $subscription->token = $request['data']['email_token'];
                    $subscription->customer_id = $request['data']['customer']['customer_code'];
                    $subscription->plan_id = $request['data']['plan']['plan_code'];
                }
                $subscription->save();
                break;
            case "subscription.disable":
                $user = User::where('email', $request['data']['customer']['email'])->first();
                $subscription = Subscription::paystack()->where('company_id', $user->company_id)->where('subscription_id', $request['data']['subscription_code'])->first();
                if ($subscription) {
                    $subscription->status = 'inactive';
                    $subscription->save();
                }
                break;
            default:
                echo "Wrong event";
        }
    }

    private function getPaystackSubscriptionDetails($subscriptionCode)
    {
        $authBearer = 'Bearer ' . config('paystack.secretKey');

        $this->client = new Client(
            [
                'base_uri' => Config::get('paystack.paymentUrl'),
                'headers' => [
                    'Authorization' => $authBearer,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json'
                ]
            ]
        );

        $response = $this->client->{'get'}(
            Config::get('paystack.paymentUrl') . '/subscription/' . $subscriptionCode
        );

        return $response;
    }



    public function saveUpiInvoices(Request $request)
    {


        $key = "2fd5954a-1e7d-4d7e-a8e7-c7cba1b7b8f5"; // Your Api Token https://merchant.upigateway.com/user/api_credentials
        $client_txn_id = $request->client_txn_id; // you will get client_txn_id in GET Method
        $txn_date = date("d-m-Y"); // date of transaction


        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://merchant.upigateway.com/api/check_order_status',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode([
                'key' => $key,
                'client_txn_id' => $client_txn_id,
                'txn_date' => $txn_date,
            ]),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));
        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to check order status.',

            ], 400);
        }

        $result = json_decode($response, true);

        if ($result['status'] == true) {
            // Txn Status = 'created', 'scanning', 'success','failure'
            $data = $result['data'];
            [$companyId] = Hashids::decode($data['udf2']);

            if ($result['data']['status'] == 'success') {

                // All the Process you want to do after successful payment
                // Please also check the txn is already success in your database.

                $payment = new UpiPayment();
                $payment->id = $data['id'];
                $payment->company_id = $companyId;
                $payment->customer_vpa = $data['customer_vpa'];
                $payment->amount = $data['amount'];
                $payment->client_txn_id = $data['client_txn_id'];
                $payment->customer_name = $data['customer_name'];
                $payment->customer_email = $data['customer_email'];
                $payment->customer_mobile = $data['customer_mobile'];
                $payment->p_info = $data['p_info'];
                $payment->upi_txn_id = $data['upi_txn_id'];
                $payment->status = $data['status'];
                $payment->remark = $data['remark'];
                $payment->plan_id = $data['udf1'];
                $payment->udf2 = $data['udf2'];
                $payment->udf3 = $data['udf3'];
                $payment->redirect_url = $data['redirect_url'];
                $payment->txnAt = $data['txnAt'];
                $payment->createdAt = $data['createdAt'];
                $payment->merchant_name = $data['Merchant']['name'];
                $payment->merchant_upi_id = $data['Merchant']['upi_id'];
                $payment->save();

                return response('
            <html>
            <head>
                <style>
                    /* Add your desired CSS styles here */
                    body {
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        height: 100vh;
                    }

                    .container {
                        text-align: center;
                        animation: fade-in 1s ease-in-out;
                    }

                    h1 {
                        color: green;
                        font-size: 24px;
                    }

                    .button {
                        padding: 10px 20px;
                        background-color: green;
                        color: #fff;
                        text-decoration: none;
                        border-radius: 5px;
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <h1 class="animated">Payment successful!</h1>
                    <a href="javascript:void(0);" onclick="goBack()" class="button">Go Back</a>
                </div>
                <script>
                    // Add your desired JavaScript animations here
                    // Example: Add a class to apply a CSS animation
                    setTimeout(function() {
                        document.querySelector(".animated").classList.add("bounce");
                    }, 1000);

                    function goBack() {
                        window.close();
                    }
                </script>
            </body>
            </html>
        ', 200)->header('Content-Type', 'text/html');
            } else {
                return response('
            <html>
            <head>
                <style>
                    /* Add your desired CSS styles here */
                    body {
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        height: 100vh;
                    }

                    .container {
                        text-align: center;
                        animation: fade-in 1s ease-in-out;
                    }

                    h1 {
                        color: red;
                        font-size: 24px;
                    }

                    .button {
                        padding: 10px 20px;
                        background-color: red;
                        color: #fff;
                        text-decoration: none;
                        border-radius: 5px;
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <h1 class="animated">Payment Canceled!</h1>
                    <a href="javascript:void(0);" onclick="goBack()" class="button">Go Back</a>
                </div>
                <script>
                    // Add your desired JavaScript animations here
                    // Example: Add a class to apply a CSS animation
                    setTimeout(function() {
                        document.querySelector(".animated").classList.add("bounce");
                    }, 1000);

                    function goBack() {
                        window.close();
                    }
                </script>
            </body>
            </html>
        ', 200)->header('Content-Type', 'text/html');
            }

            if ($data['udf3'] != 'order') {
                try {
                    [$plansId] = Hashids::decode($data['udf1']);
                    $planDetails = SubscriptionPlan::find($plansId);
                } catch (\Throwable $th) {
                    //throw $th;
                }

                $offlineRequest = new PaymentTranscation();
                $company = Company::find($companyId);

                $expireDate = null;
                $planType = 'monthly';
                if ($data['amount'] == $planDetails->monthly_price) {
                    $today = Carbon::today();
                    $expireDate = $today->addMonth()->format('Y-m-d');
                    $planType = 'monthly';
                }
                // else if ($data['amount'] == $planDetails->annual_price) {
                //     $today = Carbon::today();
                //     $expireDate = $today->addYear()->format('Y-m-d');
                //     $planType = 'annual';
                // }
                $company->subscription_plan_id =  $plansId;
                $company->package_type = $planType;

                // Set company status active
                $company->status = 'active';
                $company->licence_expire_on = $expireDate;
                $company->save();

                $offlineRequest->company_id = $companyId;
                $offlineRequest->plan_type = $planType;
                $offlineRequest->payment_method = 'upi';
                $offlineRequest->subscription_plan_id =  $plansId;
                $offlineRequest->next_payment_date = $expireDate;
                $offlineRequest->paid_on = Carbon::today()->format('Y-m-d');
                $offlineRequest->total = $data['amount'];
                $offlineRequest->save();
            }
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to check order status.',
            ], 400);
        }
    }
}
