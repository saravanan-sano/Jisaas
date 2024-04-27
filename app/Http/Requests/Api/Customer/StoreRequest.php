<?php

namespace App\Http\Requests\Api\Customer;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

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
            'phone'    => [
                'numeric',
                Rule::unique('users', 'phone')->where(function ($query)  use ($company_id) {
                    return $query->where('user_type', 'customers')->where('is_deleted', 0)->where(['company_id' => $company_id]);
                })
            ],
            'name'     => 'required',
            'email' => [
                'nullable',
                'email',
                Rule::unique('users', 'email')->where(function ($query) use ($company_id) {
                    return $query->where('user_type', 'customers')
                        ->where('is_deleted', 0)
                        ->where('company_id', $company_id);
                }),
            ],
            'status'     => 'required',
        ];

        if ($loggedUser->hasRole('admin')) {
            $rules['warehouse_id'] = 'required';
        }

        return $rules;
    }
}
