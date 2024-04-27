<?php

namespace App\Http\Requests\Api\Tax;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
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
        $company = company();
        $company_id=$company->id;

        $rules = [
           'name'    => 'required',
            // 'rate'    => 'required|numeric|between:0,100',
            // 'name' => ['required', Rule::unique('taxes')->where(function ($query) use ($company_id) {
            //     return $query->where(['company_id' => $company_id]);
            // })],
			'rate' => ['required', 'numeric', 'between:0,100', Rule::unique('taxes')->where(function ($query) use ($company_id) {
                return $query->where(['company_id' => $company_id]);
            })],
        ];

        return $rules;

    }

}
