<?php

use App\Classes\Common;
use App\SuperAdmin\Models\GlobalCompany;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;

Route::get('{path}', function () {
    if (file_exists(storage_path('installed'))) {

        $appName = "JIERPSaas";
        $appVersion = File::get(public_path() . '/superadmin_version.txt');
        $modulesData = Common::moduleInformations();
        $themeMode = session()->has('theme_mode') ? session('theme_mode') : 'light';
        $company = GlobalCompany::first();
        $appVersion = File::get('superadmin_version.txt');
        $appVersion = preg_replace("/\r|\n/", "", $appVersion);

        $host = $_SERVER['HTTP_HOST'];

        if ($host=="localhost:8000" || $host=="7f44-183-82-179-90.ngrok-free.app")
        {
            $country_code ="IN";
        }
        else
        {
            $country_code = isset($_SERVER["HTTP_CF_IPCOUNTRY"]) ? $_SERVER["HTTP_CF_IPCOUNTRY"] : "IN";
        }

        if ($country_code=="IN")
        {
            $country_code ="IN";
        }
        else
        {
            $country_code ="NONIN";
        }

        return view('welcome', [
            'appName' => $appName,
            'appVersion' => preg_replace("/\r|\n/", "", $appVersion),
            'installedModules' => $modulesData['installed_modules'],
            'enabledModules' => $modulesData['enabled_modules'],
            'themeMode' => $themeMode,
            'company' => $company,
            'appVersion' => $appVersion,
            'appEnv' => env('APP_ENV'),
            'appType' => 'multiple',
            'country_code'=> $country_code,
        ]);
    } else {
        return redirect('/install');
    }
})->where('path', '^(?!api.*$).*')->name('main');
