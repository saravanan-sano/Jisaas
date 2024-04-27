<?php

namespace App\Exports;

use App\Models\ProductDetails;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Vinkla\Hashids\Facades\Hashids;

class ProductExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $warehouse = warehouse();
        $data = ProductDetails::join('products', 'products.id', '=', 'product_details.product_id')
            ->leftJoin('brands', 'brands.id', '=', 'products.brand_id')
            ->leftJoin('taxes', 'taxes.id', '=', 'product_details.tax_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->join('units', 'units.id', '=', 'products.unit_id')
            ->where('product_details.warehouse_id', $warehouse->id)
            ->select(
                'products.name',
                'product_details.mrp',
                'product_details.purchase_price',
                'product_details.sales_price',
                'product_details.current_stock',
                'product_details.product_id'
            )
            ->get();

        // Encode the 'id' values using Hashids
        $hashedData = $data->map(function ($item) {
            $item['x_product_id'] = Hashids::decode($item['x_product_id']);
           
            return $item;
        });

        return $hashedData;
    }

    public function headings(): array
    {
        return [
            'name',
            'mrp',
            'purchase_price',
            'sales_price',
            'current_stock',
            'id',
        ];
    }
}
