<?php

namespace App\Http\Controllers\Api;

use App\Classes\Common;
use App\Http\Controllers\ApiBaseController;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\ProfileRequest;
use App\Http\Requests\Api\Auth\RefreshTokenRequest;
use App\Http\Requests\Api\Auth\UploadFileRequest;
use App\Models\AutoSyncLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Company;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Expense;
use App\Models\Lang;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentMode;
use App\Models\Product;
use App\Models\Settings;
use App\Models\Translation;
use App\Models\User;
use App\Models\Warehouse;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Examyou\RestAPI\ApiResponse;
use Examyou\RestAPI\Exceptions\ApiException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\SuperAdmin\Notifications\Front\NewUserOTP;
use App\SuperAdmin\Models\GlobalCompany;
use App\Models\HelpVideo;
use App\Models\OrderShippingAddress;
use App\Models\UserDevices;
use Illuminate\Support\Facades\Hash as FacadesHash;
use Laravel\Socialite\Facades\Socialite;
use Vinkla\Hashids\Facades\Hashids;

class AuthController extends ApiBaseController
{

    public function companySetting()
    {
        $settings = Company::first();

        return ApiResponse::make('Success', [
            'global_setting' => $settings,
        ]);
    }

    public function emailSettingVerified()
    {
        $emailSettingVerified = Settings::where('setting_type', 'email')
            ->where('status', 1)
            ->where('verified', 1)
            ->count();

        return $emailSettingVerified > 0 ? 1 : 0;
    }

    public function app()
    {

        $company = company(true);
        $addMenuSetting = $company ? Settings::where('setting_type', 'shortcut_menus')->first() : null;
        $totalPaymentModes = PaymentMode::count();
        $totalCurrencies = Currency::count();
        $totalWarehouses = Warehouse::count();


        return ApiResponse::make('Success', [
            'app' => $company,
            'shortcut_menus' => $addMenuSetting,
            'email_setting_verified' => $this->emailSettingVerified(),
            'total_currencies' => $totalCurrencies,
            'total_warehouses' => $totalWarehouses,
            'total_payment_modes' => $totalPaymentModes
        ]);
    }

    public function checkSubscriptionModuleVisibility()
    {
        $request = request();
        $itemType = $request->item_type;

        $visible = Common::checkSubscriptionModuleVisibility($itemType);

        return ApiResponse::make('Success', [
            'visible' => $visible,
        ]);
    }

    public function visibleSubscriptionModules()
    {

        $visibleSubscriptionModules = Common::allVisibleSubscriptionModules();

        return ApiResponse::make('Success', $visibleSubscriptionModules);
    }

    public function allEnabledLangs()
    {
        $allLangs = Lang::select('id', 'name', 'key', 'image')->where('enabled', 1)->get();

        return ApiResponse::make('Success', [
            'langs' => $allLangs
        ]);
    }

    public function pdf($uniqueId, $langKey = "en")
    {
        $order = Order::with(['warehouse', 'user', 'items', 'items.product', 'items.unit', 'orderPayments:id,order_id,payment_id,amount', 'orderPayments.payment:id,payment_mode_id', 'orderPayments.payment.paymentMode:id,name'])
            ->where('unique_id', $uniqueId)
            ->first();

        $lang = Lang::where('key', $langKey)->first();
        if (!$lang) {
            $lang = Lang::where('key', 'en')->first();
        }

        $invoiceTranslation = Translation::where('lang_id', $lang->id)
            ->where('group', 'invoice')
            ->pluck('value', 'key')
            ->toArray();

        $pdfData = [
            'order' => $order,
            'company' => Company::with('currency')->where('id', $order->company_id)->first(),
            'dateTimeFormat' => 'd-m-Y',
            'traslations' => $invoiceTranslation
        ];


        $html = view('pdf', $pdfData);

        $pdf = app('dompdf.wrapper');
        $pdf->loadHTML($html);
        return $pdf->download();
    }

    public function viewpdf($uniqueId, $langKey = "en")
    {
        $order = Order::with(['warehouse', 'user', 'items', 'items.product', 'items.unit', 'orderPayments:id,order_id,payment_id,amount', 'orderPayments.payment:id,payment_mode_id', 'orderPayments.payment.paymentMode:id,name'])
            ->where('unique_id', $uniqueId)
            ->first();

        $lang = Lang::where('key', $langKey)->first();
        if (!$lang) {
            $lang = Lang::where('key', 'en')->first();
        }

        $invoiceTranslation = Translation::where('lang_id', $lang->id)
            ->where('group', 'invoice')
            ->pluck('value', 'key')
            ->toArray();

        $pdfData = [
            'order' => $order,
            'company' => Company::with('currency')->where('id', $order->company_id)->first(),
            'dateTimeFormat' => 'd-m-Y',
            'traslations' => $invoiceTranslation
        ];

        $html = view('pdf', $pdfData);

        $pdf = app('dompdf.wrapper');
        $pdf->loadHTML($html);
        return $html;
    }

    public function login(LoginRequest $request)
    {

        // Removing all sessions before login
        session()->flush();

        $phone = "";
        $email = "";

        $credentials = [
            'password' => $request->password
        ];

        if (is_numeric($request->get('email'))) {
            $credentials['phone'] = $request->email;
            $phone = $request->email;
        } else {
            $credentials['email'] = $request->email;
            $email = $request->email;
        }

        // For checking user
        $user = User::select('*');
        if ($email != '') {
            $user = $user->where('email', $email);
        } else if ($phone != '') {
            $user = $user->where('phone', $phone);
        }
        $user = $user->first();

        // Adding user type according to email/phone
        if ($user) {
            $credentials['user_type'] = 'staff_members';
            $credentials['is_superadmin'] = 0;
            $userCompany = Company::where('id', $user->company_id)->first();
        }


        if (!$token = auth('api')->attempt($credentials)) {
            throw new ApiException('These credentials do not match our records.');
        } else if ($userCompany->status === 'pending') {
            throw new ApiException('Your company not verified.');
        } else if ($userCompany->status === 'inactive') {
            throw new ApiException('Company account deactivated.');
        } else if (auth('api')->user()->status === 'waiting') {
            throw new ApiException('User not verified.');
        } else if (auth('api')->user()->status === 'disabled') {
            throw new ApiException('Account deactivated.');
        }
        $warehouseId = $this->getWarehouseId();
        $company = company();
        $response = $this->respondWithToken($token);
        $addMenuSetting = Settings::where('setting_type', 'shortcut_menus')->first();
        $helpvideos = HelpVideo::where('status', 1)->get();
        $response['app'] = $company;
        $response['shortcut_menus'] = $addMenuSetting;
        $response['email_setting_verified'] = $this->emailSettingVerified();
        $response['visible_subscription_modules'] = Common::allVisibleSubscriptionModules($warehouseId);
        $response['helpvideos'] = $helpvideos;

        return ApiResponse::make('Logged in successfull1', $response);
    }

