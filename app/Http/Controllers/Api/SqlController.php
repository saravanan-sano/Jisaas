<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\ApiBaseController;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;

class SqlController  extends ApiBaseController
{
    public function downloadSql(Request $request)
    {
        $company = company();

        // Set the filename
        $filename = 'data.sql';

        // Set response headers
        $headers = [
            'Content-type'        => 'text/plain',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        // Open a stream to write the response
        $stream = fopen('php://temp', 'r+');

        // Write headers to the stream
        foreach ($headers as $header => $value) {
            header($header . ': ' . $value);
        }

        // Chunk size for fetching data from the database
        $chunkSize = 1000; // Adjust the chunk size as needed

        // Chunked response for users
        User::where('company_id', $company->id)->chunk($chunkSize, function ($users) use ($stream) {
            foreach ($users as $user) {
                fwrite($stream, "INSERT INTO users (`id`, `company_id`, `is_superadmin`, `warehouse_id`, `role_id`, `lang_id`, `user_type`, `accounttype`, `is_walkin_customer`, `login_enabled`, `name`, `email`, `password`, `phone`, `contact_name`, `mobile`, `profile_image`, `addional_documents`, `address`, `pincode`, `location`, `business_type`, `shipping_address`, `email_verification_code`, `mobile_otp`, `email_otp`, `status`, `reset_code`, `timezone`, `date_format`, `date_picker_format`, `time_format`, `tax_number`, `created_by`, `created_at`, `updated_at`, `is_verified`, `country_code`, `user_agent`, `is_wholesale_customer`, `assign_to`, `is_deleted`) VALUES ('{$user->id}', '{$user->company_id}', '{$user->is_superadmin}', '{$user->warehouse_id}', '{$user->role_id}', '{$user->lang_id}', '{$user->user_type}', '{$user->accounttype}', '{$user->is_walkin_customer}', '{$user->login_enabled}', '{$user->name}', '{$user->email}', '{$user->password}', '{$user->phone}', '{$user->contact_name}', '{$user->mobile}', '{$user->profile_image}', '{$user->addional_documents}', '{$user->address}', '{$user->pincode}', '{$user->location}', '{$user->business_type}', '{$user->shipping_address}', '{$user->email_verification_code}', '{$user->mobile_otp}', '{$user->email_otp}', '{$user->status}', '{$user->reset_code}', '{$user->timezone}', '{$user->date_format}', '{$user->date_picker_format}', '{$user->time_format}', '{$user->tax_number}', '{$user->created_by}', '{$user->created_at}', '{$user->updated_at}', '{$user->is_verified}', '{$user->country_code}', '{$user->user_agent}', '{$user->is_wholesale_customer}', '{$user->assign_to}', '{$user->is_deleted}');\n"); // Write SQL statement to the stream
            }
        });

        Category::where('company_id', $company->id)->chunk($chunkSize, function ($categories) use ($stream) {
            foreach ($categories as $category) {
                fwrite($stream, "INSERT INTO `categories` (`id`, `company_id`, `name`, `slug`, `image`, `parent_id`, `created_at`, `updated_at`) VALUES ('{$category->id}','{$category->company_id}', '{$category->name}', '{$category->slug}', '{$category->image}', '{$category->parent_id}', '{$category->created_at}', '{$category->updated_at}');\n"); //

            }});

        // Chunked response for products
        Product::where('company_id', $company->id)->chunk($chunkSize, function ($products) use ($stream) {
            foreach ($products as $product) {
                fwrite($stream, "INSERT INTO `products` (`id`, `company_id`, `warehouse_id`, `name`, `slug`, `barcode_symbology`, `item_code`, `image`, `category_id`, `brand_id`, `unit_id`, `description`, `user_id`, `created_at`, `updated_at`, `hsn_sac_code`, `product_code`, `identity_code`, `is_default`) VALUES
                ('{$product->id}', '{$product->company_id}', '{$product->warehouse_id}', '{$product->name}', '{$product->slug}', '{$product->barcode_symbology}', '{$product->item_code}', NULL, '{$product->category_id}',  '{$product->brand_id}',  '{$product->unit_id}',  '{$product->description}',  '{$product->user_id}',  '{$product->created_at}',  '{$product->updated_at}',  '{$product->hsn_sac_code}',  '{$product->product_code}',  '{$product->identity_code}', 0);\n"); // Write SQL statement to the stream
            }
        });

        // Rewind the stream to the beginning
        rewind($stream);

        // Return the stream as a downloadable response
        return response()->stream(function () use ($stream) {
            fpassthru($stream); // Output the stream to the client
            fclose($stream);    // Close the stream
        }, 200, $headers);
    }

}
