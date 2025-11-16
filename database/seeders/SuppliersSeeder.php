<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SuppliersSeeder extends Seeder
{
    public function run()
    {
        $suppliers = [
            [
                'sup_id' => 1,
                'sup_name' => 'Flour & More Trading',
                'sup_email' => 'sales@flourandmore.com',
                'sup_address' => '123 Commerce Street, Makati City, Metro Manila',
                'contact_person' => 'Michael Torres',
                'contact_number' => '+63 917 111 2222',
                'sup_status' => 'active',
            ],
            [
                'sup_id' => 2,
                'sup_name' => 'Dairy Delights Corporation',
                'sup_email' => 'orders@dairydelights.ph',
                'sup_address' => '456 Industrial Road, Quezon City, Metro Manila',
                'contact_person' => 'Sarah Lopez',
                'contact_number' => '+63 917 333 4444',
                'sup_status' => 'active',
            ],
            [
                'sup_id' => 3,
                'sup_name' => 'Sweet Solutions Supply',
                'sup_email' => 'info@sweetsolutions.ph',
                'sup_address' => '789 Sugar Street, Cavite City',
                'contact_person' => 'James Wilson',
                'contact_number' => '+63 917 555 6666',
                'sup_status' => 'active',
            ],
            [
                'sup_id' => 4,
                'sup_name' => 'Chocolate House Imports',
                'sup_email' => 'chocolate@houseimports.com',
                'sup_address' => '321 Chocolate Avenue, Pampanga',
                'contact_person' => 'Elena Rodriguez',
                'contact_number' => '+63 917 777 8888',
                'sup_status' => 'active',
            ],
            [
                'sup_id' => 5,
                'sup_name' => 'NutriSeeds Distribution',
                'sup_email' => 'nuts@nutriseeds.ph',
                'sup_address' => '654 Healthy Drive, Laguna',
                'contact_person' => 'Carlos Martinez',
                'contact_number' => '+63 917 999 0000',
                'sup_status' => 'active',
            ],
            [
                'sup_id' => 6,
                'sup_name' => 'Fresh Produce Market',
                'sup_email' => 'fresh@producemarket.com',
                'sup_address' => '987 Green Valley Road, Bulacan',
                'contact_person' => 'Anna Green',
                'contact_number' => '+63 917 123 4567',
                'sup_status' => 'active',
            ],
            [
                'sup_id' => 7,
                'sup_name' => 'Packaging Pro',
                'sup_email' => 'contact@packagingpro.ph',
                'sup_address' => '246 Box Street, Metro Manila',
                'contact_person' => 'Peter Chen',
                'contact_number' => '+63 917 654 3210',
                'sup_status' => 'active',
            ],
            [
                'sup_id' => 8,
                'sup_name' => 'CleanSupply Inc',
                'sup_email' => 'clean@cleansupply.ph',
                'sup_address' => '135 Soap Lane, Manila',
                'contact_person' => 'Lisa Wang',
                'contact_number' => '+63 917 987 6543',
                'sup_status' => 'active',
            ],
            [
                'sup_id' => 9,
                'sup_name' => 'DecoCraft Supplies',
                'sup_email' => 'deco@decocraft.ph',
                'sup_address' => '753 Decor Street, Taguig City',
                'contact_person' => 'Mark Taylor',
                'contact_number' => '+63 917 456 7890',
                'sup_status' => 'active',
            ],
            [
                'sup_id' => 10,
                'sup_name' => 'Baking Essentials Co',
                'sup_email' => 'baking@essentials.ph',
                'sup_address' => '864 Flour Road, Angeles City',
                'contact_person' => 'Julie Kim',
                'contact_number' => '+63 917 789 1234',
                'sup_status' => 'active',
            ],
        ];

        foreach ($suppliers as $supplier) {
            DB::table('suppliers')->insert([
                'sup_id' => $supplier['sup_id'],
                'sup_name' => $supplier['sup_name'],
                'sup_email' => $supplier['sup_email'],
                'sup_address' => $supplier['sup_address'],
                'contact_person' => $supplier['contact_person'],
                'contact_number' => $supplier['contact_number'],
                'sup_status' => $supplier['sup_status'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}