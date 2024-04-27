<?php

namespace App\Http\Requests\Api\Sales;

use App\Models\Warehouse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

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
        $warehouse = warehouse();
        $warehouseId = $warehouse->id;
        $warehouse = Warehouse::where('id', $warehouseId)->first();

   

        $rules = [
            'user_id' => 'required',
            'order_status' => 'required',
            'product_items'    => 'required',
            'order_date'    => 'required',
        ];

        if ($warehouse->is_billing != 1 && $this->order_type != 'purchase') {
            $rules['message'] = 'required|unique:orders,message';
        }
        if ($this->invoice_number && $this->invoice_number != '') {
            $rules['invoice_number'] = 'required|unique:orders,invoice_number';
        }

        return $rules;
    }
}
