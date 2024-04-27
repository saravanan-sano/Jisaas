<?php

namespace App\Http\Requests\Api\Product;

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
            'name'    => 'required',
            //'slug'    => 'required|unique:products,slug',
            'slug' => ['required', Rule::unique('products')->where(function ($query) use ($company_id) {
                return $query->where(['company_id' => $company_id]);
            })],
            'barcode_symbology'    => 'required',
            //'item_code'    => 'required|unique:products,item_code',
            'item_code' => ['required', Rule::unique('products')->where(function ($query) use ($company_id) {
                return $query->where(['company_id' => $company_id]);
            })],
            'category_id'    => 'required',
            'purchase_price'    => 'required|gte:0',
            'sales_price'    => 'required||gte:0|gte:purchase_price',
            'unit_id'    => 'required',
            // Added for getting the tax by default by 'saravanan'
           //'tax_id'    => 'required',
        ];

        // If purchase or sales includes tax
        // if ($this->purchase_tax_type == 'inclusive' || $this->sales_tax_type == 'inclusive') {
        //     $rules['tax_id'] = 'required';
        // }

        if ($loggedUser->hasRole('admin')) {
            $rules['warehouse_id'] = 'required';
        }

        return $rules;
    }
}
