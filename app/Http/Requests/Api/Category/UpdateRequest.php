<?php

namespace App\Http\Requests\Api\Category;

use Illuminate\Foundation\Http\FormRequest;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Validation\Rule;

class UpdateRequest extends FormRequest
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
			'slug' => ['required', 'string', 'max:255', Rule::unique('categories')->where(function ($query) use ($company_id) {
                return $query->where(['company_id' => $company_id]);
            })],
		];

		return $rules;
	}
}
