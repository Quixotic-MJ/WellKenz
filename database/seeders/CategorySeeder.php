<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    public function run()
    {
        DB::table('categories')->delete();

        $categories = [
            ['cat_name' => 'Raw Materials'],
            ['cat_name' => 'Baking Ingredients'],
            ['cat_name' => 'Packaging Materials'],
            ['cat_name' => 'Equipment'],
            ['cat_name' => 'Office Supplies'],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->insert([
                'cat_name' => $category['cat_name'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Categories seeded successfully!');
    }
}