<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PurchaseOrdersSeeder extends Seeder
{
    public function run()
    {
        $purchaseOrders = [
            [
                'po_ref' => 'PO-2024-001',
                'po_status' => 'ordered',
                'order_date' => '2024-11-15',
                'delivery_address' => 'WellKenz Bakery, 123 Business District, Makati City, Metro Manila',
                'expected_delivery_date' => '2024-11-18',
                'total_amount' => 2850.00,
                'notes' => 'Emergency flour and eggs order for weekend operations',
                'sup_id' => 1, // Flour & More Trading
                'req_id' => 1, // REQ-2024-001
                'created_at' => '2024-11-15 10:00:00',
                'updated_at' => '2024-11-15 10:00:00'
            ],
            [
                'po_ref' => 'PO-2024-002',
                'po_status' => 'delivered',
                'order_date' => '2024-11-10',
                'delivery_address' => 'WellKenz Bakery, 123 Business District, Makati City, Metro Manila',
                'expected_delivery_date' => '2024-11-13',
                'total_amount' => 1200.00,
                'notes' => 'Special order items for custom cake decorations',
                'sup_id' => 9, // DecoCraft Supplies
                'req_id' => 3, // REQ-2024-003
                'created_at' => '2024-11-10 15:45:00',
                'updated_at' => '2024-11-13 16:30:00'
            ],
            [
                'po_ref' => 'PO-2024-003',
                'po_status' => 'draft',
                'order_date' => '2024-11-15',
                'delivery_address' => 'WellKenz Bakery, 123 Business District, Makati City, Metro Manila',
                'expected_delivery_date' => '2024-11-22',
                'total_amount' => 1575.05,
                'notes' => 'Regular weekly procurement - pending supervisor approval',
                'sup_id' => 2, // Dairy Delights Corporation
                'req_id' => 2, // REQ-2024-002
                'created_at' => '2024-11-15 10:30:00',
                'updated_at' => '2024-11-15 10:30:00'
            ],
            [
                'po_ref' => 'PO-2024-004',
                'po_status' => 'ordered',
                'order_date' => '2024-11-14',
                'delivery_address' => 'WellKenz Bakery, 123 Business District, Makati City, Metro Manila',
                'expected_delivery_date' => '2024-11-16',
                'total_amount' => 3200.00,
                'notes' => 'Urgent egg and milk order - delivery needed for tomorrow',
                'sup_id' => 6, // Fresh Produce Market
                'req_id' => 6, // REQ-2024-006
                'created_at' => '2024-11-14 18:15:00',
                'updated_at' => '2024-11-14 18:15:00'
            ],
            [
                'po_ref' => 'PO-2024-005',
                'po_status' => 'ordered',
                'order_date' => '2024-11-15',
                'delivery_address' => 'WellKenz Bakery, 123 Business District, Makati City, Metro Manila',
                'expected_delivery_date' => '2024-11-20',
                'total_amount' => 11400.00,
                'notes' => 'Holiday season stock-up - flour, eggs, and milk bulk order',
                'sup_id' => 1, // Flour & More Trading
                'req_id' => 5, // REQ-2024-005
                'created_at' => '2024-11-15 14:00:00',
                'updated_at' => '2024-11-15 14:00:00'
            ],
            [
                'po_ref' => 'PO-2024-006',
                'po_status' => 'cancelled',
                'order_date' => '2024-11-12',
                'delivery_address' => 'WellKenz Bakery, 123 Business District, Makati City, Metro Manila',
                'expected_delivery_date' => '2024-11-15',
                'total_amount' => 500.00, // 10×25 + 5×50 = 250 + 250 = 500
                'notes' => 'Order cancelled due to sufficient existing supplies',
                'sup_id' => 8, // CleanSupply Inc
                'req_id' => 4, // REQ-2024-004 (rejected)
                'created_at' => '2024-11-12 12:00:00',
                'updated_at' => '2024-11-13 08:30:00'
            ]
        ];

        foreach ($purchaseOrders as $po) {
            DB::table('purchase_orders')->insert($po);
        }
    }
}