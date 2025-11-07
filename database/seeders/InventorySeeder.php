<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventorySeeder extends Seeder
{
    public function run()
    {
        DB::table('inventories')->delete();

        $inventories = [
            [
                'inv_unit' => 'kg',
                'inv_stock_quantity' => 50,
                'inv_expire_date' => now()->addMonths(6),
                'reorder_level' => 10,
                'item_id' => 1,
            ],
            [
                'inv_unit' => 'kg',
                'inv_stock_quantity' => 30,
                'inv_expire_date' => now()->addMonths(12),
                'reorder_level' => 15,
                'item_id' => 2,
            ],
            [
                'inv_unit' => 'piece',
                'inv_stock_quantity' => 200,
                'inv_expire_date' => now()->addWeeks(2),
                'reorder_level' => 50,
                'item_id' => 3,
            ],
            [
                'inv_unit' => 'kg',
                'inv_stock_quantity' => 25,
                'inv_expire_date' => now()->addMonths(3),
                'reorder_level' => 8,
                'item_id' => 4,
            ],
            [
                'inv_unit' => 'piece',
                'inv_stock_quantity' => 100,
                'inv_expire_date' => null,
                'reorder_level' => 25,
                'item_id' => 5,
            ],
        ];

        foreach ($inventories as $inventory) {
            DB::table('inventories')->insert(array_merge($inventory, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('Inventory seeded successfully!');
    }
}