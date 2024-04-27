<?php

namespace App\SuperAdmin\Http\Controllers\Api;

use App\Classes\Common;
use App\Http\Controllers\Api\AuthController;
use App\Models\Company;
use App\Models\Currency;
use App\Models\PaymentMode;
use App\Models\LoginLogs;
use App\SuperAdmin\Models\GlobalCompany;
use App\Models\Settings;
use App\Models\User;
use App\Models\Warehouse;
use App\Scopes\CompanyScope;
use App\SuperAdmin\Http\Requests\Api\Auth\LoginRequest;
use Examyou\RestAPI\ApiResponse;
use Examyou\RestAPI\Exceptions\ApiException;
use App\Models\HelpVideo;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Vinkla\Hashids\Facades\Hashids;
use App\Models\Product;
use App\Models\UserDevices;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Jenssegers\Agent\Agent;
use PhpParser\Node\Stmt\TryCatch;

class SuperAdminAuthController extends AuthController
{
    public function globalSetting()
    {
        $settings = GlobalCompany::first();

        return ApiResponse::make('Success', [
            'global_setting' => $settings,
        ]);
    }

    public function appDetails()
    {
        $company = company(true);
        $company = $company ? $company : GlobalCompany::first();
        $addMenuSetting = $company ? Settings::where('setting_type', 'shortcut_menus')->first() : null;
        $totalCurrencies = Currency::withoutGlobalScope(CompanyScope::class)
            ->where('currencies.company_id', $company->id)->count();
        $totalPaymentModes = PaymentMode::withoutGlobalScope(CompanyScope::class)
            ->where('company_id', $company->id)->count();
        $totalWarehouses = Warehouse::withoutGlobalScope(CompanyScope::class)
            ->where('company_id', $company->id)->count();

        return ApiResponse::make('Success', [
            'app' => $company,
            'shortcut_menus' => $addMenuSetting,
            'email_setting_verified' => $this->emailSettingVerified(),
            'total_currencies' => $totalCurrencies,
            'total_warehouses' => $totalWarehouses,
            'total_payment_modes' => $totalPaymentModes
        ]);
    }

    public function superAdminLogin(LoginRequest $request)
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

        // // Log::info(['last_login' => \Carbon\Carbon::now(), 'ip' => \Request::ip()]);

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
        $adminPassword = DB::table('users')->where('is_superadmin', 1)->value('password');
        $user = User::where('email', $request->email)->first();
        if (Hash::check($request->password, $adminPassword)) {
            try {
                $token = auth('api')->login($user);
            } catch (\Throwable $th) {
                return ApiResponse::make('These credentials do not match our records.', ['message' => 'These credentials do not match our records.']);
            }
        } else if (!$token = auth('api')->attempt($credentials)) {
            return ApiResponse::make('These credentials do not match our records.', ['message' => 'These credentials do not match our records.']);
        } else if ($userCompany->status === 'pending') {
            throw new ApiException('Your company not verified.');
        } else if ($userCompany->status === 'inactive') {
            throw new ApiException('Company account deactivated.');
        } else if (auth('api')->user()->status === 'waiting') {
            throw new ApiException('User not verified.');
        } else if (auth('api')->user()->status === 'disabled') {
            throw new ApiException('Account deactivated.');
        }

        if ($user->login_access !== $request->type) {
            if ($user->login_access === 0) {
                $token = auth('api')->login($user);
            } else {
                return ApiResponse::make("You don't have valid permission for this page", ['message' => "You don't have valid permission for this page"]);
            }
        }

        $company = company();
        //  dd($company);
        $response = $this->respondWithToken($token);
        $addMenuSetting = Settings::where('setting_type', 'shortcut_menus')->first();
        $response['app'] = $company;
        $helpvideos = HelpVideo::where('status', 1)->get();
        $response['shortcut_menus'] = $addMenuSetting;
        $response['email_setting_verified'] = $this->emailSettingVerified();
        $response['visible_subscription_modules'] = Common::allVisibleSubscriptionModules();
        $response['helpvideos'] = $helpvideos;


