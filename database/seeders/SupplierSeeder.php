<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    public function run()
    {
        DB::table('suppliers')->delete();

        $suppliers = [
            [
                'sup_name' => 'Baking Supplies Co.',
                'sup_email' => 'sales@bakingsupplies.com',
                'sup_address' => '123 Baker Street, Cityville',
                'contact_person' => 'John Supplier',
                'contact_number' => '+1 (555) 111-2222',
            ],
            [
                'sup_name' => 'Fresh Ingredients Ltd.',
                'sup_email' => 'orders@freshingredients.com',
                'sup_address' => '456 Farm Road, Countryside',
                'contact_person' => 'Mary Producer',
                'contact_number' => '+1 (555) 333-4444',
            ],
            [
                'sup_name' => 'Packaging Solutions Inc.',
                'sup_email' => 'info@packagingsolutions.com',
                'sup_address' => '789 Industrial Ave, Tech City',
                'contact_person' => 'Robert Packer',
                'contact_number' => '+1 (555) 555-6666',
            ],
        ];

        foreach ($suppliers as $supplier) {
            DB::table('suppliers')->insert(array_merge($supplier, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        $this->command->info('Suppliers seeded successfully!');
    }
}