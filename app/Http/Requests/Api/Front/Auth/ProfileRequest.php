<?php

namespace App\Http\Requests\Api\Front\Auth;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Vinkla\Hashids\Facades\Hashids;

class ProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $user = auth('api_front')->user();
       // Log::info($user->xid);
        $id = Hashids::decode($user->xid);
     //   Log::info($id);
        $companyId = $user->company_id;
        return [
            'name' => 'required',
            'email' => [
                'email',
                Rule::unique('users', 'email')->where(function ($query) use ($companyId) {
                    return $query->where('user_type', 'customers')
                        ->where('is_deleted', 0)
                        ->where('company_id', $companyId);
                })->ignore($id[0]),
            ],
        ];
    }
}
