<?php

namespace App\Imports;


use App\Models\ProductDetails;
use App\Models\LogActivity;
use Examyou\RestAPI\Exceptions\ApiException;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\ToArray;
use Vinkla\Hashids\Facades\Hashids;

class ProductUpdateImport implements ToArray, WithHeadingRow
{
    public function array(array $products)
    {
        DB::transaction(function () use ($products) {
            $user = user();

            foreach ($products as $product) {

                $productid = trim($product['id']);
                $productName = trim($product['name']);
                [$productid] = Hashids::decode($productid);
                $productCount = ProductDetails::where('product_id', $productid)->first();
                if ($productCount) {
                    if (trim($product['purchase_price']) <= trim($product['sales_price']) || trim($product['purchase_price']) == 0) {
                        $id = Hashids::decode(trim($product['id']));
                        $mrp = $product['mrp'] ?? 0;
                        $purchasePrice = $product['purchase_price'] ?? 0;
                        $salesPrice = $product['sales_price'] ?? 0;
                        $currentStock = $product['current_stock'] ?? 0;

                        $productDetail = ProductDetails::where('product_id', $id)->first();

                        $productDetail->mrp = $mrp;
                        $productDetail->purchase_price = $purchasePrice;
                        $productDetail->sales_price = $salesPrice;
                        $productDetail->current_stock = $currentStock;
                        $productDetail->save();

                        $requestInfo = [
                            'ip' => request()->ip(),
                            'user_agent' => request()->userAgent(),
                        ];

                        LogActivity::create([
                            'user_id' => $user->id,
                            'log_datetime' => now(),
                            'log_type' => 'update report',
                            'table_name' => 'product_details',
                            'request_info' => json_encode($requestInfo), // Serialize the array as JSON
                            'data' => json_encode($product), // Serialize the product array as JSON
                        ]);
                    } else {
                        throw new ApiException('Product ' . $productName . 'Selling price is lower than purchase price');
                    }
                }
            }
        });
    }
}
