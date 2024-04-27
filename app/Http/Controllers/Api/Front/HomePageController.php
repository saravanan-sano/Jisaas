<?php

namespace App\Http\Controllers\Api\Front;

use App\Classes\Common;
use App\Http\Controllers\ApiBaseController;
use App\Http\Requests\Api\Front\Auth\LoginRequest;
use App\Http\Requests\Api\Front\Auth\SignupRequest;
use App\Http\Requests\Api\Front\Auth\ProfileRequest;
use App\Http\Requests\Api\Front\Auth\RefreshTokenRequest;
use App\Models\Category;
use App\Models\Customer;
use App\Models\FrontProductCard;
use App\Models\FrontWebsiteSettings;
use App\Models\Product;
use App\Models\UserDetails;
use App\Models\Warehouse;
use App\Models\Company;
use App\Models\Currency;
use App\Models\Tax;
use Carbon\Carbon;
use Examyou\RestAPI\ApiResponse;
use Examyou\RestAPI\Exceptions\ApiException;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Claims\Custom;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Models\Translation;
use App\Models\User;

class HomePageController extends ApiBaseController
{
    public function app($storeSlug)
    {

        $warehouse = Warehouse::where('slug', $storeSlug)->first();

        if (!$warehouse) {
            throw new ApiException("Not a valid warehouse");
        }

        $company = Company::where('id', $warehouse->company_id)->first();


        $settings = FrontWebsiteSettings::withoutGlobalScope('current_warehouse')
            ->where('warehouse_id', $warehouse->id)->first();
        $currency = Currency::where('id', $company->currency_id)->first();
        //$warehouse_selected = Warehouse::where('id', $company->currency_id)->first();


        $settings->currency = $currency;
        $settings->warehouse = $warehouse;
        $settings->company = $company;

        return ApiResponse::make('App settings fetched', [
            'app' => $settings,
            'warehouse' => $warehouse
        ]);
    }

    public function homepage($storeSlug)
    {
        $warehouse = Warehouse::where('slug', $storeSlug)->first();

        if (!$warehouse) {
            throw new ApiException("Not a valid warehouse");
        }

        $categories = Category::select('id', 'name', 'slug', 'image')->where("company_id", $warehouse->company_id)->get();
        $products = Product::select('id', 'name', 'slug', 'image', 'description', 'category_id', 'brand_id')
            ->with(['details:id,product_id,sales_price,mrp,tax_id,sales_tax_type', 'details.tax:id,rate', 'category:id,name,slug', 'brand:id,name,slug'])->where("warehouse_id", $warehouse->id)
            ->take(20)->get();
        $frontSettings = FrontWebsiteSettings::withoutGlobalScope('current_warehouse')
            ->where('warehouse_id', $warehouse->id)->first();
        $frontProductCards = FrontProductCard::withoutGlobalScope('current_warehouse')
            ->where('warehouse_id', $warehouse->id)
            ->get();
        // $frontTranslation = Translation::where('lang_id',1)
        // 	->get();

        return ApiResponse::make('App settings fetched', [
            'categories' => $categories,
            'products' => $products,
            'front_settings' => $frontSettings,
            'front_product_cards' => $frontProductCards,
            // 'language' => $frontTranslation,
        ]);
    }

