<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Api\StaffMember\IndexRequest;
use App\Http\Requests\Api\StaffMember\StoreRequest;
use App\Http\Requests\Api\StaffMember\UpdateRequest;
use App\Http\Requests\Api\StaffMember\DeleteRequest;
use App\Http\Controllers\ApiBaseController;
use App\Models\StaffMember;
use App\Traits\PartyTraits;
use Illuminate\Support\Facades\Log;

class StaffMemberController extends ApiBaseController
{
    use PartyTraits;

    protected $model = StaffMember::class;
    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function __construct()
    {
        parent::__construct();

        $this->userType = "staff_members";
    }

}
