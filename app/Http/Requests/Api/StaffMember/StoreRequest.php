<?php

namespace App\Http\Requests\Api\StaffMember;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
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
        $loggedUser = auth('api')->user();
        $company = company();
        $company_id = $company->id;

        $rules = [
            'phone' => [
                'numeric',
                Rule::unique('users', 'phone')->where(function ($query) use ($company_id) {
                    return $query->where('user_type', 'staff_members')->where(['company_id' => $company_id]);
                })
            ],
            'name' => 'required',
            // 'email'    => [
            //     'required', 'email',
            //     Rule::unique('users', 'email')->where(function ($query) {
            //         return $query->where('user_type', 'referral');
            //     })
            // ],
            'status' => 'required',
        ];

        if ($loggedUser->hasRole('admin')) {
            $rules['warehouse_id'] = 'required';
        }

        return $rules;
    }
}