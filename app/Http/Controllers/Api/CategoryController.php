<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ApiBaseController;
use App\Http\Requests\Api\Category\IndexRequest;
use App\Http\Requests\Api\Category\StoreRequest;
use App\Http\Requests\Api\Category\UpdateRequest;
use App\Http\Requests\Api\Category\DeleteRequest;
use App\Http\Requests\Api\Category\ImportRequest;
use App\Imports\CategoryImport;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use Examyou\RestAPI\ApiResponse;
use Examyou\RestAPI\Exceptions\ApiException;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use PhpParser\Node\Expr\Cast\Object_;

use function PHPSTORM_META\map;

class CategoryController extends ApiBaseController
{
    protected $model = Category::class;

    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function destroying(Category $category)
    {
        // Can not delete parent category
        $childCategoryCount = Category::where('parent_id', $category->id)->count();
        if ($childCategoryCount > 0) {
            throw new ApiException('Parent category cannot be deleted. Please delete child category first.');
        }

        // Category assigned to any product will not be deleted
        $productCount = Product::where('category_id', $category->id)->count();
        if ($productCount > 0) {
            throw new ApiException('Cateogry assigned to any product is not deletable.');
        }

        return $category;
    }

    public function import(ImportRequest $request)
    {
        if ($request->hasFile('file')) {
            Excel::import(new CategoryImport, request()->file('file'));
        }

        return ApiResponse::make('Imported Successfully', []);
    }


    public function categoryReport()
    {
        $request = request();
        $warehouse = warehouse();
        $company = company();



        $items = Product::with('category')->where('company_id', $company->id)->get();

        $category_orderItems = [];

        if ($request->has('dates') && $request->dates != null && count($request->dates) > 0) {
            $dates = $request->dates;
            $startDate = $dates[0];
            $endDate = $dates[1];
        }

        foreach ($items as $key => $item) {
            $orders = OrderItem::where("product_id", $item->id)->whereRaw('created_at >= ?', [$startDate])
                ->whereRaw('created_at <= ?', [$endDate])->get();
            if (count($orders) > 0) {
                foreach ($orders as $key => $order) {
                    $order->name = $item->name;
                    $order->hsn_sac_code = $item->hsn_sac_code;
                    $order->category_name = $item->category->name;
                }
                $category_orderItems[] = $orders;
            }
        }

        // return  $category_orderItems
        $productSum = [];

        foreach ($category_orderItems as $key => $items) {
            $product = ['quantity' => '0', 'subtotal' => 0, 'total' => 0, 'discount' => 0, 'tax_amount' => 0];
            foreach ($items as $key => $item) {
                $product['quantity'] = $product['quantity'] + $item->quantity;
                $product['subtotal'] = $product['subtotal'] + $item->subtotal;
                $product['discount'] = $product['discount'] + $item->total_discount;
                $product['tax_amount'] = $product['tax_amount'] + $item->total_tax;
                $product['total'] = $product['total'] + $item->total_tax +  $item->subtotal - $item->total_discount;
                $product['name'] = $item->name;
                $product['hsn_sac_code'] = $item->hsn_sac_code;
            }
            $productSum[$items[0]->category_name][] = $product;
        }

        if ($request->has('categories') && $request->categories != null) {
            return ApiResponse::make('product', [$request->categories => $productSum[$request->categories]]);
        };

        return ApiResponse::make('product', $productSum);
    }
}
