<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PurchaseItemsSeeder extends Seeder
{
    public function run()
    {
        $purchaseItems = [
            // Purchase Order 1 items
            [
                'pi_quantity' => 50,
                'pi_unit_price' => 45.00,
                'pi_subtotal' => 2250.00,
                'po_id' => 1,
                'item_id' => 1, // All-Purpose Flour
                'created_at' => '2024-11-15 10:00:00',
                'updated_at' => '2024-11-15 10:00:00'
            ],
            [
                'pi_quantity' => 100,
                'pi_unit_price' => 6.00,
                'pi_subtotal' => 600.00,
                'po_id' => 1,
                'item_id' => 7, // Fresh Eggs
                'created_at' => '2024-11-15 10:00:00',
                'updated_at' => '2024-11-15 10:00:00'
            ],

            // Purchase Order 2 items
            [
                'pi_quantity' => 30,
                'pi_unit_price' => 35.00,
                'pi_subtotal' => 1050.00,
                'po_id' => 2,
                'item_id' => 10, // Butter (Unsalted)
                'created_at' => '2024-11-10 15:45:00',
                'updated_at' => '2024-11-10 15:45:00'
            ],
            [
                'pi_quantity' => 5,
                'pi_unit_price' => 30.00,
                'pi_subtotal' => 150.00,
                'po_id' => 2,
                'item_id' => 16, // Testing Item Low Stock (decorator supplies)
                'created_at' => '2024-11-10 15:45:00',
                'updated_at' => '2024-11-10 15:45:00'
            ],

            // Purchase Order 3 items (draft)
            [
                'pi_quantity' => 25,
                'pi_unit_price' => 50.00,
                'pi_subtotal' => 1250.00,
                'po_id' => 3,
                'item_id' => 2, // Cake Flour
                'created_at' => '2024-11-15 10:30:00',
                'updated_at' => '2024-11-15 10:30:00'
            ],
            [
                'pi_quantity' => 15,
                'pi_unit_price' => 21.67,
                'pi_subtotal' => 325.00,
                'po_id' => 3,
                'item_id' => 8, // Granulated Sugar
                'created_at' => '2024-11-15 10:30:00',
                'updated_at' => '2024-11-15 10:30:00'
            ],

            // Purchase Order 4 items
            [
                'pi_quantity' => 1000,
                'pi_unit_price' => 2.80,
                'pi_subtotal' => 2800.00,
                'po_id' => 4,
                'item_id' => 7, // Fresh Eggs
                'created_at' => '2024-11-14 18:15:00',
                'updated_at' => '2024-11-14 18:15:00'
            ],
            [
                'pi_quantity' => 50,
                'pi_unit_price' => 8.00,
                'pi_subtotal' => 400.00,
                'po_id' => 4,
                'item_id' => 4, // Fresh Milk
                'created_at' => '2024-11-14 18:15:00',
                'updated_at' => '2024-11-14 18:15:00'
            ],

            // Purchase Order 5 items
            [
                'pi_quantity' => 200,
                'pi_unit_price' => 45.00,
                'pi_subtotal' => 9000.00,
                'po_id' => 5,
                'item_id' => 1, // All-Purpose Flour
                'created_at' => '2024-11-15 14:00:00',
                'updated_at' => '2024-11-15 14:00:00'
            ],
            [
                'pi_quantity' => 200,
                'pi_unit_price' => 6.00,
                'pi_subtotal' => 1200.00,
                'po_id' => 5,
                'item_id' => 7, // Fresh Eggs
                'created_at' => '2024-11-15 14:00:00',
                'updated_at' => '2024-11-15 14:00:00'
            ],
            [
                'pi_quantity' => 150,
                'pi_unit_price' => 8.00,
                'pi_subtotal' => 1200.00,
                'po_id' => 5,
                'item_id' => 4, // Fresh Milk
                'created_at' => '2024-11-15 14:00:00',
                'updated_at' => '2024-11-15 14:00:00'
            ],

            // Purchase Order 6 items (cancelled - for testing)
            [
                'pi_quantity' => 10,
                'pi_unit_price' => 25.00,
                'pi_subtotal' => 250.00,
                'po_id' => 6,
                'item_id' => 17, // Testing Item Critical Stock
                'created_at' => '2024-11-12 12:00:00',
                'updated_at' => '2024-11-13 08:30:00'
            ],
            [
                'pi_quantity' => 5,
                'pi_unit_price' => 50.00,
                'pi_subtotal' => 250.00,
                'po_id' => 6,
                'item_id' => 11, // Vegetable Oil
                'created_at' => '2024-11-12 12:00:00',
                'updated_at' => '2024-11-13 08:30:00'
            ]
        ];

        foreach ($purchaseItems as $item) {
            DB::table('purchase_items')->insert($item);
        }
    }
}