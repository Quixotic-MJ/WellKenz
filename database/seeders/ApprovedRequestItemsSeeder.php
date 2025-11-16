<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ApprovedRequestItemsSeeder extends Seeder
{
    public function run()
    {
        $approvedItems = [
            [
                'req_id' => 1,
                'item_id' => 1,
                'item_name' => 'All-Purpose Flour',
                'item_description' => 'Premium all-purpose flour for general baking needs',
                'item_unit' => 'kilogram',
                'requested_quantity' => 50.00,
                'approved_quantity' => 50.00,
                'req_ref' => 'REQ-2024-001',
                'created_as_item' => false,
                'created_at' => '2024-11-15 09:45:00',
                'updated_at' => '2024-11-15 09:45:00'
            ],
            [
                'req_id' => 1,
                'item_id' => 7,
                'item_name' => 'Fresh Eggs (Large)',
                'item_description' => 'Grade A large fresh eggs',
                'item_unit' => 'piece',
                'requested_quantity' => 100.00,
                'approved_quantity' => 100.00,
                'req_ref' => 'REQ-2024-001',
                'created_as_item' => false,
                'created_at' => '2024-11-15 09:45:00',
                'updated_at' => '2024-11-15 09:45:00'
            ],
            [
                'req_id' => 3,
                'item_id' => 10,
                'item_name' => 'Butter (Unsalted)',
                'item_description' => 'Premium unsalted butter for baking',
                'item_unit' => 'kilogram',
                'requested_quantity' => 30.00,
                'approved_quantity' => 30.00,
                'req_ref' => 'REQ-2024-003',
                'created_as_item' => false,
                'created_at' => '2024-11-10 15:30:00',
                'updated_at' => '2024-11-13 16:30:00'
            ],
            [
                'req_id' => 3,
                'item_id' => null,
                'item_name' => 'Professional Piping Tips Set',
                'item_description' => 'Complete piping tips set for cake decorating',
                'item_unit' => 'set',
                'requested_quantity' => 5.00,
                'approved_quantity' => 5.00,
                'req_ref' => 'REQ-2024-003',
                'created_as_item' => true,
                'created_at' => '2024-11-10 15:30:00',
                'updated_at' => '2024-11-13 16:30:00'
            ],
            [
                'req_id' => 6,
                'item_id' => 7,
                'item_name' => 'Fresh Eggs (Large)',
                'item_description' => 'Grade A large fresh eggs',
                'item_unit' => 'piece',
                'requested_quantity' => 1000.00,
                'approved_quantity' => 1000.00,
                'req_ref' => 'REQ-2024-006',
                'created_as_item' => false,
                'created_at' => '2024-11-14 18:00:00',
                'updated_at' => '2024-11-14 18:00:00'
            ],
            [
                'req_id' => 6,
                'item_id' => 4,
                'item_name' => 'Fresh Milk',
                'item_description' => 'Fresh whole milk for baking and consumption',
                'item_unit' => 'liter',
                'requested_quantity' => 50.00,
                'approved_quantity' => 50.00,
                'req_ref' => 'REQ-2024-006',
                'created_as_item' => false,
                'created_at' => '2024-11-14 18:00:00',
                'updated_at' => '2024-11-14 18:00:00'
            ]
        ];

        foreach ($approvedItems as $item) {
            DB::table('approved_request_items')->insert($item);
        }
    }
}