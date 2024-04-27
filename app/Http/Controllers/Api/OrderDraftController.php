<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OrderDraft;
use Examyou\RestAPI\ApiResponse;
use Illuminate\Http\Request;
use Vinkla\Hashids\Facades\Hashids;
class OrderDraftController extends Controller
{
    public function create(Request $request)
    {
        $warehouse = warehouse();
        $user = $request->user_id;
        [$userId]=Hashids::decode($user);
        $draft = new OrderDraft();
        $draft->warehouse_id = $warehouse->id;
        $draft->user_id = $userId;
        $draft->name = $request->name;
        $draft->data = $request->data;
        $draft->save();
        return ApiResponse::make('Draft Created Successfull', ['draft' => $draft]);
    }

    public function get()
    {
        $warehouse = warehouse();
        $drafts = OrderDraft::where('warehouse_id', $warehouse->id)->where('status', 1)->get();
        $hashedDrafts = $drafts->map(function ($draft) {
            $draft->user_id = Hashids::encode($draft->user_id);
            return $draft;
        });
        return response()->json([
            'data' => $hashedDrafts,
            'message' => 'Data Received Successfully!',
            'status' => 200
        ], 200);
    }
    public function Deletestatus(Request $request, $id)
    {
        $warehouse = warehouse();
        OrderDraft::where('id', $id)->where('warehouse_id', $warehouse->id)->delete();
        return ApiResponse::make('Deleted Successfull', ['id' => $id]);
    }
}
