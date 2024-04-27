<?php

namespace App\Http\Requests\Api\Unit;

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
			'name' => ['required', 'string', 'max:50', Rule::unique('units')->where(function ($query) use ($company_id) {
                return $query->where(['company_id' => $company_id]);
            })],
			'short_name' => ['required', 'string', 'max:50', Rule::unique('units')->where(function ($query) use ($company_id) {
                return $query->where(['company_id' => $company_id]);
            })],
			// 'name'    => 'required',
			// 'short_name'    => 'required',
			'operator'    => 'required',
			'operator_value'    => 'required',
		];

		return $rules;
	}
}
