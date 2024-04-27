<?php

use App\Models\Company;
use App\Scopes\CompanyScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Get App Type
if (!function_exists('app_type')) {

    function app_type()
    {
        if (env('APP_TYPE')) {
            return 'multiple';
        } else {
            return 'multiple';
        }
    }
}

// Front Landing settings Language Key
if (!function_exists('front_lang_key')) {

    function front_lang_key()
    {
        if (session()->has('front_lang_key')) {
            return session('front_lang_key');
        }

        session(['front_lang_key' => 'en']);
        return session('front_lang_key');
    }
}

// This is app setting for logged in company
if (!function_exists('company')) {

    function company($reset = false)
    {
        if (session()->has('company') && $reset == false) {
            return session('company');
        }

        // If it is single
        if (app_type() == 'single') {
            $company = Company::with(['warehouse' => function ($query) {
                return $query->withoutGlobalScope(CompanyScope::class);
            }, 'currency' => function ($query) {
                return $query->withoutGlobalScope(CompanyScope::class);
            }, 'subscriptionPlan'])->first();

            if ($company) {
                session(['company' => $company]);
                return session('company');
            }

            return null;
        } else {
            $user = user();
           // $country_code = $_SERVER["HTTP_CF_IPCOUNTRY"];
            $country_code ="IN";

            // $ip = $request->ip(); //Dynamic IP address get
            // $data = \Location::get($ip);
            // // Log::info($data);
            if ($user && $user->company_id != "") {
                $company = Company::withoutGlobalScope('company')->with(['warehouse' => function ($query) use ($user) {
                    return $query->withoutGlobalScope(CompanyScope::class)
                        ->where('company_id', $user->company_id);
                }, 'currency' => function ($query) use ($user) {
                    return $query->withoutGlobalScope(CompanyScope::class)
                        ->where('company_id', $user->company_id);
                }, 'subscriptionPlan' => function ($query) use ($user) {
                    return $query->select('id', 'name', 'modules', 'max_products','max_orders','max_purchases','max_expenses', 'monthly_price', 'annual_price', 'default');
                }])->where('id', $user->company_id)->first();

                session(['company' => $company]);
                return session('company');
            }

            return null;
        }
    }
}

if (!function_exists('super_admin')) {

    /**
     * Return currently logged in user
     */
    function super_admin()
    {
        if (session()->has('super_admin')) {
            return session('super_admin');
        }

        $user = auth('api')->user();

        if ($user) {

            session(['super_admin' => $user]);
            return session('super_admin');
        }

        return null;
    }
}

if (!function_exists('user')) {

    /**
     * Return currently logged in user
     */
    function user($reset = false)
    {
        if (session()->has('user') && $reset == false) {
            return session('user');
        }

        $user = auth('api')->user();

        // TODO - Check if
        if ($user) {
            $user = $user->load(['role' => function ($query) use ($user) {
                return $query->withoutGlobalScope(CompanyScope::class)
                    ->where('company_id', $user->company_id);
            }, 'role.perms', 'warehouse' => function ($query) use ($user) {
                return $query->withoutGlobalScope(CompanyScope::class)
                    ->where('company_id', $user->company_id);
            }]);

            session(['user' => $user]);
            return session('user');
        }

        return null;
    }
}

if (!function_exists('warehouse')) {

    /**
     * Return currently logged in user
     */
    function warehouse($reset = false)
    {
        if (session()->has('warehouse') && $reset == false) {
            return session('warehouse');
        }

        $user = user($reset);

        if ($user) {
            session(['warehouse' => $user->warehouse]);
            return session('warehouse');
        }

        return null;
    }
}
