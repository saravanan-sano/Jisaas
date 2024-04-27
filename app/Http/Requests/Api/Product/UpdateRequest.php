<?php

namespace App\Http\Requests\Api\Product;

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
		$convertedId = Hashids::decode($this->route('product'));
		$id = $convertedId[0];
		$company = company();
        $company_id=$company->id;

		$rules = [
			'name'    => 'required',
			//'slug' => 'required|unique:products,slug,' . $id,
			'slug' => ['required', 'string', 'max:255', Rule::unique('products')->where(function ($query) use ($id, $company_id) {
                return $query->where(['company_id' => $company_id])->where('id', '!=',$id);
            })],
			'barcode_symbology'    => 'required',
			'item_code'    => ['required', Rule::unique('products')->where(function ($query) use ($id, $company_id) {
                return $query->where(['company_id' => $company_id])->where('id', '!=',$id);
            })],
			'category_id'    => 'required',
			'purchase_price'    => 'required|gt:0',
			'sales_price'    => 'required|gt:0|gte:purchase_price',
			'unit_id'    => 'required'
		];

		// If purchase or sales includes tax
		if ($this->purchase_tax_type == 'inclusive' || $this->sales_tax_type == 'inclusive') {
			$rules['tax_id'] = 'required';
		}

		return $rules;
	}
}
