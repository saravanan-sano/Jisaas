<?php

namespace App\SuperAdmin\Http\Controllers\Front;

use App\Classes\Common;
use App\Classes\Output;
use App\Models\HelpVideo;
use App\Models\Settings;
use App\Models\User;
use App\SuperAdmin\Http\Requests\Front\Contact\StoreRequest;
use App\SuperAdmin\Http\Requests\Front\Register\StoreRegisterRequest;
use Carbon\Carbon;
use Examyou\RestAPI\ApiResponse;
use Examyou\RestAPI\Exceptions\ApiException;
use Revolution\Google\Sheets\Facades\Sheets;
use Illuminate\Support\Facades\Http;
use App\Models\Company;
use App\Models\Role;
use App\Models\StaffMember;
use App\Models\SubscriptionPlan;
use App\Models\UserDetails;
use App\Models\Warehouse;
use App\Scopes\CompanyScope;
use App\SuperAdmin\Classes\SuperAdminCommon;
use App\SuperAdmin\Http\Requests\Front\CallToActionRequest;
use App\SuperAdmin\Models\GlobalCompany;
use App\SuperAdmin\Models\GlobalSettings;
use App\SuperAdmin\Notifications\Front\ContactUsEmail;
use App\SuperAdmin\Notifications\Front\NewUserRegistered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\SuperAdmin\Notifications\Front\NewUserOTP;
use App\Models\Currency;
use App\Models\PaymentMode;


