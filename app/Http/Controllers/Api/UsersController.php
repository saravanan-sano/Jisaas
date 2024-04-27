<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiBaseController;
use App\Http\Requests\Api\User\IndexRequest;
use App\Http\Requests\Api\User\StoreRequest;
use App\Http\Requests\Api\User\UpdateRequest;
use App\Http\Requests\Api\User\DeleteRequest;
use App\Models\User;
use App\Models\Company;
use App\Models\UserDevices;
use App\Traits\UserTraits;
use Illuminate\Support\Facades\Log;
use Examyou\RestAPI\ApiResponse;
use Tymon\JWTAuth\Facades\JWTAuth;
use Vinkla\Hashids\Facades\Hashids;

class UsersController extends ApiBaseController
{
	use UserTraits;

	protected $model = User::class;

	protected $indexRequest = IndexRequest::class;
	protected $storeRequest = StoreRequest::class;
	protected $updateRequest = UpdateRequest::class;
	protected $deleteRequest = DeleteRequest::class;

	public function __construct()
	{
		parent::__construct();

        $request = request();
        try {
            $this->userType = $request->user_type;
        } catch (\Throwable $th) {
            $this->userType = "staff_members";
        }
	}

	public function sessionuser()
	{
		$user = user();
		$userDevices = [];

		if ($user->role->name == "admin") {
			$users = User::where('user_type', 'staff_members')->where('company_id', $user->company_id)->get();

			foreach ($users as $userItem) {
				$deviceId = $this->getIdFromHash($userItem->xid);
				$udevices = UserDevices::where('user_id', $deviceId)->get();
				$userDevices = array_merge($userDevices, $udevices->toArray());
			}
		} else {
			$userDevices = UserDevices::where('user_id', $user->id)->get()->toArray();
		}

		return response()->json(['data' => $userDevices, 'message' => 'Data Received Successfully!', 'status' => 200], 200);
	}


    public function deactivateAccount()
	{

        $request = request();
        $user_id = $this->getIdFromHash($request->xid);

        $user = User::where('id', $user_id)->first();
        $user->status = "disabled";
        $user->save();

		return response()->json(['data'=> $user ,'message' => 'Account Deactivated!', 'status' => 200], 200);
	}

	public function sessionclear()
	{
		$request = request();
		$token = $request->token;
		$refresh_token = $request->refresh_token;
		$is_token = UserDevices::where('token', $token)->first();
		if ($refresh_token) {
			// JWTAuth::setToken($refresh_token)->invalidate();
			UserDevices::where('refresh_token', $refresh_token)->delete();
		}
		else if ($is_token) {
			// JWTAuth::setToken($token)->invalidate();
			UserDevices::where('token', $token)->delete();
		}
		return ApiResponse::make(__('Session closed successfully'));
	}
}
