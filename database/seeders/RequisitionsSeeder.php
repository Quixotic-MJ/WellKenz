<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RequisitionsSeeder extends Seeder
{
    public function run()
    {
        $requisitions = [
            [
                'req_id' => 1,
                'req_ref' => 'REQ-2024-001',
                'req_purpose' => 'Emergency supply run for weekend orders - ran out of essential ingredients',
                'req_priority' => 'high',
                'req_status' => 'approved',
                'req_date' => '2024-11-15',
                'approved_date' => '2024-11-15',
                'req_reject_reason' => null,
                'requested_by' => 5, // Alice Brown
                'approved_by' => 2, // Jane Smith
                'created_at' => '2024-11-15 08:30:00',
                'updated_at' => '2024-11-15 09:45:00'
            ],
            [
                'req_id' => 2,
                'req_ref' => 'REQ-2024-002',
                'req_purpose' => 'Regular weekly procurement for basic baking supplies',
                'req_priority' => 'medium',
                'req_status' => 'pending',
                'req_date' => '2024-11-15',
                'approved_date' => null,
                'req_reject_reason' => null,
                'requested_by' => 6, // Bob Wilson
                'approved_by' => null,
                'created_at' => '2024-11-15 10:15:00',
                'updated_at' => '2024-11-15 10:15:00'
            ],
            [
                'req_id' => 3,
                'req_ref' => 'REQ-2024-003',
                'req_purpose' => 'Special order for birthday cake decorations - custom request',
                'req_priority' => 'medium',
                'req_status' => 'completed',
                'req_date' => '2024-11-10',
                'approved_date' => '2024-11-10',
                'req_reject_reason' => null,
                'requested_by' => 7, // Carol Davis
                'approved_by' => 2, // Jane Smith
                'created_at' => '2024-11-10 14:20:00',
                'updated_at' => '2024-11-13 16:30:00'
            ],
            [
                'req_id' => 4,
                'req_ref' => 'REQ-2024-004',
                'req_purpose' => 'Replenishment of cleaning supplies for new health compliance',
                'req_priority' => 'low',
                'req_status' => 'rejected',
                'req_date' => '2024-11-12',
                'approved_date' => null,
                'req_reject_reason' => 'Already have sufficient supplies in stock',
                'requested_by' => 8, // David Lee
                'approved_by' => 2, // Jane Smith
                'created_at' => '2024-11-12 11:45:00',
                'updated_at' => '2024-11-13 08:30:00'
            ],
            [
                'req_id' => 5,
                'req_ref' => 'REQ-2024-005',
                'req_purpose' => 'Restocking flour and baking supplies for upcoming holiday season',
                'req_priority' => 'high',
                'req_status' => 'pending',
                'req_date' => '2024-11-15',
                'approved_date' => null,
                'req_reject_reason' => null,
                'requested_by' => 9, // Emma Taylor
                'approved_by' => null,
                'created_at' => '2024-11-15 13:30:00',
                'updated_at' => '2024-11-15 13:30:00'
            ],
            [
                'req_id' => 6,
                'req_ref' => 'REQ-2024-006',
                'req_purpose' => 'Urgent: Running low on eggs and milk for tomorrow\'s orders',
                'req_priority' => 'high',
                'req_status' => 'approved',
                'req_date' => '2024-11-14',
                'approved_date' => '2024-11-14',
                'req_reject_reason' => null,
                'requested_by' => 5, // Alice Brown
                'approved_by' => 2, // Jane Smith
                'created_at' => '2024-11-14 17:15:00',
                'updated_at' => '2024-11-14 18:00:00'
            ],
            // ----- ADDED THIS NEW REQUISITION -----
            [
                'req_id' => 7,
                'req_ref' => 'REQ-2024-007',
                'req_purpose' => 'NEW TEST: Approved requisition waiting for a PO',
                'req_priority' => 'medium',
                'req_status' => 'approved', // <-- It's 'approved'
                'req_date' => '2025-11-17',
                'approved_date' => '2025-11-17',
                'req_reject_reason' => null,
                'requested_by' => 6, // Bob Wilson
                'approved_by' => 2, // Jane Smith
                'created_at' => '2025-11-17 10:00:00',
                'updated_at' => '2025-11-17 10:00:00'
            ]
            // This req_id (7) is NOT in your PurchaseOrdersSeeder,
            // so it will appear on the page.
        ];

        foreach ($requisitions as $requisition) {
            DB::table('requisitions')->insert($requisition);
        }
    }
}