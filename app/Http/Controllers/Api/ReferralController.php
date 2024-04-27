<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiBaseController;
use App\Http\Requests\Api\Referral\IndexRequest;
use App\Http\Requests\Api\Referral\StoreRequest;
use App\Http\Requests\Api\Referral\UpdateRequest;
use App\Http\Requests\Api\Referral\DeleteRequest;
use App\Models\Referral;
use App\Traits\PartyTraits;


class ReferralController extends ApiBaseController
{
    use PartyTraits;

    protected $model = Referral::class;

    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function __construct()
    {
        parent::__construct();

        $this->userType = "referral";
    }


}