    public function sendOTP(Request $request)
    {
        $globalCompany = GlobalCompany::first();
        $phone = $request->phone;
        $otp = mt_rand(100000, 999999);
        $countryCode = $request->country_code;
        $sms = $this->sendSMS($phone, $otp, $countryCode);
        $whatsapp = $this->sendWhatsApp($phone, $otp, $countryCode);
        $notficationData = [
            'mobile' => $request->phone,
            'name' => $request->name,
            'email' => $request->email,
            'otp' => $otp,
        ];
        Notification::route('mail', $request->email)->notify(new NewUserOTP($notficationData));


        DB::table('users')->where('mobile', $phone)->where('email', $request->email)->update(['mobile_otp' => $otp]);

        if ($sms == 1 || $whatsapp == 1) {
            // return ApiResponse::make('OTP sent successfully');
            return response()->json(["status" => 'success', "message" => 'OTP sent successfully']);
        } else {
            // return ApiResponse::make('Please try again sometimes.');
            return response()->json(["status" => 'error', "message" => 'Please try again sometimes.']);
        }
    }



    public function resetPassword(Request $request)
    {
        $globalCompany = GlobalCompany::first();
        $email = $request->email;
        $user = User::select('*');
        if ($email != '') {
            $user = $user->where('email', $email);
        }
        $user = $user->first();
        if ($user) {
            $mobile = $user->mobile;
            $otp = mt_rand(100000, 999999);
            $countryCode = $user->country_code;
            $sms = $this->sendSMS($mobile, $otp, $countryCode);
            $whatsapp = $this->sendWhatsApp($mobile, $otp, $countryCode);
            $notficationData = [
                'mobile' => $mobile,
                'name' => $user->name,
                'email' => $user->email,
                'otp' => $otp,
            ];
            // // Log::info("OTP" . $otp);
            Notification::route('mail',  $user->email)->notify(new NewUserOTP($notficationData));


            if ($mobile) {
                DB::table('users')->where('mobile', $mobile)->update(['mobile_otp' => $otp]);
            } else {
                return response()->json(["status" => 'error', "message" => 'Mobile number Required.']);
            }

            if ($sms == 1 || $whatsapp == 1) {
                // return ApiResponse::make('OTP sent successfully');
                return response()->json(["status" => 'success', "message" => 'OTP sent successfully']);
            } else {
                // return ApiResponse::make('Please try again sometimes.');
                return response()->json(["status" => 'error', "message" => 'Please try again sometimes.']);
            }
        } else {
            // return ApiResponse::make('Please try again sometimes.');
            return response()->json(["status" => 'error', "message" => 'We regret to inform you that the email address you provided is not associated with any account in our records. ']);
        }
    }


    public function resetPasswordFront(Request $request)
    {
        $email = $request->email;
        $user = User::select('*');
        if ($email != '') {
            $user = $user->where('email', $email)->where('company_id', $request->company_id);
        }
        $user = $user->first();
        if ($user) {
            $mobile = $user->mobile;
            $otp = mt_rand(100000, 999999);
            $countryCode = $user->country_code;
            $sms = $this->sendSMS($mobile, $otp, $countryCode);
            $whatsapp = $this->sendWhatsApp($mobile, $otp, $countryCode);
            $notficationData = [
                'mobile' => $mobile,
                'name' => $user->name,
                'email' => $user->email,
                'otp' => $otp,
            ];
            // // Log::info("OTP" . $otp);
            Notification::route('mail',  $user->email)->notify(new NewUserOTP($notficationData));



            DB::table('users')->where('email', $email)->update(['mobile_otp' => $otp]);


            if ($sms == 1 || $whatsapp == 1) {
                // return ApiResponse::make('OTP sent successfully');
                return response()->json(["status" => 'success', "message" => 'OTP sent successfully']);
            } else {
                // return ApiResponse::make('Please try again sometimes.');
                return response()->json(["status" => 'error', "message" => 'Please try again sometimes.']);
            }
        } else {
            // return ApiResponse::make('Please try again sometimes.');
            return response()->json(["status" => 'error', "message" => 'We regret to inform you that the email address you provided is not associated with any account in our records. ']);
        }
    }

    public function resetpassword_save(Request $request)
    {
        $email = $request->email;
        $user = User::select('*');
        if ($email != '') {
            $user = $user->where('email', $email);
        }
        $user = $user->first();
        if ($user) {
            $id = $user->id;
            $mobile = $user->mobile;
            $password = FacadesHash::make($request->password);

            DB::table('users')->where('id', $id)->update(['password' => $password]);
            $message_client = "Dear" . $user->name . ",

            We are writing to confirm that your password has been successfully reset for your JnanaERP account.

            Please keep this email for your records. If you did not request a password reset, please contact our support team immediately at erp@jnanain.com.

            To ensure the security of your account, we recommend taking the following steps:

            Create a strong and unique password that is not easily guessable.
            Avoid using the same password for multiple accounts.
            Enable two-factor authentication for an added layer of security.

            Thank you for choosing JnanaERP. We are committed to providing a secure and reliable experience for all our users.

            Best regards,
            JnanaERP Support";
            $whatsapp = $this->sendWhatsApp_1($mobile, $message_client);
            return response()->json(["message" => 'Password Changed Successfully']);
        } else {
            // return ApiResponse::make('Please try again sometimes.');
            return response()->json(["message" => 'Please try again sometimes.']);
        }
    }




    public function getCountries()
    {
        $countrycode = DB::table('countries')
            ->select('id', 'iso', 'phonecode', 'name', 'nicename')
            ->whereNotNull('iso3')
            ->get();

        return response()->json(['data' => $countrycode, 'message' => 'Data  Recived Successfully!', 'status' => 200], 200);
    }

