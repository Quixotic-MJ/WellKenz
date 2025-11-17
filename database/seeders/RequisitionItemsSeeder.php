<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RequisitionItemsSeeder extends Seeder
{
    public function run()
    {
        $requisitionItems = [
            // Requisition 1 items
            [
                'req_item_id' => 1,
                'req_item_quantity' => 50,
                'req_item_status' => 'fulfilled',
                'item_unit' => 'kilogram',
                'req_id' => 1,
                'item_id' => 1, // All-Purpose Flour
                'created_at' => '2024-11-15 08:30:00',
                'updated_at' => '2024-11-15 09:45:00'
            ],
            [
                'req_item_id' => 2,
                'req_item_quantity' => 100,
                'req_item_status' => 'fulfilled',
                'item_unit' => 'piece',
                'req_id' => 1,
                'item_id' => 7, // Fresh Eggs
                'created_at' => '2024-11-15 08:30:00',
                'updated_at' => '2024-11-15 09:45:00'
            ],
            [
                'req_item_id' => 3,
                'req_item_quantity' => 20,
                'req_item_status' => 'pending',
                'item_unit' => 'liter',
                'req_id' => 1,
                'item_id' => 4, // Fresh Milk
                'created_at' => '2024-11-15 08:30:00',
                'updated_at' => '2024-11-15 09:45:00'
            ],

            // Requisition 2 items
            [
                'req_item_id' => 4,
                'req_item_quantity' => 25,
                'req_item_status' => 'pending',
                'item_unit' => 'kilogram',
                'req_id' => 2,
                'item_id' => 2, // Cake Flour
                'created_at' => '2024-11-15 10:15:00',
                'updated_at' => '2024-11-15 10:15:00'
            ],
            [
                'req_item_id' => 5,
                'req_item_quantity' => 15,
                'req_item_status' => 'pending',
                'item_unit' => 'kilogram',
                'req_id' => 2,
                'item_id' => 8, // Granulated Sugar
                'created_at' => '2024-11-15 10:15:00',
                'updated_at' => '2024-11-15 10:15:00'
            ],

            // Requisition 3 items
            [
                'req_item_id' => 6,
                'req_item_quantity' => 30,
                'req_item_status' => 'fulfilled',
                'item_unit' => 'kilogram',
                'req_id' => 3,
                'item_id' => 10, // Butter (Unsalted)
                'created_at' => '2024-11-10 14:20:00',
                'updated_at' => '2024-11-13 16:30:00'
            ],
            [
                'req_item_id' => 7,
                'req_item_quantity' => 5,
                'req_item_status' => 'fulfilled',
                'item_unit' => 'set',
                'req_id' => 3,
                'item_id' => 16, // Testing Item Low Stock (as decorator supplies)
                'created_at' => '2024-11-10 14:20:00',
                'updated_at' => '2024-11-13 16:30:00'
            ],

            // Requisition 4 items (rejected)
            [
                'req_item_id' => 8,
                'req_item_quantity' => 10,
                'req_item_status' => 'pending',
                'item_unit' => 'piece',
                'req_id' => 4,
                'item_id' => 17, // Testing Item Critical Stock
                'created_at' => '2024-11-12 11:45:00',
                'updated_at' => '2024-11-13 08:30:00'
            ],

            // Requisition 5 items
            [
                'req_item_id' => 9,
                'req_item_quantity' => 200,
                'req_item_status' => 'pending',
                'item_unit' => 'kilogram',
                'req_id' => 5,
                'item_id' => 1, // All-Purpose Flour
                'created_at' => '2024-11-15 13:30:00',
                'updated_at' => '2024-11-15 13:30:00'
            ],
            [
                'req_item_id' => 10,
                'req_item_quantity' => 75,
                'req_item_status' => 'pending',
                'item_unit' => 'piece',
                'req_id' => 5,
                'item_id' => 7, // Fresh Eggs
                'created_at' => '2024-11-15 13:30:00',
                'updated_at' => '2024-11-15 13:30:00'
            ],
            [
                'req_item_id' => 11,
                'req_item_quantity' => 150,
                'req_item_status' => 'pending',
                'item_unit' => 'liter',
                'req_id' => 5,
                'item_id' => 4, // Fresh Milk
                'created_at' => '2024-11-15 13:30:00',
                'updated_at' => '2024-11-15 13:30:00'
            ],

            // Requisition 6 items (approved)
            [
                'req_item_id' => 12,
                'req_item_quantity' => 1000,
                'req_item_status' => 'fulfilled',
                'item_unit' => 'piece',
                'req_id' => 6,
                'item_id' => 7, // Fresh Eggs
                'created_at' => '2024-11-14 17:15:00',
                'updated_at' => '2024-11-14 18:00:00'
            ],
            [
                'req_item_id' => 13,
                'req_item_quantity' => 50,
                'req_item_status' => 'fulfilled',
                'item_unit' => 'liter',
                'req_id' => 6,
                'item_id' => 4, // Fresh Milk
                'created_at' => '2024-11-14 17:15:00',
                'updated_at' => '2024-11-14 18:00:00'
            ],

            // ----- ADDED THESE NEW ITEMS FOR REQ 7 -----
            [
                'req_item_id' => 14,
                'req_item_quantity' => 20,
                'req_item_status' => 'pending',
                'item_unit' => 'kilogram',
                'req_id' => 7,
                'item_id' => 3, // Bread Flour
                'created_at' => '2025-11-17 10:00:00',
                'updated_at' => '2025-11-17 10:00:00'
            ],
            [
                'req_item_id' => 15,
                'req_item_quantity' => 10,
                'req_item_status' => 'pending',
                'item_unit' => 'kilogram',
                'req_id' => 7,
                'item_id' => 5, // Yeast
                'created_at' => '2025-11-17 10:00:00',
                'updated_at' => '2025-11-17 10:00:00'
            ]
        ];

        foreach ($requisitionItems as $item) {
            DB::table('requisition_items')->insert($item);
        }
    }
}