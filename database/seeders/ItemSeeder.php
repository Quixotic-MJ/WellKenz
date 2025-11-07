<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemSeeder extends Seeder
{
    public function run()
    {
        DB::table('items')->delete();

        $items = [
            [
                'item_code' => 'FLR-001',
                'item_name' => 'All-Purpose Flour',
                'item_description' => 'High quality all-purpose flour for baking',
                'item_unit' => 'kg',
                'cat_id' => 2,
            ],
            [
                'item_code' => 'SGR-001',
                'item_name' => 'Granulated Sugar',
                'item_description' => 'Fine granulated white sugar',
                'item_unit' => 'kg',
                'cat_id' => 2,
            ],
            [
                'item_code' => 'EGG-001',
                'item_name' => 'Fresh Eggs',
                'item_description' => 'Grade A large eggs',
                'item_unit' => 'piece',
                'cat_id' => 1,
            ],
            [
                'item_code' => 'BTR-001',
                'item_name' => 'Unsalted Butter',
                'item_description' => 'Premium unsalted butter',
                'item_unit' => 'kg',
                'cat_id' => 1,
            ],
            [
                'item_code' => 'BOX-001',
                'item_name' => 'Pastry Box',
                'item_description' => 'Cardboard boxes for pastry packaging',
                'item_unit' => 'piece',
                'cat_id' => 3,
            ],
        ];

        foreach ($items as $item) {
            DB::table('items')->insert(array_merge($item, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('Items seeded successfully!');
    }
}