    public function UpdateContact(Request $request)
    {

        $getMobile = Auth::user()->mobile;
        $getEmail = Auth::user()->email;
        $mobile = $request->input('mobile');
        $email = $request->input('email');

        if ($mobile && $email) {
            DB::table('users')
                ->where('mobile', $mobile)
                ->orWhere('email', $getEmail)
                ->update(['mobile' => $mobile]);
            DB::table('users')
                ->where('email', $email)
                ->orWhere('mobile', $mobile)
                ->update(['email' => $email]);
            $user = User::select('*');
            $user = $user->where('email', $email)->first();
            if ($user) {
                $otp = $user->mobile_otp;
                $countryCode = $user->country_code;
                $sms = $this->sendSMS($mobile, $otp, $countryCode);
            }
            return response()->json(["message" => 'Mobile & Email Updated successfully']);
        } else if ($mobile) {
            DB::table('users')
                ->where('mobile', $mobile)
                ->orWhere('email', $getEmail)
                ->update(['mobile' => $mobile]);
            return response()->json(["message" => 'Mobile Updated successfully']);
        } else if ($email) {
            DB::table('users')
                ->where('email', $email)
                ->orWhere('mobile', $getMobile)
                ->update(['email' => $email]);
            return response()->json(["message" => 'Email Updated successfully']);
        }
    }
    private function sendSMS($phone, $otp, $countryCode)
    {
        $url = 'https://api.authkey.io/request';
        $authkey = 'e543b8ad701e8670';
        $mobile = $phone;
        $country_code = $countryCode;
        $sid = '9102';
        $name = 'URESTR';
        $otpMessage = 'Hi, ' . $otp . ' is your One Time Password on onlineshop. Please use this password to login your account. Jnana In';
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
            // Handle the HTTP request failure
            return 0;
        } else {
            // Request was successful
            return 1;
        }
    }

    private function sendWhatsApp($phone, $otp, $countryCode)
    {
        $url = 'https://wa.yourestore.in/chat/sendmessage';
        $data = [
            'number' => $phone,
            'message' => 'Please use the following One-Time Password (OTP) for verification: ' . $otp,
            'client' => '9445526095',
            'country_code' => $countryCode,
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            // Handle the cURL error
            return 0;
        } else {
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($statusCode >= 200 && $statusCode < 300) {
                // Request was successful
                return 1;
            } else {
                // Request failed
                return 0;
            }
        }

        curl_close($ch);
    }

    public function verifyOTP_resetpassword(Request $request)
    {
        // $phone = Auth::user()->phone;
        $otp = $request->otp;
        $email = $request->email;
        $user = User::select('*');
        if ($email != '') {
            $user = $user->where('email', $email);
        }
        $user = $user->first();
        if ($user) {
            $mobile = $user->mobile;
        }

        $getItem = User::where(['mobile' => $mobile, 'email' => $request->email, 'mobile_otp' => $request->otp])->first();
        if (isset($getItem) && !empty($getItem)) {
            //     //DB::table('users')->where('mobile', $mobile)->update(['is_verified' => true]);

            return response()->json(['error' => false, 'message' => 'OTP verification successful.'], 200);
        } else {
            return response()->json(['error' => true, 'message' => 'Invalid OTP.'], 200);
        }
    }

    public function verifyOTP(Request $request)
    {
        // $phone = Auth::user()->phone;
        $otp = $request->otp;


        $getItem = User::where(['mobile' => $request->mobile, 'email' => $request->email, 'mobile_otp' => $request->otp])->first();
        if (isset($getItem) && !empty($getItem)) {
            DB::table('users')->where('mobile', $request->mobile)->update(['is_verified' => true]);
            $message_client = "Hello,

We appreciate your interest in JnanaERP, the top AI-based cloud POS and ERP solution in India. Your contact from our sales representative will come soon.

Check out our merchandise while you wait. I'm eager to welcome you to the Jnana Community.";
            $whatsapp = $this->sendWhatsApp_1($request->mobile, $message_client);
            return response()->json(['error' => false, 'message' => 'OTP verification successful.'], 200);
        } else {
            return response()->json(['error' => true, 'message' => 'Invalid OTP.']);
        }
    }

    private function sendWhatsApp_1($phone, $message)
    {
        $url = 'https://wa.yourestore.in/chat/sendmessage';
        $data = [
            'number' => $phone,
            'message' => $message,
            'client' => '9445526095',
            'country_code' => '',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            // Handle the cURL error
            return 0;
        } else {
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($statusCode >= 200 && $statusCode < 300) {
                // Request was successful
                return 1;
            } else {
                // Request failed
                return 0;
            }
        }

        curl_close($ch);
    }

    protected function respondWithToken($token)
    {
        $user = user();
        [$user_id] = Hashids::decode($user->xid);
        $warehouse_latest = Warehouse::where(['id' => $user->warehouse_id])->orderBy('id', 'desc')->first();
        $lastorder = Order::where(['staff_user_id' => $user_id, 'order_type' => 'sales'])->where('invoice_type', 'pos-off')->orderBy('id', 'desc')->first();

        if ($lastorder) {
            $invoice_spliter = $warehouse_latest->invoice_spliter;

            $lastorder = explode($invoice_spliter, $lastorder);
            $last_order = $lastorder[1];
        } else {
            $last_order = 1;
        }
        return [
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Carbon::now()->addDays(180),
            'user' => $user,
            'user_id' => $user_id,
            'invoice_no' => (float) $last_order,
        ];
    }

    public function logout()
    {
        $request = request();
        $user = user();

        if (auth('api')->user() && $request->bearerToken() != '') {
            auth('api')->logout();
            $is_token = UserDevices::where('token', $request->bearerToken())->first();
            $is_refresh_token = UserDevices::where('refresh_token', $request->bearerToken())->first();

            if ($is_refresh_token) {
                UserDevices::where('refresh_token', $request->bearerToken())->delete();
            } else if ($is_token) {
                UserDevices::where('token', $request->bearerToken())->delete();
            }
        }
        if ($request->device_id) {

            $deviceUser = UserDevices::where('user_id', $this->getIdFromHash($request->id))->where('device_id', $request->device_id)->first();
            if ($deviceUser) {
                UserDevices::where('user_id', $this->getIdFromHash($request->id))->where('device_id', $request->device_id)->delete();
            }
        }


        session()->flush();
        return ApiResponse::make(__('Session closed successfully'));
    }

    public function user()
    {
        $user = auth('api')->user();
        $user = $user->load('role', 'role.perms', 'warehouse');

        session(['user' => $user]);

        return ApiResponse::make('Data successfull', [
            'user' => $user
        ]);
    }

    public function refreshToken(RefreshTokenRequest $request)
    {

        //  $newToken = auth('api')->refresh();


        // $response = $this->respondWithToken($newToken);
        $response = $request->token;

        return ApiResponse::make('Token fetched successfully', $response);
    }

    public function uploadFile(UploadFileRequest $request)
    {
        $result = Common::uploadFile($request);

        return ApiResponse::make('File Uploaded', $result);
    }

    public function profile(ProfileRequest $request)
    {
        $user = auth('api')->user();

        // In Demo Mode
        if (env('APP_ENV') == 'production') {
            $request = request();

            if ($request->email == 'admin@example.com' && $request->has('password') && $request->password != '') {
                throw new ApiException('Not Allowed In Demo Mode');
            }
        }

        $user->name = $request->name;
        if ($request->has('profile_image')) {
            $user->profile_image = $request->profile_image;
        }
        if ($request->password != '') {
            $user->password = $request->password;
        }
        $user->phone = $request->phone;
        $user->address = $request->address;
        $user->save();

        return ApiResponse::make('Profile updated successfull', [
            'user' => $user->load('role', 'role.perms')
        ]);
    }

    public function langTrans()
    {
        $langs = Lang::with('translations')->get();

        return ApiResponse::make('Langs fetched', [
            'data' => $langs
        ]);
    }

    public function dashboard(Request $request)
    {
        $data = [
            'topSellingProducts' => $this->getTopProducts(),
            'purchaseSales' => $this->getPurchaseSales(),
            'stockAlerts' => $this->getStockAlerts(5),
            'topCustomers' => $this->getSalesTopCustomers(),
            'stockHistoryStatsData' => $this->getStockHistoryStatsData(),
            'stateData' => $this->getStatsData(),
            'paymentChartData' => $this->getPaymentChartData(),
            'pendingPaymentToSend' => $this->getPendingPayments(),
            'pendingPaymentToReceive' => $this->getPendingSalesPayments(),
            'leastSellingProducts' => $this->getLeastSellingProducts(),
            'saleData' => $this->getSaleData(),

        ];

        return ApiResponse::make('Data fetched', $data);
    }

    public function stockAlerts()
    {
        $data = [
            'stockAlerts' => $this->getStockAlerts(),
        ];

        return ApiResponse::make('Data fetched', $data);
    }

    public function getStockAlerts($limit = null)
    {
        $request = request();
        $warehouseId = $this->getWarehouseId();

        $warehouseStocks = Product::select('products.id as product_id', 'products.name as product_name', 'product_details.current_stock', 'product_details.stock_quantitiy_alert', 'units.short_name')
            ->join('product_details', 'product_details.product_id', '=', 'products.id')
            ->join('units', 'units.id', '=', 'products.unit_id')
            ->whereNotNull('product_details.stock_quantitiy_alert')
            ->whereRaw('product_details.current_stock <= product_details.stock_quantitiy_alert');

        // If user not have admin role
        // then he can only view reords
        // of warehouse assigned to him
        $warehouseStocks = $warehouseStocks->where('product_details.warehouse_id', '=', $warehouseId);

        if ($request->has('product_id') && $request->product_id != null) {
            $productId = $this->getIdFromHash($request->product_id);
            $warehouseStocks = $warehouseStocks->where('product_details.product_id', '=', $productId);
        }
        if ($limit != null) {
            $warehouseStocks = $warehouseStocks->take($limit);
        }
        $warehouseStocks = $warehouseStocks->get();

        return $warehouseStocks;
    }

    public function getSaleData()
    {
        $request = request();
        $company = company();
        $timezone = $company->timezone;
        $warehouseId = $this->getWarehouseId();


        $sales = DB::table('orders')
            ->selectRaw('HOUR(order_date_local) as hour, ROUND(SUM(subtotal), 2) as total_sales, COUNT(*) as order_count, ROUND(AVG(subtotal), 2) as average_order')
            ->whereDate('order_date_local', Carbon::now()->tz($timezone)->toDateString()) // Filter by today's date
            ->where('orders.warehouse_id', $warehouseId)
            ->where(function ($query) {
                $query->where('orders.order_type', 'sales')
                    ->orWhere('orders.order_type', 'online-orders');
            })
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $averageOrderValue = DB::table('orders')
            ->whereDate('order_date_local', Carbon::now()->tz($timezone)->toDateString())
            ->where('orders.warehouse_id', $warehouseId)
            ->where(function ($query) {
                $query->where('orders.order_type', 'sales')
                    ->orWhere('orders.order_type', 'online-orders');
            })
            ->avg('subtotal');


        $sales_category = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereDate('orders.created_at', Carbon::now()->tz($timezone))
            ->where('orders.warehouse_id', $warehouseId)
            ->where(function ($query) {
                $query->where('orders.order_type', 'sales')
                    ->orWhere('orders.order_type', 'online-orders');
            })
            ->select(
                'categories.name as category_name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.subtotal) as total_value')
            )
            ->groupBy('categories.name')
            ->get();


        //     $sales = DB::table('orders')
        //     ->selectRaw('HOUR(order_date_local) as hour, ROUND(SUM(subtotal), 2) as total_sales, COUNT(*) as order_count, ROUND(AVG(subtotal), 2) as average_order')
        //     ->whereDate('order_date_local',  Carbon::now()->tz($timezone)->toDateString()) // Filter by today's date
        //     ->where('orders.warehouse_id', $warehouseId)
        //     ->where('orders.order_type', 'sales')
        //     ->groupBy('hour')
        //     ->orderBy('hour')
        //     ->get();

        // $averageOrderValue = DB::table('orders')
        //     ->whereDate('order_date_local', Carbon::now()->tz($timezone)->toDateString())
        //     ->where('orders.warehouse_id', $warehouseId)
        //     ->where('orders.order_type', 'sales')
        //     ->avg('subtotal');

        //     $sales_category = DB::table('order_items')
        //     ->join('products', 'order_items.product_id', '=', 'products.id')
        //     ->join('orders', 'order_items.order_id', '=', 'orders.id')
        //     ->join('categories', 'products.category_id', '=', 'categories.id')
        //     ->whereDate('orders.created_at', Carbon::now()->tz($timezone))
        //     ->where('orders.warehouse_id', $warehouseId)
        //     ->where('orders.order_type', 'sales')
        //     ->select(
        //         'categories.name as category_name',
        //         DB::raw('SUM(order_items.quantity) as total_quantity'),
        //         DB::raw('SUM(order_items.subtotal) as total_value')
        //     )
        //     ->groupBy('categories.name')
        //     ->get();

        return [
            'totalSalesData' => $sales,
            'average' => round($averageOrderValue, 2),
            'category_sale' => $sales_category,

        ];
    }

    public function getStatsData()
    {
        $request = request();
        $warehouseId = $this->getWarehouseId();

        // Total Purchase
        // $totalSalesAmount = Order::where('order_type', 'sales');

        // Total Sales
        $totalSalesAmount = Order::where(function ($query) {
            $query->where('order_type', 'sales')
                ->orWhere('order_type', 'online-orders');
        });
        // Total Expenses
        $totalExpenses = Expense::select('amount');
        // Payment Sent
        $paymentSent = Payment::where('payments.payment_type', 'out');
        // Payment Received
        $paymentReceived = Payment::where('payments.payment_type', 'in');

        // Warehouse Filter
        if ($warehouseId && $warehouseId != null) {
            $totalSalesAmount = $totalSalesAmount->where('orders.warehouse_id', $warehouseId);
            $totalExpenses = $totalExpenses->where('warehouse_id', $warehouseId);
        }

        // Dates Filters
        if ($request->has('dates') && $request->dates != null && count($request->dates) > 0) {
            $dates = $request->dates;
            $startDate = $dates[0];
            $endDate = $dates[1];

            $totalSalesAmount = $totalSalesAmount->whereRaw('DATE(orders.order_date_local) >= ?', [$startDate])
                ->whereRaw('DATE(orders.order_date) <= ?', [$endDate]);
            $totalExpenses = $totalExpenses->whereRaw('DATE(expenses.date) >= ?', [$startDate])
                ->whereRaw('DATE(expenses.date) <= ?', [$endDate]);
            $paymentSent = $paymentSent->whereRaw('DATE(payments.date) >= ?', [$startDate])
                ->whereRaw('DATE(payments.date) <= ?', [$endDate]);
            $paymentReceived = $paymentReceived->whereRaw('DATE(payments.date) >= ?', [$startDate])
                ->whereRaw('DATE(payments.date) <= ?', [$endDate]);
        }

        $totalSalesAmount = $totalSalesAmount->sum('total');
        $totalExpenses = $totalExpenses->sum('amount');
        $paymentSent = $paymentSent->sum('payments.amount');
        $paymentReceived = $paymentReceived->sum('payments.amount');

        $nosales = Order::where(function ($query) {
            $query->where('order_type', 'sales')
                ->orWhere('order_type', 'online-orders');
        })
            ->whereRaw('DATE(orders.order_date_local) >= ?', [$startDate])
            ->whereRaw('DATE(orders.order_date_local) <= ?', [$endDate]);

        if ($warehouseId && $warehouseId != null) {
            $nosales = $nosales->where('orders.warehouse_id', $warehouseId);
        }

        $nosales = $nosales->count();


        return [
            'totalSales' => $totalSalesAmount,
            'totalExpenses' => $totalExpenses,
            'paymentSent' => $paymentSent,
            'paymentReceived' => $paymentReceived,
            'totalNoSales' => $nosales,
        ];
    }

    public function getLeastSellingProducts()
    {
        $request = request();
        $warehouse = warehouse();
        $warehouseId = $warehouse->id;

        $colors = ["#20C997", "#5F63F2", "#ffa040", "#FFCD56", "#ff6385"];

        $topSellingProducts = $this->getTopProducts();
        $topSellingProductIds = array_column($topSellingProducts['labels'], 'id');

        $leastSellingProducts = OrderItem::select('order_items.product_id', DB::raw('sum(order_items.subtotal) as total_amount'))
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.order_type', 'sales');

        if ($warehouseId && $warehouseId != null) {
            $leastSellingProducts = $leastSellingProducts->where('orders.warehouse_id', $warehouseId);
        }

        if ($request->has('dates') && $request->dates != null && count($request->dates) > 0) {
            $dates = $request->dates;
            $startDate = $dates[0];
            $endDate = $dates[1];

            $leastSellingProducts = $leastSellingProducts->whereRaw('DATE(orders.order_date) >= ?', [$startDate])
                ->whereRaw('DATE(orders.order_date) <= ?', [$endDate]);
        }

        $leastSellingProducts = $leastSellingProducts->whereNotIn('order_items.product_id', $topSellingProductIds)
            ->groupBy('order_items.product_id')
            ->orderByRaw("sum(order_items.subtotal) asc") // Order by ascending subtotal for least selling products
            ->take(5)
            ->get();

        $leastSellingProductsNames = [];
        $leastSellingProductsValues = [];
        $leastSellingProductsColors = [];
        $counter = 0;
        foreach ($leastSellingProducts as $leastSellingProduct) {
            $product = Product::select('name')->find($leastSellingProduct->product_id);

            $leastSellingProductsNames[] = $product->name;
            $leastSellingProductsValues[] = $leastSellingProduct->total_amount;
            $leastSellingProductsColors[] = $colors[$counter];
            $counter++;
        }

        return [
            'labels' => $leastSellingProductsNames,
            'values' => $leastSellingProductsValues,
            'colors' => $leastSellingProductsColors,
        ];
    }

    public function getPaymentChartData()
    {
        $request = request();
        $warehouseId = $this->getWarehouseId();
        if ($request->has('dates') && $request->dates != null && count($request->dates) > 0) {
            $dates = $request->dates;
            $startDate = $dates[0];
            $endDate = $dates[1];
        } else {
            $startDate = Carbon::now()->subDays(30)->format("Y-m-d");
            $endDate = Carbon::now()->format("Y-m-d");
        }

        // Sent Payments
        $allSentPayments = Payment::select(DB::raw('date(payments.date) as date, sum(payments.amount) as total_amount'))
            ->where('payments.payment_type', 'out')
            ->whereRaw('DATE(payments.date) >= ?', [$startDate])
            ->whereRaw('DATE(payments.date) <= ?', [$endDate]);

        // Received Payments
        $allReceivedPayments = Payment::select(DB::raw('date(payments.date) as date, sum(payments.amount) as total_amount'))
            ->where('payments.payment_type', 'in')
            ->whereRaw('DATE(payments.date) >= ?', [$startDate])
            ->whereRaw('DATE(payments.date) <= ?', [$endDate]);



        // Sent Payments
        $allSentPayments = $allSentPayments->groupByRaw('date(payments.date)')
            ->orderByRaw("date(payments.date) asc")
            ->pluck('total_amount', 'date');

        // Received Payments
        $allReceivedPayments = $allReceivedPayments->groupByRaw('date(payments.date)')
            ->orderByRaw("date(payments.date) asc")
            ->pluck('total_amount', 'date');

        $periodDates = CarbonPeriod::create($startDate, $endDate);
        $datesArray = [];
        $sentPaymentsArray = [];
        $receivedPaymentsArray = [];

        // Iterate over the period
        foreach ($periodDates as $periodDate) {
            $currentDate = $periodDate->format('Y-m-d');

            if (isset($allSentPayments[$currentDate]) || isset($allReceivedPayments[$currentDate])) {
                $datesArray[] = $currentDate;
                $sentPaymentsArray[] = isset($allSentPayments[$currentDate]) ? $allSentPayments[$currentDate] : 0;
                $receivedPaymentsArray[] = isset($allReceivedPayments[$currentDate]) ? $allReceivedPayments[$currentDate] : 0;
            }
        }

        return [
            'dates' => $datesArray,
            'sent' => $sentPaymentsArray,
            'received' => $receivedPaymentsArray,
        ];
    }

    public function getStockHistoryStatsData()
    {
        $request = request();
        $warehouseId = $this->getWarehouseId();

        // Total Sales
        $totalSales = OrderItem::join('orders', 'orders.id', '=', 'order_items.order_id')->where('order_type', 'sales');
        // Sales Returns
        $totalSalesReturns = OrderItem::join('orders', 'orders.id', '=', 'order_items.order_id')->where('order_type', 'sales-returns');
        // Purchases
        $totalPurchases = OrderItem::join('orders', 'orders.id', '=', 'order_items.order_id')->where('order_type', 'purchases');
        // Purchase Returns
        $totalPurchaseReturns = OrderItem::join('orders', 'orders.id', '=', 'order_items.order_id')->where('order_type', 'purchase-returns');

        // Warehouse Filter
        if ($warehouseId && $warehouseId != null) {
            $totalSales = $totalSales->where('orders.warehouse_id', $warehouseId);
            $totalSalesReturns = $totalSalesReturns->where('warehouse_id', $warehouseId);
            $totalPurchases = $totalPurchases->where('orders.warehouse_id', $warehouseId);
            $totalPurchaseReturns = $totalPurchaseReturns->where('orders.warehouse_id', $warehouseId);
        }

        // Dates Filters
        if ($request->has('dates') && $request->dates != null && count($request->dates) > 0) {
            $dates = $request->dates;
            $startDate = $dates[0];
            $endDate = $dates[1];

            $totalSales = $totalSales->whereRaw('DATE(orders.order_date) >= ?', [$startDate])
                ->whereRaw('DATE(orders.order_date) <= ?', [$endDate]);
            $totalSalesReturns = $totalSalesReturns->whereRaw('DATE(orders.order_date) >= ?', [$startDate])
                ->whereRaw('DATE(orders.order_date) <= ?', [$endDate]);
            $totalPurchases = $totalPurchases->whereRaw('DATE(orders.order_date) >= ?', [$startDate])
                ->whereRaw('DATE(orders.order_date) <= ?', [$endDate]);
            $totalPurchaseReturns = $totalPurchaseReturns->whereRaw('DATE(orders.order_date) >= ?', [$startDate])
                ->whereRaw('DATE(orders.order_date) <= ?', [$endDate]);
        }

        $totalSales = $totalSales->sum('order_items.quantity');
        $totalPurchases = $totalPurchases->sum('order_items.quantity');
        $totalSalesReturns = $totalSalesReturns->sum('order_items.quantity');
        $totalPurchaseReturns = $totalPurchaseReturns->sum('order_items.quantity');

        return [
            'totalSales' => $totalSales,
            'totalPurchases' => $totalPurchases,
            'totalSalesReturn' => $totalPurchaseReturns,
            'totalPurchaseReturn' => $totalPurchaseReturns,
        ];
    }

    public function getTopProducts()
    {
        $request = request();
        $waehouse = warehouse();
        $warehouseId = $waehouse->id;

        $colors = ["#20C997", "#5F63F2", "#ffa040", "#FFCD56", "#ff6385"];

        $maxSellingProducts = OrderItem::select('order_items.product_id', DB::raw('sum(order_items.subtotal) as total_amount'))
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where(function ($query) {
                $query->where('orders.order_type', 'sales')
                    ->orWhere('orders.order_type', 'online-orders');
            });


        if ($warehouseId && $warehouseId != null) {
            $maxSellingProducts = $maxSellingProducts->where('orders.warehouse_id', $warehouseId);
        }

        if ($request->has('dates') && $request->dates != null && count($request->dates) > 0) {
            $dates = $request->dates;
            $startDate = $dates[0];
            $endDate = $dates[1];

            $maxSellingProducts = $maxSellingProducts->whereRaw('DATE(orders.order_date) >= ?', [$startDate])
                ->whereRaw('DATE(orders.order_date) <= ?', [$endDate]);
        }

        $maxSellingProducts = $maxSellingProducts->groupBy('order_items.product_id')
            ->orderByRaw("sum(order_items.subtotal) desc")
            ->take(5)
            ->get();

        $topSellingProductsNames = [];
        $topSellingProductsValues = [];
        $topSellingProductsColors = [];
        $counter = 0;
        foreach ($maxSellingProducts as $maxSellingProduct) {
            $product = Product::select('name')->find($maxSellingProduct->product_id);

            $topSellingProductsNames[] = $product->name;
            $topSellingProductsValues[] = $maxSellingProduct->total_amount;
            $topSellingProductsColors[] = $colors[$counter];
            $counter++;
        }

        return [
            'labels' => $topSellingProductsNames,
            'values' => $topSellingProductsValues,
            'colors' => $topSellingProductsColors,
        ];
    }

    public function getWarehouseId()
    {
        $waehouse = warehouse();
        $warehouseId = $waehouse->id;

        return $warehouseId;
    }

    public function getSalesTopCustomers()
    {
        $request = request();
        $warehouseId = $this->getWarehouseId();

        $topCustomers = Order::select(DB::raw('sum(orders.total) as total_amount, user_id, count(user_id) as total_sales'))
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->where('orders.order_type', '=', 'sales');

        if ($warehouseId && $warehouseId != null) {
            $topCustomers = $topCustomers->where('orders.warehouse_id', $warehouseId);
        }

        if ($request->has('dates') && $request->dates != null && count($request->dates) > 0) {
            $dates = $request->dates;
            $startDate = $dates[0];
            $endDate = $dates[1];

            $topCustomers = $topCustomers->whereRaw('DATE(orders.order_date) >= ?', [$startDate])
                ->whereRaw('DATE(orders.order_date) <= ?', [$endDate]);
        }

        $topCustomers = $topCustomers->groupByRaw('user_id')
            ->orderByRaw('sum(orders.total) desc')
            ->take(5)
            ->get();

        $results = [];

        foreach ($topCustomers as $topCustomer) {
            $customer = Customer::select('id', 'name', 'profile_image')->find($topCustomer->user_id);

            $results[] = [
                'customer_id' => $customer->xid,
                'customer' => $customer,
                'total_amount' => $topCustomer->total_amount,
                'total_sales' => $topCustomer->total_sales,
            ];
        }

        return $results;
    }


    public function getPendingPayments()
    {
        $request = request();
        $warehouseId = $this->getWarehouseId();

        $topCustomers = Order::select(DB::raw('sum(orders.due_amount) as total_amount, user_id, count(user_id) as total_sales'))
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->where('orders.order_type', '=', 'purchases')
            ->where('orders.due_amount', '!=', 0);


        if ($warehouseId && $warehouseId != null) {
            $topCustomers = $topCustomers->where('orders.warehouse_id', $warehouseId);
        }

        if ($request->has('dates') && $request->dates != null && count($request->dates) > 0) {
            $dates = $request->dates;
            $startDate = $dates[0];
            $endDate = $dates[1];

            $topCustomers = $topCustomers->whereRaw('DATE(orders.order_date) >= ?', [$startDate])
                ->whereRaw('DATE(orders.order_date) <= ?', [$endDate]);
        }

        $topCustomers = $topCustomers->groupByRaw('user_id')
            ->get();



        $results = [];
        // $results['data']=[];
        $results['total'] = 0;
        foreach ($topCustomers as $topCustomer) {

            $customer = Supplier::select('id', 'name', 'profile_image')->find($topCustomer->user_id);

            $results['data'][] = [
                'customer_id' => $customer->xid,
                'customer' => $customer,
                'total_amount' => $topCustomer->total_amount,
                'total_sales' => $topCustomer->total_sales,
            ];
            $results['total'] = +$topCustomer->total_amount;
        }

        return $results;
    }

    public function getPendingSalesPayments()
    {
        $request = request();
        $warehouseId = $this->getWarehouseId();

        $topCustomers = Order::select(DB::raw('sum(orders.due_amount) as total_amount, user_id, count(user_id) as total_sales'))
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->where('orders.order_type', '=', 'sales')
            ->where('orders.due_amount', '!=', 0);


        if ($warehouseId && $warehouseId != null) {
            $topCustomers = $topCustomers->where('orders.warehouse_id', $warehouseId);
        }

        if ($request->has('dates') && $request->dates != null && count($request->dates) > 0) {
            $dates = $request->dates;
            $startDate = $dates[0];
            $endDate = $dates[1];

            $topCustomers = $topCustomers->whereRaw('DATE(orders.order_date) >= ?', [$startDate])
                ->whereRaw('DATE(orders.order_date) <= ?', [$endDate]);
        }

        $topCustomers = $topCustomers->groupByRaw('user_id')
            ->get();



        $results = [];
        $results['total'] = 0;

        foreach ($topCustomers as $topCustomer) {

            $customer = Customer::select('id', 'name', 'profile_image')->find($topCustomer->user_id);

            $results['data'][] = [
                'customer_id' => $customer->xid,
                'customer' => $customer,
                'total_amount' => $topCustomer->total_amount,
                'total_sales' => $topCustomer->total_sales,
            ];
            $results['total'] = +$topCustomer->total_amount;
        }

        return $results;
    }

    public function getPurchaseSales()
    {
        $request = request();
        $warehouseId = $this->getWarehouseId();
        if ($request->has('dates') && $request->dates != null && count($request->dates) > 0) {
            $dates = $request->dates;
            $startDate = $dates[0];
            $endDate = $dates[1];
        } else {
            $startDate = Carbon::now()->subDays(30)->format("Y-m-d");
            $endDate = Carbon::now()->format("Y-m-d");
        }

        $allPurchases = Order::select(DB::raw('date(orders.order_date) as date, sum(orders.total) as total_amount'))
            ->where('orders.order_type', 'purchases')
            ->whereRaw('DATE(orders.order_date) >= ?', [$startDate])
            ->whereRaw('DATE(orders.order_date) <= ?', [$endDate]);
        if ($warehouseId && $warehouseId != null) {
            $allPurchases = $allPurchases->where('orders.warehouse_id', $warehouseId);
        }
        $allPurchases = $allPurchases->groupByRaw('date(orders.order_date)')
            ->orderByRaw("date(orders.order_date) asc")
            ->take(5)
            ->pluck('total_amount', 'date');

        $sales = Order::select(DB::raw('date(orders.order_date) as date, round(sum(orders.total)) as total_amount'))
            ->where('orders.order_type', 'sales')
            ->whereRaw('DATE(orders.order_date) >= ?', [$startDate])
            ->whereRaw('DATE(orders.order_date) <= ?', [$endDate]);



        if ($warehouseId && $warehouseId != null) {
            $sales = $sales->where('orders.warehouse_id', $warehouseId);
        }





        $sales = $sales->groupByRaw('date(orders.order_date)')
            ->orderByRaw("date(orders.order_date) asc")
            ->take(5)
            ->pluck('total_amount', 'date');


        $periodDates = CarbonPeriod::create($startDate, $endDate);
        $datesArray = [];
        $purchasesArray = [];
        $salesArray = [];

        // Iterate over the period
        foreach ($periodDates as $periodDate) {
            $currentDate = $periodDate->format('Y-m-d');

            if (isset($allPurchases[$currentDate]) || isset($sales[$currentDate])) {
                $datesArray[] = $currentDate;
                $purchasesArray[] = isset($allPurchases[$currentDate]) ? $allPurchases[$currentDate] : 0;
                $salesArray[] = isset($sales[$currentDate]) ? $sales[$currentDate] : 0;
            }
        }

        return [
            'dates' => $datesArray,
            'purchases' => $purchasesArray,
            'sales' => $salesArray,

        ];
    }

    public function changeThemeMode(Request $request)
    {
        $mode = $request->has('theme_mode') ? $request->theme_mode : 'light';

        session(['theme_mode' => $mode]);

        if ($mode == 'dark') {
            $company = company();
            $company->left_sidebar_theme = 'dark';
            $company->save();

            $updatedCompany = company(true);
        }

        return ApiResponse::make('Success', [
            'status' => "success",
            'themeMode' => $mode,
            'themeModee' => session()->all(),
        ]);
    }

    public function changeAdminWarehouse(Request $request)
    {
        $user = user();

        $warehouse = $user->hasRole('admin') && $request->has('warehouse_id') && $request->warehouse_id
            ? Warehouse::find(Common::getIdFromHash($request->warehouse_id))
            : $user->warehouse;

        session(['warehouse' => $warehouse]);

        return ApiResponse::make('Success', [
            'status' => "success",
            'warehouse' => $warehouse
        ]);
    }

    public function getAllTimezones()
    {
        $timezones = \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);

        return ApiResponse::make('Success', [
            'timezones' => $timezones,
            'date_formates' => [
                'd-m-Y' => 'DD-MM-YYYY',
                'm-d-Y' => 'MM-DD-YYYY',
                'Y-m-d' => 'YYYY-MM-DD',
                'd.m.Y' => 'DD.MM.YYYY',
                'm.d.Y' => 'MM.DD.YYYY',
                'Y.m.d' => 'YYYY.MM.DD',
                'd/m/Y' => 'DD/MM/YYYY',
                'm/d/Y' => 'MM/DD/YYYY',
                'Y/m/d' => 'YYYY/MM/DD',
                'd/M/Y' => 'DD/MMM/YYYY',
                'd.M.Y' => 'DD.MMM.YYYY',
                'd-M-Y' => 'DD-MMM-YYYY',
                'd M Y' => 'DD MMM YYYY',
                'd F, Y' => 'DD MMMM, YYYY',
                'D/M/Y' => 'ddd/MMM/YYYY',
                'D.M.Y' => 'ddd.MMM.YYYY',
                'D-M-Y' => 'ddd-MMM-YYYY',
                'D M Y' => 'ddd MMM YYYY',
                'd D M Y' => 'DD ddd MMM YYYY',
                'D d M Y' => 'ddd DD MMM YYYY',
                'dS M Y' => 'Do MMM YYYY',
            ],
            'time_formates' => [
                "hh:mm A" => '12 Hours hh:mm A',
                'hh:mm a' => '12 Hours hh:mm a',
                'hh:mm:ss A' => '12 Hours hh:mm:ss A',
                'hh:mm:ss a' => '12 Hours hh:mm:ss a',
                'HH:mm ' => '24 Hours HH:mm:ss',
                'HH:mm:ss' => '24 Hours hh:mm:ss',
            ]
        ]);
    }

    public function getDefaultWalkinCustomer()
    {
        $walkinCustomer = Customer::select('id', 'name')
            ->withoutGlobalScope(CompanyScope::class)
            ->where('is_walkin_customer', '=', 1)
            ->first();

        return ApiResponse::make('Data fetched', [
            'customer' => $walkinCustomer
        ]);
    }

    public function orderget($id)
    {

        $convertedId = Hashids::decode($id);

        if (empty($convertedId)) {
            return ApiResponse::make('Invalid ID', null, 400);
        }

        $order = Order::with('items', 'user', 'warehouse', 'items.product', 'items.product.details', 'items.unit')
            ->where('id', $convertedId[0])
            ->get();
        $orderShippingAddress = OrderShippingAddress::where('order_id', $convertedId[0])->first();
        $currency = Currency::where('company_id', $order[0]->company_id)->first();
        $invoiceTemplate = Company::where('id', $order[0]->company_id)->first()->invoice_template;

        if (!$order) {
            return ApiResponse::make('Order not found', null, 404);
        }
        $order[0]['orderShippingAddress'] = $orderShippingAddress;
        $order[0]['currency'] = $currency;
        $order[0]['invoice_template'] = $invoiceTemplate;

        return response()->json(["data" => $order]);
    }

    public function warehouseget($id)
    {

        $convertedId = Hashids::decode($id);

        if (empty($convertedId)) {
            return ApiResponse::make('Invalid ID', null, 400);
        }

        $order = company::with('warehouses', 'currency')
            ->where('id', $convertedId[0])
            ->get();

        if (!$order) {
            return ApiResponse::make('Order not found', null, 404);
        }

        return response()->json(["data" => $order]);
    }

    public function orderinvoiceget(Request $request)
    {

        if (empty($request->invoice)) {
            return ApiResponse::make('Invalid Invoice', null, 400);
        }
        $company = company();
        $order = Order::with('items', 'user', 'warehouse', 'items.product', 'items.product.details', 'items.unit')
            ->where('invoice_number', $request->invoice)->where('order_type', $request->order_type)->where('company_id', $company->id)
            ->get();

        if (count($order) == 0) {
            return response()->json([
                'message' => 'Invalid Invoice',
                'data' => $order,
            ], 400);
        } else {

            $currency = Currency::where('company_id', $order[0]->company_id)->first();
            $order[0]['currency'] = $currency;
        }

        return response()->json(["data" => $order]);
    }

    public function storeAutoSyncLogs(Request $request)
    {
        $company = company();
        $warehouse = warehouse();
        $user = user();
        $auto_sync_log = new AutoSyncLog();
        $auto_sync_log['company_id'] = $company->id;
        $auto_sync_log['warehouse_id'] = $warehouse->id;
        $auto_sync_log['user_id'] = $user->id;
        $auto_sync_log['sync_date'] = $request->sync_date;
        $auto_sync_log['sync_time'] = $request->sync_time;
        $auto_sync_log['sync_status'] = 1;
        $auto_sync_log['product_count'] = $request->product_count;
        $auto_sync_log['status'] = 1;

        $auto_sync_log->save();

        return ApiResponse::make('Success');
    }
}
