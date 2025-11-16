<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriesSeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['cat_name' => 'Flour & Baking Mixes'],
            ['cat_name' => 'Dairy Products'],
            ['cat_name' => 'Eggs'],
            ['cat_name' => 'Sugar & Sweeteners'],
            ['cat_name' => 'Butter & Oil'],
            ['cat_name' => 'Chocolate & Cocoa'],
            ['cat_name' => 'Nuts & Seeds'],
            ['cat_name' => 'Fruits & Fillings'],
            ['cat_name' => 'Baking Supplies'],
            ['cat_name' => 'Decorations'],
            ['cat_name' => 'Packaging Materials'],
            ['cat_name' => 'Cleaning Supplies'],
        ];

        foreach ($categories as $index => $category) {
            DB::table('categories')->insert([
                'cat_id' => $index + 1,
                'cat_name' => $category['cat_name'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}