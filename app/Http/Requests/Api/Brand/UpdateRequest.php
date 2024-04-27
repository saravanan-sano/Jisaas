<?php

namespace App\Http\Requests\Api\Brand;

use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

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
        $convertedId = Hashids::decode($this->route('brand'));
        $id = $convertedId[0];
        $company = company();
        $company_id = $company->id;
        $rules = [
            'name'    => 'required',
            //'slug' => 'required|unique:brands,slug,' . $id,
            'slug' => ['required', 'string', 'max:255', Rule::unique('brands')->where(function ($query) use ($id, $company_id) {
                return $query->where(['company_id' => $company_id])->where('id', '!=', $id);
            })],
        ];
        return $rules;
    }
}
