<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ItemSeeder extends Seeder
{
    public function run()
    {
        $items = [
            // Raw Materials (cat_id: 1)
            [
                'item_code' => 'EGG-001',
                'item_name' => 'Fresh Eggs',
                'item_description' => 'Grade A large eggs',
                'item_unit' => 'piece',
                'cat_id' => 1,
                'item_stock' => 120,
                'item_expire_date' => Carbon::now()->addDays(14)->format('Y-m-d'),
                'reorder_level' => 30,
                'min_stock_level' => 20,
                'max_stock_level' => 200,
                'is_active' => true,
                'is_custom' => false,        // NEW
                'last_updated' => now(),
            ],
            [
                'item_code' => 'BTR-001',
                'item_name' => 'Unsalted Butter',
                'item_description' => 'Premium unsalted butter',
                'item_unit' => 'kg',
                'cat_id' => 1,
                'item_stock' => 25.5,
                'item_expire_date' => Carbon::now()->addMonths(3)->format('Y-m-d'),
                'reorder_level' => 5,
                'min_stock_level' => 3,
                'max_stock_level' => 50,
                'is_active' => true,
                'is_custom' => false,        // NEW
                'last_updated' => now(),
            ],
            [
                'item_code' => 'MLK-001',
                'item_name' => 'Fresh Milk',
                'item_description' => 'Whole fresh milk',
                'item_unit' => 'liter',
                'cat_id' => 1,
                'item_stock' => 40.0,
                'item_expire_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
                'reorder_level' => 10,
                'min_stock_level' => 5,
                'max_stock_level' => 80,
                'is_active' => true,
                'is_custom' => false,        // NEW
                'last_updated' => now(),
            ],

            // Baking Ingredients (cat_id: 2)
            [
                'item_code' => 'FLR-001',
                'item_name' => 'All-Purpose Flour',
                'item_description' => 'High quality all-purpose flour for baking',
                'item_unit' => 'kg',
                'cat_id' => 2,
                'item_stock' => 150.75,
                'item_expire_date' => Carbon::now()->addMonths(12)->format('Y-m-d'),
                'reorder_level' => 25,
                'min_stock_level' => 15,
                'max_stock_level' => 200,
                'is_active' => true,
                'is_custom' => false,        // NEW
                'last_updated' => now(),
            ],
            [
                'item_code' => 'SGR-001',
                'item_name' => 'Granulated Sugar',
                'item_description' => 'Fine granulated white sugar',
                'item_unit' => 'kg',
                'cat_id' => 2,
                'item_stock' => 85.25,
                'item_expire_date' => Carbon::now()->addMonths(24)->format('Y-m-d'),
                'reorder_level' => 15,
                'min_stock_level' => 10,
                'max_stock_level' => 150,
                'is_active' => true,
                'is_custom' => false,        // NEW
                'last_updated' => now(),
            ],

            // Packaging Materials (cat_id: 3)
            [
                'item_code' => 'BOX-001',
                'item_name' => 'Pastry Box',
                'item_description' => 'Cardboard boxes for pastry packaging',
                'item_unit' => 'piece',
                'cat_id' => 3,
                'item_stock' => 500,
                'item_expire_date' => null,
                'reorder_level' => 100,
                'min_stock_level' => 50,
                'max_stock_level' => 1000,
                'is_active' => true,
                'is_custom' => false,        // NEW
                'last_updated' => now(),
            ],
            [
                'item_code' => 'BAG-001',
                'item_name' => 'Paper Bags',
                'item_description' => 'Brown paper bags for bread',
                'item_unit' => 'pack',
                'cat_id' => 3,
                'item_stock' => 75,
                'item_expire_date' => null,
                'reorder_level' => 20,
                'min_stock_level' => 10,
                'max_stock_level' => 200,
                'is_active' => true,
                'is_custom' => false,        // NEW
                'last_updated' => now(),
            ],
        ];

        foreach ($items as $item) {
            DB::table('items')->updateOrInsert(
                ['item_code' => $item['item_code']],
                $item
            );
        }

        $this->command->info('Items seeded successfully with is_custom flag!');
    }
}