class HomeController extends FrontBaseController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function index($slug = null)
    {
        $this->seoDetail = $this->getPageSeoDetails('home');

        // Header Features
        $allHeaderFeatures = GlobalSettings::where('setting_type', 'header_features')
            ->where('name_key', $this->langKey)
            ->first();
        $allHeaderFeatures = $allHeaderFeatures->credentials;
        // $allHeaderFeatures = SuperAdminCommon::addUrlToAllSettings($allHeaderFeatures, 'logo');
        $this->headerFeatures = Common::convertToCollection($allHeaderFeatures);

        // Clients
        $clientsSetting = GlobalSettings::where('setting_type', 'website_clients')
            ->where('name_key', $this->langKey)
            ->first();
        $clients = $clientsSetting->credentials;
        // $clients = SuperAdminCommon::addUrlToAllSettings($clients, 'logo');
        $this->frontClients = Common::convertToCollection($clients);

        // Testimonials
        $testimonialsSetting = GlobalSettings::where('setting_type', 'website_testimonials')
            ->where('name_key', $this->langKey)
            ->first();
        $testimonials = $testimonialsSetting->credentials;
        $this->frontTestimonials = Common::convertToCollection($testimonials);

        // Features With Images
        $featuresSetting = GlobalSettings::where('setting_type', 'website_features')
            ->where('name_key', $this->langKey)
            ->first();
        $features = $featuresSetting->credentials;
        // $features = SuperAdminCommon::addUrlToAllSettings($features, 'image');
        $this->allHomePageFeatures = Common::convertToCollection($features);

        return view('front.home', $this->data);
    }

    public function features()
    {
        // SEO Details
        $this->seoDetail = $this->getPageSeoDetails('features');

        // Breadcrumb Details
        $this->showFullHeader = false;
        $this->breadcrumbTitle = $this->frontSetting->features_text;

        // Features
        $allNewFeatures = GlobalSettings::where('setting_type', 'features_page')
            ->where('name_key', $this->langKey)
            ->first();
        $allFeatures = $allNewFeatures->credentials;
        $this->allFeatures = Common::convertToCollection($allFeatures);

        return view('front.features', $this->data);
    }

    public function pricing(Request $request)
    {
        $host = $_SERVER['HTTP_HOST'];

        if ($host == "localhost:8000" || $host == "7f44-183-82-179-90.ngrok-free.app") {
            $country_code = "IN";
        } else {
            $country_code = isset($_SERVER["HTTP_CF_IPCOUNTRY"]) ? $_SERVER["HTTP_CF_IPCOUNTRY"] : "IN";
        }

        if ($country_code == "IN") {
            $country_code = "IN";
        } else {
            $country_code = "NONIN";
        }

        // SEO Details
        $this->seoDetail = $this->getPageSeoDetails('pricing');

        // Breadcrumb Details
        $this->showFullHeader = false;
        $this->breadcrumbTitle = $this->frontSetting->pricing_text;

        // Subscription Plans
        $this->subscriptionPlans = SubscriptionPlan::where('default', '!=', 'trial')->where('country_code', $country_code)
            ->get();

        // FAQ
        $faqSetting = GlobalSettings::where('setting_type', 'website_faqs')
            ->where('name_key', $this->langKey)
            ->first();
        $allFaqs = $faqSetting->credentials;
        $this->allFaqs = Common::convertToCollection($allFaqs);

        // Pricing Cards
        $pricingCardSettings = GlobalSettings::where('setting_type', 'pricing_cards')
            ->where('name_key', $this->langKey)
            ->first();
        $pricingCardSettings = $pricingCardSettings->credentials;
        $pricingCardSettings = SuperAdminCommon::addUrlToAllSettings($pricingCardSettings, 'logo');
        $this->pricingCards = Common::convertToCollection($pricingCardSettings);

        return view('front.pricing', $this->data);
    }

    public function contact()
    {
        // SEO Details
        $this->seoDetail = $this->getPageSeoDetails('contact');

        // Breadcrumb Details
        $this->showFullHeader = false;
        $this->breadcrumbTitle = $this->frontSetting->contact_text;

        return view('front.contact', $this->data);
    }

    public function submitContactForm(StoreRequest $request)
    {
        $templateSubject = 'New Contact Enquiry';

        $templateContent = '<table><tbody style="color:#0000009c;">
                <tr>
                    <td><p>Name : </p></td>
                    <td><p>' . ucwords($request->name) . '</p></td>
                </tr>
                <tr>
                    <td><p>Email : </p></td>
                    <td><p>' . $request->email . '</p></td>
                </tr>
                <tr>
                    <td style="font-family: Avenir, Helvetica, sans-serif;box-sizing: border-box;min-width: 98px;vertical-align: super;"><p style="font-family: Avenir, Helvetica, sans-serif; box-sizing: border-box; color: #74787E; font-size: 16px; line-height: 1.5em; margin-top: 0; text-align: left;">Message : </p></td>
                    <td><p>' . $request->message . '</p></td>
                </tr>
        </tbody>
</table><br>';

        $emailSettingEnabled = GlobalSettings::withoutGlobalScope(CompanyScope::class)->where('setting_type', 'email')
            ->where('name_key', 'smtp')
            ->first();

        if ($emailSettingEnabled->status == 1 && $emailSettingEnabled->verified == 1) {
            $globalCompany = GlobalCompany::first();

            Notification::route('mail', $globalCompany->email)
                ->notify(new ContactUsEmail($templateSubject, $templateContent));
        }

        return Output::success($this->frontSetting->contact_us_submit_message_text);
    }

    public function page($slug = null)
    {
        $frontPageDetails = null;

        foreach ($this->footerPages as $footerPage) {
            if ($footerPage->slug == $slug) {
                $frontPageDetails = $footerPage;
            }
        }

        $this->frontPageDetails = $frontPageDetails;

        // Breadcrumb Details
        $this->showFullHeader = false;
        $this->breadcrumbTitle = $frontPageDetails->title;

        // SEO Details
        $this->seoDetail = (object) [
            "id" => $frontPageDetails->id,
            "page_key" => $frontPageDetails->slug,
            "seo_title" => $frontPageDetails->title,
            "seo_author" => $this->frontSetting->app_name,
            "seo_keywords" => $frontPageDetails->seo_keywords,
            "seo_description" => $frontPageDetails->seo_description,
            "seo_image" => $this->frontSetting->header_logo,
            "seo_image_url" => $this->frontSetting->header_logo_url
        ];

        return view('front.page', $this->data);
    }


    public function thanks(Request $request)
    {

        $this->country_code = Common::getCountryCode();

        $this->actionEmail = $request->has('email') && $request->email != '' ? $request->email : '';

        // SEO Details
        $this->seoDetail = $this->getPageSeoDetails('register');

        // Breadcrumb Details
        $this->showFullHeader = false;
        $this->breadcrumbTitle = $this->frontSetting->register_text;

        // Header Features
        $allHeaderFeatures = GlobalSettings::where('setting_type', 'header_features')
            ->where('name_key', $this->langKey)
            ->first();
        $allHeaderFeatures = $allHeaderFeatures->credentials;
        $allHeaderFeatures = SuperAdminCommon::addUrlToAllSettings($allHeaderFeatures, 'logo');
        $this->headerFeatures = Common::convertToCollection($allHeaderFeatures);

        return view('front.registerthanks', $this->data);
    }

    public function register(Request $request)
    {



        $this->country_code = Common::getCountryCode();

        $this->actionEmail = $request->has('email') && $request->email != '' ? $request->email : '';

        // SEO Details
        $this->seoDetail = $this->getPageSeoDetails('register');


        // Breadcrumb Details
        $this->showFullHeader = false;
        $this->breadcrumbTitle = $this->frontSetting->register_text;

        // Header Features
        $allHeaderFeatures = GlobalSettings::where('setting_type', 'header_features')
            ->where('name_key', $this->langKey)
            ->first();
        $allHeaderFeatures = $allHeaderFeatures->credentials;
        $allHeaderFeatures = SuperAdminCommon::addUrlToAllSettings($allHeaderFeatures, 'logo');
        $this->headerFeatures = Common::convertToCollection($allHeaderFeatures);



        return view('front.register', $this->data);
    }

    public function saveRegister(StoreRegisterRequest $request)
    {


        session()->flush();
        $company = new Company();

        DB::beginTransaction();
        try {
            $company->name = $request->company_name;
            $company->short_name = Str::lower($request->company_name);
            $company->email = $request->company_email;
            $company->phone = $request->mobile;
            $company->total_users = 1;
            $company->is_global = 0;
            $company->invoice_template = '{
                "header": {
                  "logo": false,
                  "company_name": true,
                  "company_full_name": true,
                  "receipt_title": true,
                  "company_address": true,
                  "company_email": true,
                  "company_no": true,
                  "tax_no": false
                },
                "customer_details": {
                  "name": true,
                  "invoice_no": true,
                  "staff_name": true,
                  "date": true
                },
                "total_details": {
                  "sub_total": true,
                  "discount": true,
                  "service_charges": true,
                  "tax": true,
                  "due": true
                },
                "table_setting": { "mrp": true, "saving_amount": true, "units": false },
                "tax_wise_calculations": { "enabled": false },
                "footer": {
                  "barcode": true,
                  "message": true,
                  "watermark": true,
                  "qr_code": false,
                  "total_item_count": false,
                },
                "thanks_message": "Thanks For Coming Visit again!",
                "company_full_name": ""
              }';
            $company->save();



            $verification_email = Str::random(50);

            $admin = StaffMember::create([
                'company_id' => $company->id,
                'name' => "Admin",
                'email' => $request->company_email,
                'contact_name' => $request->contact_name,
                'mobile' => $request->mobile,
                'country_code' => $request->countryCode,
                'password' => $request->password,
                'user_type' => 'staff_members',
                'email_verification_code' => $verification_email,
                'status' => 'enabled',
                'user_agent' => $request->server('HTTP_USER_AGENT'),
            ]);

            $adminRole = Role::withoutGlobalScope(CompanyScope::class)->where('name', 'admin')->where('company_id', $company->id)->first();

            $admin->role_id = $adminRole->id;
            $admin->save();
            $admin->roles()->attach($adminRole->id);


            if ($request->countryCode == '+91') {
                $rupeeCurrency = new Currency();
                $rupeeCurrency->company_id = $company->id;
                $rupeeCurrency->name = 'Rupee';
                $rupeeCurrency->code = 'INR';
                $rupeeCurrency->symbol = '₹';
                $rupeeCurrency->position = 'front';
                $rupeeCurrency->is_deletable = false;
                $rupeeCurrency->save();
                $company->currency_id = $rupeeCurrency->id;
            } else {
                $newCurrency = new Currency();
                $newCurrency->company_id = $company->id;
                $newCurrency->name = 'Dollar';
                $newCurrency->code = 'USD';
                $newCurrency->symbol = '$';
                $newCurrency->position = 'front';
                $newCurrency->is_deletable = false;
                $newCurrency->save();
                $company->currency_id = $newCurrency->id;
            }

            $company->admin_id = $admin->id;
            $company->save();

            $paymentMode = new PaymentMode();
            $paymentMode->company_id = $company->id;
            $paymentMode->name = "Cash";
            $paymentMode->mode_type = "cash";
            $paymentMode->save();




            $mailSetting = GlobalSettings::where('setting_type', 'email')->where('status', 1)->where('verified', 1)->first();
            if ($mailSetting) {
                // $message = $this->frontTranslations['register_thank_you'] . ' <a href="' . route('main', 'admin/login') . '">' . $this->frontTranslations['login'] . '</a>.';

                $globalCompany = GlobalCompany::first();
                $notficationData = [
                    'company' => $company,
                    'user' => $admin,
                    'mobile' => $request->mobile,
                    'name' => $request->contact_name,
                    'email' => $request->company_email,
                    'companyname' => $request->company_name,
                    'verificationcode_email' => $verification_email,
                ];
                Notification::route('mail', $globalCompany->email)->notify(new NewUserRegistered($notficationData));
                $message = 'New Company Registered
                Name :' . $request->contact_name . '
                Mobile : ' . $request->mobile . '
                Email:' . $request->company_email . '
                Company Name : ' . $request->company_name;
                $whatsapp = $this->sendWhatsApp_1(9840398813, $message, '+91');
                $message_client = "Hello,

                We appreciate your interest in JnanaERP, the top AI-based cloud POS and ERP solution in India. Your contact from our sales representative will come soon.

                Check out our merchandise while you wait. I'm eager to welcome you to the Jnana Community.";

                $message_client_new = "Dear " . $request->contact_name . ",

                Welcome to JnanaERP! We are thrilled to have you on board.

                To proceed with your login, we kindly request you to verify the OTP (One-Time Password) sent to your registered mobile number.

                Once you have successfully verified the OTP, you can access all the powerful features and tools of JnanaERP SaaS. Our comprehensive suite of services is designed to streamline your business operations and enhance productivity.

                If you encounter any issues during the login process or have any questions, feel free to reach out to our dedicated support team. We are here to assist you every step of the way.

                Thank you for choosing JnanaERP SaaS. We look forward to serving you and helping your business thrive.

                Best regards,
                The JnanaERP Team";

                $whatsapp = $this->sendWhatsApp_1($request->mobile, $message_client_new, $request->countryCode);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            return Output::error($e->getMessage());
            return Output::error($this->frontSetting->error_contact_support);
        }


        $phone = $request->mobile;
        $otp = mt_rand(100000, 999999);
        $countryCode = $request->countryCode;
        $sms = $this->sendSMS($phone, $otp, $countryCode);
        $whatsapp = $this->sendWhatsApp($phone, $otp, $countryCode);
        $notficationData = [
            'mobile' => $request->phone,
            'name' => $request->name,
            'email' => $request->company_email,
            'otp' => $otp,
        ];
        //  Notification::route('mail', $request->company_email)->notify(new NewUserOTP($notficationData));


        //   DB::table('users')->where('mobile', $phone)->update(['mobile_otp' => $otp]);

        // if ($sms == 1 || $whatsapp == 1) {
        //     // return ApiResponse::make('OTP sent successfully');
        //     return response()->json(["message" => 'OTP sent successfully']);
        // } else {
        //     // return ApiResponse::make('Please try again sometimes.');
        //     return response()->json(["message" => 'Please try again sometimes.']);
        // }

        $sheetName = 'users';

        $data = [
            [
                $request->company_name,
                $request->contact_name,
                $request->mobile,
                $request->company_email,
                Carbon::now()->format('Y-m-d H:i:s')
            ]
        ];

        /** generate a new sheet in a specific spread sheet **/
        Sheets::spreadsheet(config('google.post_spreadsheet_id'));

        /** write the data in the newly generated sheet **/
        Sheets::sheet($sheetName)->append($data);



        return Output::success($this->frontSetting->register_success_text);
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

    private function sendWhatsApp_1($phone, $message, $countrycode)
    {
        $url = 'https://wa.yourestore.in/chat/sendmessage';
        $data = [
            'number' => $phone,
            'message' => $message,
            'client' => '9445526095',
            'country_code' => $countrycode,
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

    public function callToAction(CallToActionRequest $request)
    {
        return Output::success('success');
    }

    public function changeLanguage()
    {
        $request = request();

        $langKey = $request->has('key') ? $request->key : 'en';

        session(['front_lang_key' => $langKey]);

        return Output::success('success');
    }

    // TODO - Verify Register
    // TODO - Change Language

    public function googleLogin(Request $request)
    {

        session()->flush();
        $token = $request->input('token');

        if ($token == "") {
            throw new ApiException('Access token required.');
        }

        // Verify the token with Google API
        $googleTokenInfoUrl = 'https://oauth2.googleapis.com/tokeninfo?access_token=' . $token;
        $response = file_get_contents($googleTokenInfoUrl);
        $tokenInfo = json_decode($response);

        // Check if the token is valid and retrieve the user's email address
        if (!$tokenInfo || isset($tokenInfo->error)) {
            throw new ApiException('Failed to verify token with Google.');
        }

        $email = $tokenInfo->email;

        // Check if the email is already registered
        $existingCompany = Company::where('email', $email)->first();

        if ($existingCompany) {
            $credentials = [
                'password' => $request->password,
                'email' => $email,
                'user_type' => 'staff_members'
            ];
            $token1 = auth('api')->attempt($credentials);


            // // For checking user
            // $user = User::select('*');
            // if ($email != '') {
            //     $user = $user->where('email', $email);
            // } else if ($phone != '') {
            //     $user = $user->where('phone', $phone);
            // }
            // $user = $user->first();

            // Adding user type according to email/phone

            return ApiResponse::make('Logged in successfully', $this->prepareResponse($token1));
        } else {
            // Company not registered, handle registration

            // Create a new company record with the retrieved email and other details
            $company = new Company();
            $company->name = $request->company_name;
            $company->short_name = Str::lower($request->company_name);
            $company->email = $email;
            $company->phone = null;
            $company->is_global = 0;
            $company->is_googlelogin = true;
            $company->invoice_template = '{
                "header": {
                  "logo": false,
                  "company_name": true,
                  "company_full_name": true,
                  "receipt_title": true,
                  "company_address": true,
                  "company_email": true,
                  "company_no": true,
                  "tax_no": false
                },
                "customer_details": {
                  "name": true,
                  "invoice_no": true,
                  "staff_name": true,
                  "date": true
                },
                "total_details": {
                  "sub_total": true,
                  "discount": true,
                  "service_charges": true,
                  "tax": true,
                  "due": true
                },
                "table_setting": { "mrp": true, "saving_amount": true, "units": false },
                "tax_wise_calculations": { "enabled": false },
                "footer": {
                  "barcode": true,
                  "message": true,
                  "watermark": true,
                  "qr_code": false,
                  "total_item_count": false,
                },
                "thanks_message": "Thanks For Coming Visit again!",
                "company_full_name": ""
              }';
            $company->save(); // Generate a random 10-character password
            $verification_email = Str::random(50);
            $admin = StaffMember::create([
                'company_id' => $company->id,
                'name' => "Admin",
                'phone' => null,
                'profile_name' => $request->profile_name,
                'email' => $email,
                'password' => $request->password,
                // Hash the generated password
                'user_type' => 'staff_members',
                'email_verification_code' => $verification_email,
                'status' => 'enabled',
                'user_agent' => $request->server('HTTP_USER_AGENT')
            ]);


            $adminRole = Role::withoutGlobalScope(CompanyScope::class)->where('name', 'admin')->where('company_id', $company->id)->first();

            $admin->role_id = $adminRole->id;
            $admin->save();
            $admin->roles()->attach($adminRole->id);
            $rupeeCurrency = new Currency();
            $rupeeCurrency->company_id = $company->id;
            $rupeeCurrency->name = 'Rupee';
            $rupeeCurrency->code = 'INR';
            $rupeeCurrency->symbol = '₹';
            $rupeeCurrency->position = 'front';
            $rupeeCurrency->is_deletable = false;
            $rupeeCurrency->save();




            $company->currency_id = $rupeeCurrency->id;
            $company->admin_id = $admin->id;
            $company->save();

            $paymentMode = new PaymentMode();
            $paymentMode->company_id = $company->id;
            $paymentMode->name = "Cash";
            $paymentMode->mode_type = "cash";
            $paymentMode->save();

            $mailSetting = GlobalSettings::where('setting_type', 'email')->where('status', 1)->where('verified', 1)->first();
            if ($mailSetting) {
                $globalCompany = GlobalCompany::first();
                $notficationData = [
                    'company' => $company,
                    'user' => $admin,
                    'mobile' => $request->mobile,
                    'name' => $request->contact_name,
                    'email' => $request->company_email,
                    'companyname' => $request->company_name,
                    'verificationcode_email' => $verification_email,
                ];
                Notification::route('mail', $globalCompany->email)->notify(new NewUserRegistered($notficationData));
            }

            // Authenticate the newly registered user and generate a JWT token
            $credentials = [
                'email' => $email,
                'password' => $request->password,
                // Use the generated password for authentication
                'user_type' => 'staff_members',
                // Modify according to your user type
            ];

            $user = User::select('*');
            if ($email != '') {
                $user = $user->where('email', $email);
            }
            $user = $user->first();

            // Adding user type according to email/phone
            if ($user) {
                if ($user->user_type == 'super_admins') {
                    $credentials['user_type'] = 'super_admins';
                    $credentials['is_superadmin'] = 1;
                    $userCompany = GlobalCompany::where('id', $user->company_id)->first();
                } else {
                    $credentials['user_type'] = 'staff_members';
                    $userCompany = Company::where('id', $user->company_id)->first();
                }
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

            // Return the JWT token and other necessary data to the frontend
            return ApiResponse::make('Registered and logged in successfully', $this->prepareResponse($token));
        }
    }

    private function prepareResponse($token)
    {
        $company = company();
        $helpvideos = HelpVideo::where('status', 1)->get();
        // Customize the response data as per your requirements
        $response = [
            'helpvideos' => $helpvideos,
            'token' => $token,
            'user' => $this->respondWithToken($token),
            'app' => $company,
            'shortcut_menus' => Settings::where('setting_type', 'shortcut_menus')->first(),
            'email_setting_verified' => $this->emailSettingVerified(),
            'visible_subscription_modules' => Common::allVisibleSubscriptionModules(),
        ];

        return $response;
    }

    protected function respondWithToken($token)
    {
        $user = user();

        return [
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Carbon::now()->addDays(180),
            'user' => $user
        ];
    }

    public function emailSettingVerified()
    {
        $emailSettingVerified = Settings::where('setting_type', 'email')
            ->where('status', 1)
            ->where('verified', 1)
            ->count();

        return $emailSettingVerified > 0 ? 1 : 0;
    }
}
