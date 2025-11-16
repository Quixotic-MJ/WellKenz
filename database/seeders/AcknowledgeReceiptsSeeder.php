<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AcknowledgeReceiptsSeeder extends Seeder
{
    public function run()
    {
        $receipts = [
            [
                'ar_ref' => 'AR-2024-001',
                'ar_remarks' => 'Received in good condition - checked for damages and quantities match purchase order',
                'ar_status' => 'received',
                'issued_date' => '2024-11-15',
                'req_id' => 1, // REQ-2024-001
                'issued_by' => 4, // Maria Garcia (Inventory Manager)
                'issued_to' => 5, // Alice Brown (Employee)
                'created_at' => '2024-11-15 11:00:00',
                'updated_at' => '2024-11-15 11:30:00'
            ],
            [
                'ar_ref' => 'AR-2024-002',
                'ar_remarks' => 'Special order items received - quantities verified and quality checked',
                'ar_status' => 'received',
                'issued_date' => '2024-11-13',
                'req_id' => 3, // REQ-2024-003
                'issued_by' => 4, // Maria Garcia (Inventory Manager)
                'issued_to' => 7, // Carol Davis (Employee)
                'created_at' => '2024-11-13 15:00:00',
                'updated_at' => '2024-11-13 16:30:00'
            ],
            [
                'ar_ref' => 'AR-2024-003',
                'ar_remarks' => 'Emergency delivery processed immediately for next day operations',
                'ar_status' => 'received',
                'issued_date' => '2024-11-15',
                'req_id' => 6, // REQ-2024-006
                'issued_by' => 4, // Maria Garcia (Inventory Manager)
                'issued_to' => 6, // Bob Wilson (Employee)
                'created_at' => '2024-11-15 16:30:00',
                'updated_at' => '2024-11-15 17:00:00'
            ],
            [
                'ar_ref' => 'AR-2024-004',
                'ar_remarks' => 'Draft requisition - acknowledgment pending approval and delivery',
                'ar_status' => 'issued',
                'issued_date' => '2024-11-15',
                'req_id' => 2, // REQ-2024-002
                'issued_by' => 4, // Maria Garcia (Inventory Manager)
                'issued_to' => 6, // Bob Wilson (Employee)
                'created_at' => '2024-11-15 10:45:00',
                'updated_at' => '2024-11-15 10:45:00'
            ],
            [
                'ar_ref' => 'AR-2024-005',
                'ar_remarks' => 'Holiday stock order in progress - acknowledge upon delivery',
                'ar_status' => 'issued',
                'issued_date' => '2024-11-15',
                'req_id' => 5, // REQ-2024-005
                'issued_by' => 4, // Maria Garcia (Inventory Manager)
                'issued_to' => 9, // Emma Taylor (Employee)
                'created_at' => '2024-11-15 14:15:00',
                'updated_at' => '2024-11-15 14:15:00'
            ]
        ];

        foreach ($receipts as $receipt) {
            DB::table('acknowledge_receipts')->insert($receipt);
        }
    }
}