    public function front_homepage_products($storeSlug)
    {
        $warehouse = Warehouse::where('slug', $storeSlug)->first();

        if (!$warehouse) {
            throw new ApiException("Not a valid warehouse");
        }

        $categories = Category::select('id', 'name', 'slug', 'image')->where("company_id", $warehouse->company_id)->get();

        $frontProductCards = FrontProductCard::withoutGlobalScope('current_warehouse')
            ->where('warehouse_id', $warehouse->id)
            ->get();

        //// Log::info($frontProductCards['products_details']);

        // $updatedFrontProductCards = $frontProductCards->map(function ($product) {
        //     $product->products_details = collect($product->products_details)->map(function ($productDetail) {
        //         $productDetails = $productDetail['details'];
        //         $taxrate = $productDetails['tax'];

        //         $tax = Tax::find($productDetails['tax_id']);

        //         $taxType = $productDetails['sales_tax_type'];
        //         $unitPrice = $productDetails['sales_price'];

        //         $singleUnitPrice = $unitPrice;

        //         if ($taxrate && $taxrate != '') {
        //             $taxRate = $taxrate['rate'];

        //             if ($taxType == 'inclusive') {
        //                 $subTotal = $singleUnitPrice;
        //                 $singleUnitPrice = ($singleUnitPrice * 100) / (100 + $taxRate);
        //                 $taxAmount = ($singleUnitPrice) * ($taxRate / 100);
        //             } else {
        //                 $taxAmount = ($singleUnitPrice * ($taxRate / 100));
        //                 $subTotal = $singleUnitPrice + $taxAmount;
        //                 $singleUnitPrice = $subTotal;
        //             }
        //         } else {
        //             $taxAmount = 0;
        //             $taxRate = 0;
        //             $subTotal = $singleUnitPrice;
        //         }

        //         // Update the sales_price in the response array
        //         $productDetail['details']['sales_price'] = $subTotal;

        //         return $productDetail;
        //     })->toArray();

        //     return $product;
        // });

        return ApiResponse::make('App settings fetched', [
            'categories' => $categories,
            'products' => $frontProductCards,
            // 'language' => $frontTranslation,
        ]);


        // $frontProductCards = FrontProductCard::withoutGlobalScope('current_warehouse')
        // 	->where('warehouse_id', $warehouse->id)
        // 	->get();


        // $frontProductCards1 = $frontProductCards->each(function ($product) {
        // 	collect($product->products_details)->each(function (&$productDetail) {
        // 		$productDetails = $productDetail['details'];
        // 		$taxrate = $productDetails['tax'];

        // 		$tax = Tax::find($productDetails['tax_id']);

        // 		$taxType = $productDetails['sales_tax_type'];
        // 		$unitPrice = $productDetails['sales_price'];

        // 		$singleUnitPrice = $unitPrice;

        // 		if ($taxrate && $taxrate != '') {
        // 			$taxRate = $taxrate['rate'];

        // 			if ($taxType == 'inclusive') {
        // 				$subTotal = $singleUnitPrice;
        // 				$singleUnitPrice = ($singleUnitPrice * 100) / (100 + $taxRate);
        // 				$taxAmount = ($singleUnitPrice) * ($taxRate / 100);
        // 			} else {
        // 				$taxAmount = ($singleUnitPrice * ($taxRate / 100));
        // 				$subTotal = $singleUnitPrice + $taxAmount;
        // 				$singleUnitPrice = $subTotal;
        // 			}
        // 		} else {
        // 			$taxAmount = 0;
        // 			$taxRate = 0;
        // 			$subTotal = $singleUnitPrice;
        // 		}

        // 		// Update the sales_price inside the loop
        // 		$productDetail['details']['sales_price'] = $subTotal;
        // 		// Log::info($productDetail['details']['sales_price']);
        // 	});
        // });

        // $frontProductCards1 = $frontProductCards->transform(function ($product) {
        // 	collect($product->products_details)->each(function (&$productDetail) {
        // 		$productDetails = $productDetail['details'];
        // 		$taxrate = $productDetails['tax'];

        // 		$tax = Tax::find($productDetails['tax_id']);

        // 		$taxType = $productDetails['sales_tax_type'];
        // 		$unitPrice = $productDetails['sales_price'];

        // 		$singleUnitPrice = $unitPrice;

        // 		if ($taxrate && $taxrate != '') {
        // 			$taxRate = $taxrate['rate'];

        // 			if ($taxType == 'inclusive') {
        // 				$subTotal = $singleUnitPrice;
        // 				$singleUnitPrice = ($singleUnitPrice * 100) / (100 + $taxRate);
        // 				$taxAmount = ($singleUnitPrice) * ($taxRate / 100);
        // 			} else {
        // 				$taxAmount = ($singleUnitPrice * ($taxRate / 100));
        // 				$subTotal = $singleUnitPrice + $taxAmount;
        // 				$singleUnitPrice = $subTotal;
        // 			}
        // 		} else {
        // 			$taxAmount = 0;
        // 			$taxRate = 0;
        // 			$subTotal = $singleUnitPrice;
        // 		}

        // 		// Update the sales_price inside the loop
        // 		$productDetail['details']['sales_price'] = $subTotal;
        // 		// Log::info($productDetail['details']['sales_price']);
        // 	});

        // 	return $product;
        // });

        // 			$frontProductCards1 = $frontProductCards->map(function ($product) {
        // 				$product->products_details = collect($product->products_details)->map(function ($productDetail) {
        // 					$productDetails = $productDetail['details'];
        // 					$taxrate = $productDetails['tax'];

        // 					$tax = Tax::find($productDetails['tax_id']);

        // 					$taxType = $productDetails['sales_tax_type'];
        // 					$unitPrice = $productDetails['sales_price'];

        // 					$singleUnitPrice = $unitPrice;

        // 					if ($taxrate && $taxrate != '') {
        // 						$taxRate = $taxrate['rate'];

        // 						if ($taxType == 'inclusive') {
        // 							$subTotal = $singleUnitPrice;
        // 							$singleUnitPrice = ($singleUnitPrice * 100) / (100 + $taxRate);
        // 							$taxAmount = ($singleUnitPrice) * ($taxRate / 100);
        // 						} else {
        // 							$taxAmount = ($singleUnitPrice * ($taxRate / 100));
        // 							$subTotal = $singleUnitPrice + $taxAmount;
        // 							$singleUnitPrice = $subTotal;
        // 						}
        // 					} else {
        // 						$taxAmount = 0;
        // 						$taxRate = 0;
        // 						$subTotal = $singleUnitPrice;
        // 					}

        // 					// Update the sales_price inside the loop
        // 					$productDetail['details']['sales_price'] = $subTotal;

        // 					// Assuming $productDetail is an Eloquent model, save the changes
        // 					$productDetail->save();

        // 					return $productDetail;
        // 				})->toArray();

        // 				return $product;
        // 			});

        // // Log::info("VAL".$frontProductCards1);
        // 		// $frontTranslation = Translation::where('lang_id',1)
        // 		// 	->get();

        // 		return ApiResponse::make('App settings fetched', [
        // 			'categories' => $categories,
        // 			'products' => $frontProductCards1,
        // 			// 'language' => $frontTranslation,
        // 		]);
    }



