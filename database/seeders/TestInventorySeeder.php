<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Item;
use App\Models\CurrentStock;

class TestInventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create categories if they don't exist
        $categories = [
            ['name' => 'Flour & Grains', 'description' => 'Flour, rice, and grain products'],
            ['name' => 'Dairy & Eggs', 'description' => 'Milk, cheese, eggs, and dairy products'],
            ['name' => 'Baking Supplies', 'description' => 'Yeast, sugar, salt, and baking essentials'],
            ['name' => 'Pantry Staples', 'description' => 'Basic cooking ingredients'],
        ];

        foreach ($categories as $categoryData) {
            Category::firstOrCreate(
                ['name' => $categoryData['name']],
                $categoryData + ['is_active' => true]
            );
        }

        // Create units if they don't exist
        $units = [
            ['name' => 'Kilogram', 'symbol' => 'kg', 'type' => 'mass'],
            ['name' => 'Gram', 'symbol' => 'g', 'type' => 'mass'],
            ['name' => 'Liter', 'symbol' => 'L', 'type' => 'volume'],
            ['name' => 'Piece', 'symbol' => 'pcs', 'type' => 'unit'],
            ['name' => 'Box', 'symbol' => 'box', 'type' => 'unit'],
        ];

        foreach ($units as $unitData) {
            Unit::firstOrCreate(
                ['symbol' => $unitData['symbol']],
                $unitData + [
                    'is_active' => true,
                    'conversion_factor' => 1.0
                ]
            );
        }

        // Get created categories and units
        $flourCategory = Category::where('name', 'Flour & Grains')->first();
        $dairyCategory = Category::where('name', 'Dairy & Eggs')->first();
        $bakingCategory = Category::where('name', 'Baking Supplies')->first();
        $pantryCategory = Category::where('name', 'Pantry Staples')->first();

        $kgUnit = Unit::where('symbol', 'kg')->first();
        $gUnit = Unit::where('symbol', 'g')->first();
        $literUnit = Unit::where('symbol', 'L')->first();
        $pieceUnit = Unit::where('symbol', 'pcs')->first();
        $boxUnit = Unit::where('symbol', 'box')->first();

        // Create test items
        $items = [
            [
                'name' => 'All-Purpose Flour',
                'item_code' => 'FLR-001',
                'description' => 'High-quality all-purpose flour for baking',
                'category_id' => $flourCategory->id,
                'unit_id' => $kgUnit->id,
                'min_stock_level' => 50,
                'max_stock_level' => 500,
                'reorder_point' => 100,
                'is_active' => true,
                'current_stock' => 250
            ],
            [
                'name' => 'Whole Wheat Flour',
                'item_code' => 'FLR-002',
                'description' => 'Organic whole wheat flour',
                'category_id' => $flourCategory->id,
                'unit_id' => $kgUnit->id,
                'min_stock_level' => 25,
                'max_stock_level' => 200,
                'reorder_point' => 50,
                'is_active' => true,
                'current_stock' => 75
            ],
            [
                'name' => 'Fresh Milk',
                'item_code' => 'MLK-001',
                'description' => 'Fresh whole milk 1L',
                'category_id' => $dairyCategory->id,
                'unit_id' => $literUnit->id,
                'min_stock_level' => 20,
                'max_stock_level' => 100,
                'reorder_point' => 30,
                'is_active' => true,
                'current_stock' => 15
            ],
            [
                'name' => 'Fresh Eggs',
                'item_code' => 'EGG-001',
                'description' => 'Farm fresh eggs (per piece)',
                'category_id' => $dairyCategory->id,
                'unit_id' => $pieceUnit->id,
                'min_stock_level' => 100,
                'max_stock_level' => 500,
                'reorder_point' => 150,
                'is_active' => true,
                'current_stock' => 50
            ],
            [
                'name' => 'Active Dry Yeast',
                'item_code' => 'YST-001',
                'description' => 'Fast-acting dry yeast for bread making',
                'category_id' => $bakingCategory->id,
                'unit_id' => $gUnit->id,
                'min_stock_level' => 500,
                'max_stock_level' => 2000,
                'reorder_point' => 800,
                'is_active' => true,
                'current_stock' => 300
            ],
            [
                'name' => 'Granulated Sugar',
                'item_code' => 'SUG-001',
                'description' => 'Fine granulated white sugar',
                'category_id' => $bakingCategory->id,
                'unit_id' => $kgUnit->id,
                'min_stock_level' => 30,
                'max_stock_level' => 200,
                'reorder_point' => 60,
                'is_active' => true,
                'current_stock' => 25
            ],
            [
                'name' => 'Salt',
                'item_code' => 'SLT-001',
                'description' => 'Coarse sea salt',
                'category_id' => $pantryCategory->id,
                'unit_id' => $kgUnit->id,
                'min_stock_level' => 10,
                'max_stock_level' => 50,
                'reorder_point' => 20,
                'is_active' => true,
                'current_stock' => 8
            ],
            [
                'name' => 'Baking Powder',
                'item_code' => 'BKP-001',
                'description' => 'Double-acting baking powder',
                'category_id' => $bakingCategory->id,
                'unit_id' => $gUnit->id,
                'min_stock_level' => 300,
                'max_stock_level' => 1000,
                'reorder_point' => 500,
                'is_active' => true,
                'current_stock' => 450
            ]
        ];

        foreach ($items as $itemData) {
            $currentStock = $itemData['current_stock'];
            unset($itemData['current_stock']);
            
            $item = Item::firstOrCreate(
                ['item_code' => $itemData['item_code']],
                $itemData
            );

            // Create or update current stock record
            CurrentStock::updateOrCreate(
                ['item_id' => $item->id],
                [
                    'current_quantity' => $currentStock,
                    'average_cost' => rand(10, 100) / 10, // Random cost between 1.0 and 10.0
                    'last_adjustment_date' => now(),
                    'updated_by' => 1 // Assuming admin user ID 1
                ]
            );
        }

        echo "Test inventory data created successfully!" . PHP_EOL;
        echo "Items created: " . Item::count() . PHP_EOL;
        echo "CurrentStock records: " . CurrentStock::count() . PHP_EOL;
    }
}