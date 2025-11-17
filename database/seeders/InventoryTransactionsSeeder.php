<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InventoryTransactionsSeeder extends Seeder
{
    public function run()
    {
        $transactions = [
            // Stock In transactions
            [
                'trans_ref' => 'TRN-2024-001',
                'trans_type' => 'in',
                'trans_quantity' => 50,
                'trans_date' => '2024-11-15',
                'trans_remarks' => 'Purchase order delivery - emergency flour restock',
                'po_id' => 1,
                'trans_by' => 4, // Maria Garcia (Inventory Manager)
                'item_id' => 1, // All-Purpose Flour
                'created_at' => '2024-11-15 10:30:00',
                'updated_at' => '2024-11-15 10:30:00'
            ],
            [
                'trans_ref' => 'TRN-2024-002',
                'trans_type' => 'in',
                'trans_quantity' => 100,
                'trans_date' => '2024-11-15',
                'trans_remarks' => 'Purchase order delivery - emergency eggs restock',
                'po_id' => 1,
                'trans_by' => 4, // Maria Garcia (Inventory Manager)
                'item_id' => 7, // Fresh Eggs
                'created_at' => '2024-11-15 10:30:00',
                'updated_at' => '2024-11-15 10:30:00'
            ],
            
            // --- TRANSACTIONS FOR PO_ID 2 COMMENTED OUT ---
            // [
            //     'trans_ref' => 'TRN-2024-003',
            //     'trans_type' => 'in',
            //     'trans_quantity' => 30,
            //     'trans_date' => '2024-11-13',
            //     'trans_remarks' => 'Purchase order delivery - butter stock',
            //     'po_id' => 2,
            //     'trans_by' => 4, // Maria Garcia (Inventory Manager)
            //     'item_id' => 10, // Butter (Unsalted)
            //     'created_at' => '2024-11-13 14:00:00',
            //     'updated_at' => '2024-11-13 14:00:00'
            // ],
            // [
            //     'trans_ref' => 'TRN-2024-004',
            //     'trans_type' => 'in',
            //     'trans_quantity' => 5,
            //     'trans_date' => '2024-11-13',
            //     'trans_remarks' => 'Purchase order delivery - decorator supplies',
            //     'po_id' => 2,
            //     'trans_by' => 4, // Maria Garcia (Inventory Manager)
            //     'item_id' => 16, // Testing Item Low Stock
            //     'created_at' => '2024-11-13 14:00:00',
            //     'updated_at' => '2024-11-13 14:00:00'
            // ],
            // ----------------------------------------------

            [
                'trans_ref' => 'TRN-2024-005',
                'trans_type' => 'in',
                'trans_quantity' => 1000,
                'trans_date' => '2024-11-15',
                'trans_remarks' => 'Purchase order delivery - urgent egg restock',
                'po_id' => 4,
                'trans_by' => 4, // Maria Garcia (Inventory Manager)
                'item_id' => 7, // Fresh Eggs
                'created_at' => '2024-11-15 16:00:00',
                'updated_at' => '2024-11-15 16:00:00'
            ],
            [
                'trans_ref' => 'TRN-2024-006',
                'trans_type' => 'in',
                'trans_quantity' => 50,
                'trans_date' => '2024-11-15',
                'trans_remarks' => 'Purchase order delivery - urgent milk restock',
                'po_id' => 4,
                'trans_by' => 4, // Maria Garcia (Inventory Manager)
                'item_id' => 4, // Fresh Milk
                'created_at' => '2024-11-15 16:00:00',
                'updated_at' => '2024-11-15 16:00:00'
            ],

            // Stock Out transactions
            [
                'trans_ref' => 'TRN-2024-007',
                'trans_type' => 'out',
                'trans_quantity' => 25,
                'trans_date' => '2024-11-15',
                'trans_remarks' => 'Daily production usage - flour',
                'po_id' => null,
                'trans_by' => 5, // Alice Brown (Senior Baker)
                'item_id' => 1, // All-Purpose Flour
                'created_at' => '2024-11-15 08:00:00',
                'updated_at' => '2024-11-15 08:00:00'
            ],
           [
                'trans_ref' => 'TRN-2024-008',
                'trans_type' => 'out',
                'trans_quantity' => 200,
                'trans_date' => '2024-11-15',
                'trans_remarks' => 'Daily production usage - eggs',
                'po_id' => null,
                'trans_by' => 5, // Alice Brown (Senior Baker)
                'item_id' => 7, // Fresh Eggs
                'created_at' => '2024-11-15 08:00:00',
                'updated_at' => '2024-11-15 08:00:00' // <-- This was the line with the typo
            ],
            [
                'trans_ref' => 'TRN-2024-009',
                'trans_type' => 'out',
                'trans_quantity' => 15,
                'trans_date' => '2024-11-14',
                'trans_remarks' => 'Daily production usage - milk',
                'po_id' => null,
                'trans_by' => 6, // Bob Wilson (Pastry Chef)
                'item_id' => 4, // Fresh Milk
                'created_at' => '2024-11-14 08:00:00',
                'updated_at' => '2024-11-14 08:00:00'
            ],

            // Stock adjustment transactions
            [
                'trans_ref' => 'TRN-2024-010',
                'trans_type' => 'adjustment',
                'trans_quantity' => -2,
                'trans_date' => '2024-11-15',
                'trans_remarks' => 'Damaged goods - expired cream cheese removed from inventory',
                'po_id' => null,
                'trans_by' => 4, // Maria Garcia (Inventory Manager)
                'item_id' => 6, // Cream Cheese
                'created_at' => '2024-11-15 12:00:00',
                'updated_at' => '2024-11-15 12:00:00'
            ],
            [
                'trans_ref' => 'TRN-2024-011',
                'trans_type' => 'adjustment',
                'trans_quantity' => 5,
                'trans_date' => '2024-11-15',
                'trans_remarks' => 'Inventory count correction - found extra stock during cycle count',
                'po_id' => null,
                'trans_by' => 4, // Maria Garcia (Inventory Manager)
                'item_id' => 11, // Vegetable Oil
                'created_at' => '2024-11-15 14:30:00',
                'updated_at' => '2024-11-15 14:30:00'
            ]
        ];

        foreach ($transactions as $transaction) {
            DB::table('inventory_transactions')->insert($transaction);
        }
    }
}