    public function front_homepage_productsById($storeSlug, $productId)
    {
        $warehouse = Warehouse::where('slug', $storeSlug)->first();
        $user = auth('api_front')->user();

        if (!$warehouse) {
            throw new ApiException("Not a valid warehouse");
        }

        $productId = $this->getIdFromHash($productId);


        $Product = Product::withoutGlobalScope('current_warehouse')->with('details', 'front_wholesale', 'details.tax:rate,name,id')
            ->where('warehouse_id', $warehouse->id)
            ->where('id', $productId)
            ->first();

        $productDetails = $Product->details;
        $tax = Tax::find($productDetails->tax_id);

        $taxType = $productDetails->sales_tax_type;
        $unitPrice = $productDetails->sales_price;

        $singleUnitPrice = $unitPrice;

        if ($tax && $tax->rate != '') {
            $taxRate = $tax->rate;

            if ($taxType == 'inclusive') {
                $subTotal = $singleUnitPrice;
                $singleUnitPrice =  ($singleUnitPrice * 100) / (100 + $taxRate);
                $taxAmount = ($singleUnitPrice) * ($taxRate / 100);
            } else {
                $taxAmount =  ($singleUnitPrice * ($taxRate / 100));
                $subTotal = $singleUnitPrice + $taxAmount;
                $singleUnitPrice = $subTotal;
                $Product->details->sales_price = $subTotal;

                foreach ($Product->front_wholesale as $wholesale) {
                    // Add taxAmount to each wholesale_price
                    $taxAmount =  ($wholesale->wholesale_price * ($taxRate / 100));
                    $wholesale->wholesale_price += $taxAmount;

                    // Round to two decimal places
                    //	$wholesale->wholesale_price = number_format($wholesale->wholesale_price, 2, '.', '');
                }
            }
        } else {
            $taxAmount = 0;
            $taxRate = 0;
            $subTotal = $singleUnitPrice;
        }

        $Product->total_tax = $taxAmount;
        $Product->tax_rate = $taxRate;
        $Product->tax_type = $taxType;
        $Product->single_unit_price = $singleUnitPrice;


        $Product->user = $user;

        // $frontTranslation = Translation::where('lang_id',1)
        // 	->get();

        return ApiResponse::make('App settings fetched', [$Product]);
    }

