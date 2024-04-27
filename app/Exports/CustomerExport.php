<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\DB;

class CustomerExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $warehouse = warehouse();
        $userData = DB::table('users')
            ->where('warehouse_id', $warehouse->id)
            ->where('user_type', 'customers')
            ->select(
                'name',
                'phone',
                'email',
                'status',
                'tax_number',
                'is_wholesale_customer',
                'address',
                'pincode',
                'location',
                'business_type',
                'user_type',
                'id'
            )
            ->get();

        // Encode the 'id' values using Hashid


        foreach ($userData as $key => $value) {
            $userData[$key]->id = Hashids::encode($value->id);
            $userData[$key]->is_wholesale_customer = $value->is_wholesale_customer ? "Yes" : "No";
        }

        return $userData;
    }

    public function headings(): array
    {
        return [
            'name',
            'phone',
            'email',
            'status',
            'tax_number',
            'is_wholesale_customer',
            'address',
            'pincode',
            'location',
            'business_type',
            'user_type',
            'id'
        ];
    }
}
