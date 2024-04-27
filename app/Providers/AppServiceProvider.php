<?php

namespace App\Providers;

use Laravel\Cashier\Cashier;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;


class AppServiceProvider extends ServiceProvider
{
	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register()
	{
		Schema::defaultStringLength(191);
		Cashier::ignoreMigrations();
		if (app_type() == 'multiple') {
			Cashier::useSubscriptionModel(\App\SuperAdmin\Models\Subscription::class);
		}

		// For catching 404 Route not found error in vue app
		// Later in Base Controller we will disable it
		// Accroding to settings table app_debug column
		// config(['app.debug' => true]);npm
		// Setup in SmtpSettingsProvider
	}

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		Validator::extend('recaptcha', 'App\\Validators\\ReCaptcha@validate');
	}
}
