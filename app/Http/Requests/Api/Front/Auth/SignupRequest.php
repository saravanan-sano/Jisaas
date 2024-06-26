<?php

namespace App\Http\Requests\Api\Front\Auth;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class SignupRequest extends FormRequest
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
    // public function rules()
    // {

    // 	return [
    // 		'name' => 'required',
    // 		'email'    => [
    // 			'required', 'email',
    // 			Rule::unique('users', 'email')->where(function ($query) {
    // 				return $query->where('user_type', 'customers');
    // 			})
    // 		],
    // 		'phone'    => [
    // 			'numeric',
    // 			Rule::unique('users', 'phone')->where(function ($query) {
    // 				return $query->where('user_type', 'customers');
    // 			})
    // 		],
    // 		'password' => 'required|min:8'
    // 	];
    // }

    public function rules()
    {
        $companyId = $this->input('company_id');


        return [
            'name' => 'required',
            'email' => [
                'nullable',
                'email',
                Rule::unique('users', 'email')->where(function ($query) use ($companyId) {
                    return $query->where('user_type', 'customers')
                        ->where('is_deleted', 0)
                        ->where('company_id', $companyId);
                }),
            ],
            'phone' => [
                'numeric',
                Rule::unique('users', 'phone')->where(function ($query) use ($companyId) {
                    return $query->where('user_type', 'customers')
                        ->where('is_deleted', 0)
                        ->where('company_id', $companyId);
                }),
            ],
            'password' => 'required|min:8',
        ];
    }
}
