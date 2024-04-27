<?php

namespace App\Http\Controllers\Api\Front;

use Illuminate\Http\Request;
use App\Models\CartItem;
use App\Http\Controllers\ApiBaseController;
use App\Models\Company;
use App\Models\Currency;
use App\Models\Product;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Log;

class CartItemController extends ApiBaseController
{
    // GET method: Fetch all cart items
    public function get_cart(Request $request)
    {
        [$userId] = Hashids::decode($request->userId);
        $cartItems = CartItem::where('userid', $userId)->where('status', 0)->get();
        $cartProducts = json_decode($cartItems[0]->cart_item);

        $isUpdated = false;
        foreach ($cartProducts as $key => $product) {
            $currentProduct = Product::with('details', 'front_wholesale', 'details.tax')->where("id", $this->getIdFromHash($product->xid))->first();
            //  $product->front_wholesale = $currentProduct->front_wholesale;
            $product->details->shipping_price = $currentProduct->details->shipping_price;
            $product->details->is_price_updated = 0;
            $product->details->tax = $currentProduct->details->tax;

            $product->details->current_stock = $currentProduct->details->current_stock;


            if ($product->details->current_stock < $product->cart_quantity) {
                $product->details->is_stock_message = "No enough stock";
                $isUpdated = true;
            }



            if ($currentProduct->details->sales_tax_type == "exclusive") {

                if (!$currentProduct->details->tax) {
                    $currentProduct->details->tax = (object)['rate' => 0];
                }


                $currentProduct->details->sales_price = ($currentProduct->details->sales_price + ($currentProduct->details->sales_price * ($currentProduct->details->tax->rate / 100)));
                foreach ($currentProduct->front_wholesale as $key => $wholesale) {
                    $wholesale->wholesale_price = ($wholesale->wholesale_price + ($wholesale->wholesale_price * ($currentProduct->details->tax->rate / 100)));
                }
            }

            $product->single_unit_price = $currentProduct->details->sales_price;

            $currency = Company::with('currency')->where('id', $currentProduct->company_id)->first()->currency;

            if ($product->details->sales_price != $currentProduct->details->sales_price) {
                if ($product->details->sales_price < $currentProduct->details->sales_price) {
                    $product->details->is_price_updated = 1;
                    $product->details->is_price_message = "The cost of the product has been raised by " . ($currency->symbol) . ($currentProduct->details->sales_price - $product->details->sales_price);
                } else {
                    $product->details->is_price_updated = 2;
                    $product->details->is_price_message = "The cost of the product has been decreased by " . ($currency->symbol) . ($product->details->sales_price - $currentProduct->details->sales_price);
                }
                $product->details->sales_price = $currentProduct->details->sales_price;

                $isUpdated = true;
            }


            $user = auth('api_front')->user();

            if ($user->is_wholesale_customer) {
                foreach ($currentProduct->front_wholesale as $key => $wholesale) {

                    if (count($product->front_wholesale) > 0) {
                        // // Log::info([$key, $product->front_wholesale[$key]->wholesale_price, $wholesale->wholesale_price]);
                        if ($product->cart_quantity >= $wholesale->start_quantity && $product->cart_quantity <= $wholesale->end_quantity) {
                            if ($product->front_wholesale[$key]->wholesale_price < $wholesale->wholesale_price) {
                                $product->details->is_price_updated = 1;
                                $product->details->is_price_message = "The cost of the product has been raised by " . ($currency->symbol) . ($wholesale->wholesale_price - $product->front_wholesale[$key]->wholesale_price);
                            } else if ($product->front_wholesale[$key]->wholesale_price > $wholesale->wholesale_price) {
                                $product->details->is_price_updated = 2;
                                $product->details->is_price_message = "The cost of the product has been decreased by " . ($currency->symbol) . ($product->front_wholesale[$key]->wholesale_price - $wholesale->wholesale_price);
                            } else if ($product->front_wholesale[$key]->wholesale_price == $wholesale->wholesale_price) {
                                $product->details->is_price_updated = 0;
                            }
                        }
                    }
                    $product->front_wholesale = $currentProduct->front_wholesale;
                }
            }
        }
        $cartItems[0]->is_updated = $isUpdated;
        $cartItems[0]->cart_item = $cartProducts;

        return response()->json($cartItems);
    }

    // POST method: Store a new cart item
    public function store_cart(Request $request)
    {

        [$userId] = Hashids::decode($request->userid);

        $validatedData = $request->validate([
            'userid' => 'required', // Assuming userid refers to users table
            'cart_item' => 'required',
        ]);
        $validatedData['userid'] = $userId;

        $existingCartItem = CartItem::where('userid', $userId)
            ->where('status', 0)
            ->first();



        if ($existingCartItem) {
            // Update the existing cart item
            $existingCartItem->update(['cart_item' => $validatedData['cart_item']]);
            return response()->json($existingCartItem, 200);
        } else {
            // Create a new cart item
            $cartItem = CartItem::create($validatedData);
            return response()->json($cartItem, 201);
        }
    }

    public function updateStatus(Request $request)
    {
        $validatedData = $request->validate([
            'status' => 'required|integer',
        ]);

        [$userId] = Hashids::decode($request->id);

        $cartItem = CartItem::where('userid', $userId);

        $cartItem->update(['status' => $validatedData['status']]);

        return response()->json($cartItem, 200);
    }
}
