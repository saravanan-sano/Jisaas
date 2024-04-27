<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionTableSeeder25_04_2024 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            ['name' => 'quotations_create', 'display_name' => 'Quotation Create'],
            ['name' => 'quotations_view', 'display_name' => 'Quotation View'],
            ['name' => 'quotations_edit', 'display_name' => 'Quotation Edit'],
            ['name' => 'quotations_delete', 'display_name' => 'Quotation Delete'],
            ['name' => 'dashboard', 'display_name' => 'Dashboard'],
        ];

        // Insert data into the permissions table
        DB::table('permissions')->insert($permissions);
    }
}
