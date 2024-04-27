<?php

namespace App\Traits;

use App\Classes\Common;
use App\Http\Requests\Api\Customer\ImportRequest;
use App\Imports\PartyImport;
use App\Models\User;
use App\Models\UserDetails;
use App\Models\Warehouse;
use Examyou\RestAPI\ApiResponse;
use Examyou\RestAPI\Exceptions\ApiException;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

trait PartyTraits
{
    public $userType = "";

    public function modifyIndex($query)
    {

        $request = request();
        $warehouse = warehouse();

        if ($request->has('search_due_type') && $request->search_due_type != "") {
            if ($request->search_due_type == "receive") {
                $query = $query->where('user_details.due_amount', '>=', 0);
            } else {
                $query = $query->where('user_details.due_amount', '<=', 0);
            }
        }
        if ($request->has('assign_to') && $request->assign_to != "") {
            $query = $query->where('users.assign_to', '=', $request->assign_to);
        }

        if (
            ($this->userType == 'customers' && $warehouse->customers_visibility == 'warehouse') ||
            ($this->userType == 'suppliers' && $warehouse->suppliers_visibility == 'warehouse') ||
            ($this->userType == 'referral' && $warehouse->suppliers_visibility == 'warehouse') ||
            ($this->userType == 'staff_members' && $warehouse->suppliers_visibility == 'warehouse')
        ) {
            $query = $query->where(function ($query) use ($warehouse) {
                $query->where('users.warehouse_id', '=', $warehouse->id)
                    ->orWhere('users.is_walkin_customer', '=', 1);
            });
        }

        if ($this->userType == 'referral' || $this->userType == 'staff_members') {
            $query->with('orders');

            if ($request->has('dates') && $request->dates != null && count($request->dates) > 0) {
                $dates = $request->dates;
                $startDate = $dates[0];
                $endDate = $dates[1];

                $query->with([
                    'orders' => function ($query) use ($startDate, $endDate) {
                        $query->whereBetween('order_date', [[$startDate], [$endDate]]);
                    }
                ]);
            }
        }

        // Added By saravanan For Customers is_deleted
        $query = $query->where('users.is_deleted', '=', 0);

        $query = $query->join('user_details', 'user_details.user_id', '=', 'users.id')
            ->where('user_details.warehouse_id', $warehouse->id);

        return $query;
    }

    public function storing($user)
    {

        $request = request();
        $loggedUser = user();
        $warehouse = warehouse();
        $company = company();

        $user->warehouse_id = $loggedUser->hasRole('admin') && $request->warehouse_id != '' ? $request->warehouse_id : $warehouse->id;
        $user->user_type = $this->userType;
        $user->lang_id = $company->lang_id;

        return $user;
    }

    public function updating($user)
    {
        $request = request();
        $loggedUser = user();

        $user->user_type = $this->userType;

        if ($loggedUser->hasRole('admin') && $request->warehouse_id != '') {
            $user->warehouse_id = $request->warehouse_id;
        }else {
            $user->warehouse_id = $request->warehouse_id;
        }

        return $user;
    }

    public function stored($user)
    {

        // Generating user details for each warehouse
        $allWarehouses = Warehouse::select('id')->get();
        foreach ($allWarehouses as $allWarehouse) {
            $userDetails = new UserDetails();
            $userDetails->warehouse_id = $allWarehouse->id;
            $userDetails->user_id = $user->id;
            $userDetails->credit_period = 30;
            $userDetails->save();
        }

        $this->storedAndUpdated($user);
    }

    public function updated($user)
    {
        $this->storedAndUpdated($user);
    }

    public function storedAndUpdated($user)
    {

        $request = request();
        $company = company();
        $warehouse = warehouse();
        $warehouseId = $warehouse->id;

        $userDetails = $user->details;
        $userDetails->warehouse_id = $warehouseId;
        $userDetails->user_id = $user->id;
        $userDetails->opening_balance = $request->opening_balance == "" ? 0 : $request->opening_balance;
        // Added For Referral
        $userDetails->opening_balance_type = $request->opening_balance_type ? $request->opening_balance_type : 'receive';
        $userDetails->credit_period = $request->credit_period == "" ? 0 : $request->credit_period;
        $userDetails->credit_limit = $request->credit_limit == "" ? 0 : $request->credit_limit;
        $userDetails->save();
        if ($this->userType != 'referral') {
            Common::updateUserAmount($user->id, $warehouseId);
        } else if ($this->userType == 'referral') {
            User::where('id', $user->id)->update(['company_id' => $company->id]);
        }

        return $user;
    }

    public function destroying($user)
    {
        if ($user->is_walkin_customer) {
            throw new ApiException('Default Walkin Customer Cannot be deleted');
        }

        return $user;
    }

    public function import(ImportRequest $request)
    {
        if ($request->hasFile('file')) {
            Excel::import(new PartyImport($this->userType), request()->file('file'));
        }

        return ApiResponse::make('Imported Successfully', []);
    }
};
