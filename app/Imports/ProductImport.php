<?php

namespace App\Imports;

use App\Classes\Common;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductDetails;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\Warehouse;
use Examyou\RestAPI\Exceptions\ApiException;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\ToArray;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ProductImport implements ToArray, WithHeadingRow
{
    public function array(array $products)
    {

        // Log::info($products);
        DB::transaction(function () use ($products) {
            $user = user();
            $warehouse = warehouse();

            foreach ($products as $product) {

                if (
                    !array_key_exists('name', $product) || !array_key_exists('product_code', $product) || !array_key_exists('hsn_code', $product) || !array_key_exists('item_code', $product) || !array_key_exists('description', $product) ||
                    !array_key_exists('category', $product) || !array_key_exists('brand', $product) || !array_key_exists('unit', $product) || !array_key_exists('tax', $product) ||
                    !array_key_exists('mrp', $product) || !array_key_exists('purchase_price', $product) || !array_key_exists('sales_price', $product) || !array_key_exists('stock_quantitiy_alert', $product) || !array_key_exists('opening_stock', $product) || !array_key_exists('opening_stock_date', $product)
                ) {
                    throw new ApiException('Field missing from header.');
                }

                $productName = trim($product['name']);
                $productCount = Product::where('name', $productName)->where('company_id', $user->company_id)->count();


                if ($productCount > 0) {

                    $productCount1 = Product::where('name', $productName)->where('company_id', $user->company_id)->where('item_code', trim($product['item_code']))->count();
                    if ($productCount1 > 0) {
                     throw new ApiException('Product ' . $productName . ' Already Exists');
                    }
                    else
                    {
                        $productName = trim($product['name']).trim($product['item_code']);
                    }

                }
                // Category
                $categoryName = trim($product['category']);
                $category = Category::where('name', $categoryName)->where('company_id', $user->company_id)->first();
                if (!$category) {
                    $newCategory = new Category();
                    $newCategory->company_id =  $user->company_id;
                    $newCategory->name = $categoryName;
                    $newCategory->slug = Str::slug($categoryName, '-');
                    $newCategory->parent_id = null;
                    $newCategory->save();
                    $category=$newCategory;
                   // throw new ApiException('Category Not Found');
                }

                // Brand
                $brandName = trim($product['brand']);
                if ($brandName!='')
                {
                    $brand = Brand::where('name', $brandName)->where('company_id', $user->company_id)->first();
                    if (!$brand) {
                     //   throw new ApiException('Brand Not Found');
                     $newBrand = new Brand();
                     $newBrand->company_id =  $user->company_id;
                     $newBrand->name = $brandName;
                     $newBrand->slug = Str::slug($brandName, '-');
                     $newBrand->save();
                     $brand=$newBrand;
                    }

                }
                else
                {
                    $brand ='';
                }

                // Unit
                $unitName = trim($product['unit']);
                $unit = Unit::where('name', $unitName)->where('company_id', $user->company_id)->first();
                if (!$unit) {

                    $unit_save = new Unit();
                    $unit_save->company_id =  $user->company_id;
                    $unit_save->name = $unitName;
                    $unit_save->short_name = $unitName;
                    $unit_save->operator = 'multiply';
                    $unit_save->operator_value = 1;
                    $unit_save->is_deletable = false;
                    $unit_save->save();
                    $unit=$unit_save;
                   // // Log::info($unit_save);
                //    throw new ApiException('Unit Not Found');
                }

               // $barcodeSymbology = trim($product['barcode_symbology']);
                $barcodeSymbology = "CODE128";

                // if ($barcodeSymbology!='')
                // {
                // if ($barcodeSymbology == "" || !in_array($barcodeSymbology, ['CODE128', 'CODE39'])) {
                //     throw new ApiException('Barcode symoblogy must be CODE128 or CODE39');
                // }
                // }

                $itemCode = trim($product['item_code']);
                if ($itemCode!='')
                {
                $isItemCodeAlreadyExists = Product::where('item_code', $itemCode)->where('company_id', $user->company_id)->count();

                if ($isItemCodeAlreadyExists > 0) {
                   // throw new ApiException('Item Code ' . $itemCode . ' Already Exists');
                   $itemCode = 'DUP'.trim($product['item_code']);
                }
                }
                else
                {
                    $itmcode = mt_rand(100000, 999999);

                    if (!preg_match('/[^A-Za-z]/', $productName)) // '/[^a-z\d]/i' should also work.
                    {
                        $itemCode =substr($productName, 0, 5).$itmcode;
                    }
                    else {
                        //throw $th;
                        $itemCode =substr("TAM", 0, 5).$itmcode;

                    }
                }

                $hsnCode = trim($product['hsn_code']);
                $productCode = trim($product['product_code']);

                // Product Details

                // Tax
                $taxName = trim($product['tax']);
                if ($taxName != "") {
                    $tax = Tax::where('name', "Tax".$taxName)->where('company_id', $user->company_id)->first();

                    if (!$tax) {
                        $tax_save = new Tax();
                        $tax_save->company_id =  $user->company_id;
                        $tax_save->name = "Tax".$taxName;
                        $tax_save->rate = $taxName;
                        $tax_save->save();
                        $tax=$tax_save;

                      //  throw new ApiException('Tax Not Found');
                    }
                }

                // $purchaseTaxType = trim($product['purchase_tax_type']);
                // if ($taxName != "" && !in_array($purchaseTaxType, ['exclusive', 'inclusive', 'Inclusive', 'Exclusive'])) {
                //     throw new ApiException('Purchase Tax Type must be inclusive or exclusive');
                // }

                $salesTaxType = trim($product['sales_tax_type']);
                if ($taxName != "" && !in_array($salesTaxType, ['exclusive', 'inclusive', 'Inclusive', 'Exclusive'])) {
                    throw new ApiException('Sales Tax Type must be inclusive or exclusive');
                }

                $openingStockDate = trim($product['opening_stock_date']);
                $stockQuantityAlert = trim($product['stock_quantitiy_alert']);
                $openingStock = trim($product['opening_stock']);
                $openingStockDate = trim($product['opening_stock_date']);
              //  $wholesaleQuantity = trim($product['wholesale_quantity']);
              //  $wholesalePrice = trim($product['wholesale_price']);
                $allWarehouses = Warehouse::select('id')->get();

                $newProduct = new Product();
                $newProduct->name = $productName;
                $newProduct->slug = Str::slug($productName, '-');
                $newProduct->barcode_symbology = $barcodeSymbology;
                // $newProduct->item_code = (int) $itemCode;
                if ($itemCode!='')
                $newProduct->item_code = $itemCode;

                $newProduct->warehouse_id = $warehouse->id;
                $newProduct->hsn_sac_code = $hsnCode;
                $newProduct->product_code = $productCode;
                $newProduct->category_id = $category->id;
                if ($brand!='')
                $newProduct->brand_id = $brand->id;
                $newProduct->unit_id = $unit->id;
                $newProduct->user_id = $user->id;
                $newProduct->save();

                foreach ($allWarehouses as $allWarehouse) {
                    $newProductDetails = new ProductDetails();
                    $newProductDetails->warehouse_id = $allWarehouse->id;
                    $newProductDetails->product_id = $newProduct->id;
                    $newProductDetails->tax_id = $taxName == "" ? null : $tax->id;
                   // $newProductDetails->purchase_tax_type = $purchaseTaxType != '' ? lcfirst($purchaseTaxType) : 'exclusive';
                    $newProductDetails->sales_tax_type = $salesTaxType != '' ? lcfirst($salesTaxType) : 'exclusive';
                    $newProductDetails->mrp = trim($product['mrp']);
                    $newProductDetails->purchase_price = trim($product['purchase_price']);
                    $newProductDetails->sales_price = trim($product['sales_price']);
                    $newProductDetails->stock_quantitiy_alert = $stockQuantityAlert != "" ? (int) $stockQuantityAlert : null;
                    $newProductDetails->opening_stock = $openingStock != "" ? (int) $openingStock : null;
                    $newProductDetails->opening_stock_date = $openingStockDate != "" ? $openingStockDate : null;
                  //  $newProductDetails->wholesale_price = $wholesalePrice != "" ? $wholesalePrice : null;
                  //  $newProductDetails->wholesale_quantity = $wholesaleQuantity == "" ? null : $wholesaleQuantity;
                    $newProductDetails->save();



                    Common::recalculateOrderStock($newProductDetails->warehouse_id, $newProduct->id);
                }
            }
        });
    }
}
