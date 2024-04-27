<?php

use App\Classes\Common;
use App\Models\Company;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

Route::get('{path}', function () {
    if (file_exists(storage_path('installed'))) {

        $appName = "JIERP";
        $appVersion = File::get(public_path() . '/version.txt');
        $modulesData = Common::moduleInformations();
        $themeMode = session()->has('theme_mode') ? session('theme_mode') : 'light';
        $company = Company::first();
        $appVersion = File::get('version.txt');
        $appVersion = preg_replace("/\r|\n/", "", $appVersion);

        return view('welcome', [
            'appName' => $appName,
            'appVersion' => preg_replace("/\r|\n/", "", $appVersion),
            'installedModules' => $modulesData['installed_modules'],
            'enabledModules' => $modulesData['enabled_modules'],
            'themeMode' => $themeMode,
            'company' => $company,
            'appVersion' => $appVersion,
            'appEnv' => env('APP_ENV'),
            'appType' => 'multiple'
        ]);
    } else {
        return redirect('/install');
    }
})->where('path', '^(?!api.*$).*')->name('main');