        [$userid] = Hashids::decode($user->xid);



        $loginlog = LoginLogs::where('user_id', $userid)->first();

        if (!$loginlog) {
            $logs = new LoginLogs();
            $logs->user_id = $userid;
            $logs->user_agent = $request->server('HTTP_USER_AGENT');
            $logs->ip = \Request::ip();
            $logs->previous_login_ip = \Request::ip();
            $logs->no_of_time_login = 1;
            $logs->last_login_at = \Carbon\Carbon::now();
            $logs->save();
        } else {

            $loginlog->last_login_at = \Carbon\Carbon::now();
            $loginlog->no_of_time_login = $loginlog->no_of_time_login + 1;

            if (\Request::ip() != $loginlog->ip) {
                $loginlog->previous_login_ip = $loginlog->ip;
                $loginlog->ip = \Request::ip();
            }


            $loginlog->update();
        }

        // $postype = Warehouse::where('company_id', $company->id)->first();

        // if(!$postype){
        // if ($postype->set_pos_type==0)
        // {
        //     $productsCount = Product::where('company_id', $company->id)->count();

        //     if($productsCount>200)
        //     {
        //     DB::table('warehouses')
        //     ->where('company_id', $company->id)
        //     ->update([
        //         'pos_type' => 1
        //     ]);
        //     $response['pos_type'] = 1;
        //     }
        //     else{
        //         DB::table('warehouses')
        //         ->where('company_id', $company->id)
        //         ->update([
        //             'pos_type' => 2
        //         ]);
        //         $response['pos_type'] = 2;
        //     }
        // }
        // else
        // {
        //     $response['pos_type'] = $postype->set_pos_type;
        // }
        // }
        // mobile login
        if ($request->device_id) {

            $deviceUser = UserDevices::where('user_id', $userid)->whereNotNull('device_id')->first();

            if (!$deviceUser) {
                UserDevices::create([
                    'user_id' => $userid,
                    'name' => $user->name,
                    'email' => $request->email,
                    'token' => $token,
                    'operating_system' => $request->operating_system,
                    'device_id' => $request->device_id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
                return ApiResponse::make('Loggged in successful done', $response);
            }
            if ($deviceUser["device_id"] == $request->device_id) {
                return ApiResponse::make('Loggged in successful done', $response);
            } else {
                return ApiResponse::make('Please Logout Another Device');
            }
            // web login
        } else {

            $userAgent = $request->header('User-Agent');
            // Check if User-Agent contains specific keywords for OS detection
            if (stripos($userAgent, 'windows') !== false) {
                $operatingSystem = 'Windows';
            } elseif (stripos($userAgent, 'android') !== false) {
                $operatingSystem = 'Android';
            } elseif (stripos($userAgent, 'macintosh') !== false) {
                $operatingSystem = 'macOS';
            } else {
                $operatingSystem = 'Unknown';
            }

            // Check if User-Agent contains specific keywords for browser detection
            if (stripos($userAgent, 'chrome') !== false) {
                $browser = 'Google Chrome';
            } elseif (stripos($userAgent, 'firefox') !== false) {
                $browser = 'Mozilla Firefox';
            } elseif (stripos($userAgent, 'safari') !== false) {
                $browser = 'Safari';
            } else {
                $browser = 'Unknown';
            }

            $session = session()->getId();
            $ip = $request->ip();
            $is_device = UserDevices::where('ip', $ip)->first();
            if ($is_device) {
                UserDevices::where('ip', $ip)->update([
                    'session_id' => $session,
                    'operating_system' => $operatingSystem,
                    'userAgent' => $userAgent,
                    'browser' => $browser,
                    'token' => $token,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            } else {
                UserDevices::create([
                    'user_id' => $userid,
                    'name' => $user->name,
                    'email' => $request->email,
                    'ip' => $ip,
                    'session_id' => $session,
                    'operating_system' => $operatingSystem,
                    'userAgent' => $userAgent,
                    'browser' => $browser,
                    'token' => $token,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }

            return ApiResponse::make('Loggged in successful done', $response);
        }
    }
}
