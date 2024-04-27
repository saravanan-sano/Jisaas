<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\SuperAdmin\Http\Controllers\Front\HomeController;
use App\Http\Controllers\Api\OnlineOrdersController;
use App\Http\Controllers\Api\MasterDeleteController;
use App\Http\Controllers\Api\UsersController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\CustomersController;

Route::post('otpsent', [AuthController::class, 'sendOTP']);
Route::get('get_countries', [AuthController::class, 'getCountries']);
Route::post('v1/verifyotp', [AuthController::class, 'verifyOTP']);
Route::post('v1/resetpassword', [AuthController::class, 'resetPassword']);
Route::post('v1/resetpassword_front', [AuthController::class, 'resetPasswordFront']);
Route::post('v1/verifyotp_resetpassword', [AuthController::class, 'verifyOTP_resetpassword']);
Route::post('v1/resetpassword_save', [AuthController::class, 'resetpassword_save']);
Route::get('export', [ProductController::class, 'export'])->name('export');
Route::get('export_customer', [CustomersController::class, 'export'])->name('export');
Route::post('googlelogin', [HomeController::class, 'googleLogin']);


Route::middleware('jwt.auth')->group(function () {
    Route::post('v1/updatecontact', [AuthController::class, 'UpdateContact']);
});
Route::post('ordersget/{id}', [AuthController::class, 'orderget']);

Route::post('orderinvoiceget', [AuthController::class, 'orderinvoiceget']);
Route::get('warehouseget/{id}', [AuthController::class, 'warehouseget']);

Route::post('v1/deactivate', [UsersController::class,'deactivateAccount']);
