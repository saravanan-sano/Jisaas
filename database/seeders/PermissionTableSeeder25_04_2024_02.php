<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionTableSeeder25_04_2024_02 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            ['name' => 'payment_report', 'display_name' => 'Payment Report'],
            ['name' => 'stock_alert_report', 'display_name' => 'Stock Alert Report'],
            ['name' => 'sales_summary_report', 'display_name' => 'Sales Summary Report'],
            ['name' => 'stock_summary_report', 'display_name' => 'Stock Summary Report'],
            ['name' => 'rate_list_report', 'display_name' => 'Rate List Report'],
            ['name' => 'product_expiry_report', 'display_name' => 'Product Expiry Report'],
            ['name' => 'product_sales_summary_report', 'display_name' => 'Product Sales Summary Report'],
            ['name' => 'sales_margin_report', 'display_name' => 'Sales Margin Report'],
            ['name' => 'gst_report', 'display_name' => 'GST Report'],
            ['name' => 'user_report', 'display_name' => 'User Report'],
            ['name' => 'referral_report', 'display_name' => 'Referral Report'],
            ['name' => 'staff_report', 'display_name' => 'Staff Report'],
            ['name' => 'staff_onboarding_report', 'display_name' => 'Staff Onboarding Report'],
            ['name' => 'day_report', 'display_name' => 'Day Report'],
            ['name' => 'attendance_report', 'display_name' => 'Attendance Report'],
            ['name' => 'category_wise_report', 'display_name' => 'Category Wise Report'],
            ['name' => 'customer_product_wise_report', 'display_name' => 'Customer Product Wise Report'],
            ['name' => 'profit_loss_report', 'display_name' => 'Profit Loss Report'],
        ];

        // Insert data into the permissions table
        DB::table('permissions')->insert($permissions);
    }
}