    public function login(LoginRequest $request)
    {
        $loginIdentifier = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        $credentials = [
            $loginIdentifier =>  $request->email,
            'password' =>  $request->password,
            'user_type' => 'customers',
            'company_id' => $request->company_id,
        ];

        $user = User::where($loginIdentifier, $request->email)
            ->where("user_type", 'customers')
            ->where("company_id", $request->company_id)
            ->where("is_deleted", 0)
            ->first();

        if ($user) {
            if (!$token = auth('api_front')->attempt($credentials)) {
                return response()->json([
                    'error_message' =>  __('auth.login_failed')
                ], 406);
            } elseif ($user->status == 'waiting') {
                return response()->json([
                    'error_message' =>  __('auth.user_not_verified')
                ], 406);
            } elseif ($user->status == 'disabled') {
                return response()->json([
                    'error_message' =>  __('user deactivated')
                ], 406);
            } else {
                return $this->respondWithToken($token);
            }
        } else {
            return response()->json([
                'error_message' => "Your account is disabled contact the administrator"
            ], 406);
        }
    }


    public function signup(SignupRequest $request)
    {
        $newCustomer = new Customer();
        $newCustomer->name = $request->name;
        $newCustomer->email = $request->email;
        $newCustomer->phone = $request->phone;
        $newCustomer->password = $request->password;
        $newCustomer->company_id = $request->company_id;

        // changed by suriya
        // assigned by kali
        $newCustomer->tax_number = $request->tax_number;
        $newCustomer->address = $request->address;
        $newCustomer->pincode = $request->pincode;

        // added by kali on 02/04/2024
        $newCustomer->location = $request->location;
        $newCustomer->business_type = $request->business_type;



        // if (User::where('email', '=', $newCustomer->email)->count() > 0) {
        //     return response()->json([
        //         'error_message' =>  'Email address is already in Existing!'
        //     ], 406);
        // }

        $newCustomer->status = "disabled";
        if ($request->has('addional_documents')) {
            $base64Image = $request->input('addional_documents');
            $image = explode('base64,', $base64Image);
            $image = end($image);
            $image = str_replace(' ', '+', $image);
            $imageName = time() . '.png';
            $imagePath = public_path('uploads/users/') . $imageName;
            $decodedImage = base64_decode($image);

            if ($decodedImage === false) {
                throw new \Exception('Failed to decode base64 image');
            }

            file_put_contents($imagePath, $decodedImage);
            $newCustomer->addional_documents = $imageName;
        }
        // end edit from 02-01-2024

        // $newCustomer->email_verification_code = Str::random(20);
        // $newCustomer->status = 'waiting';
        $newCustomer->save();

        // Storing Customer details for each warehouse
        $allWarehouses = Warehouse::select('id')->where('company_id', $request->company_id)->get();
        foreach ($allWarehouses as $allWarehouse) {
            $customerDetails = new UserDetails();
            $customerDetails->warehouse_id = $allWarehouse->id;
            $customerDetails->user_id = $newCustomer->id;
            $customerDetails->credit_period = 30;
            $customerDetails->save();
        }

        // TODO - Notifying to Warehouse and customer


        return ApiResponse::make('Signup successfully', []);
    }

    protected function respondWithToken($token)
    {
        $user = auth('api_front')->user();

        return ApiResponse::make('Loggged in successfull', [
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Carbon::now()->addDays(180),
            'user' => $user
        ]);
    }

    public function refreshToken(RefreshTokenRequest $request)
    {

        $newToken = auth('api_front')->refresh();

        return $this->respondWithToken($newToken);
    }

    public function logout()
    {
        auth('api')->logout();

        return ApiResponse::make(__('Session closed successfully'));
    }

    public function user()
    {
        $user = auth('api_front')->user();

        return ApiResponse::make('Data successfull', [
            'user' => $user
        ]);
    }

    public function profile(ProfileRequest $request)
    {

        $user = auth('api_front')->user();

        $user->name = $request->name;
        if ($request->has('profile_image')) {
            $user->profile_image = $request->profile_image;
        }
        $user->email = $request->email;
        $user->password = $request->password;
        $user->phone = $request->phone;
        $user->address = $request->address;
        $user->shipping_address = $request->shipping_address;
        $user->save();

        return ApiResponse::make('Profile updated successfull', [
            'user' => $user
        ]);
    }

    public function uploadFile(Request $request)
    {
        $result = Common::uploadFile($request);

        return ApiResponse::make('File Uploaded', $result);
    }

    public function categories(Request $request)
    {
        $categories = Category::all();

        return ApiResponse::make('Data Fetched', [
            'categories' => $categories
        ]);
    }

    public function categoryBySlug(Request $request)
    {
        $slug = $request->slug;
        $category = Category::select('id', 'name', 'slug')->where('slug', $slug)->first();

        return ApiResponse::make('Data Fetched', [
            'category' => $category
        ]);
    }
}
