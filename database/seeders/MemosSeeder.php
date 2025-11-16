<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MemosSeeder extends Seeder
{
    public function run()
    {
        $memos = [
            [
                'memo_ref' => 'MEMO-2024-001',
                'memo_remarks' => 'Delivery receipt for emergency flour and eggs order - quantities verified and quality checked',
                'received_date' => '2024-11-15',
                'received_by' => 4, // Maria Garcia (Inventory Manager)
                'po_ref' => 'PO-2024-001',
                'created_at' => '2024-11-15 10:30:00',
                'updated_at' => '2024-11-15 10:30:00'
            ],
            [
                'memo_ref' => 'MEMO-2024-002',
                'memo_remarks' => 'Special order delivery confirmation - all decorator items received in excellent condition',
                'received_date' => '2024-11-13',
                'received_by' => 4, // Maria Garcia (Inventory Manager)
                'po_ref' => 'PO-2024-002',
                'created_at' => '2024-11-13 14:00:00',
                'updated_at' => '2024-11-13 14:00:00'
            ],
            [
                'memo_ref' => 'MEMO-2024-003',
                'memo_remarks' => 'Urgent delivery confirmation - eggs and milk received for next day operations',
                'received_date' => '2024-11-15',
                'received_by' => 4, // Maria Garcia (Inventory Manager)
                'po_ref' => 'PO-2024-004',
                'created_at' => '2024-11-15 16:00:00',
                'updated_at' => '2024-11-15 16:00:00'
            ],
            [
                'memo_ref' => 'MEMO-2024-004',
                'memo_remarks' => 'Draft purchase order - memo to be created upon delivery confirmation',
                'received_date' => '2024-11-15',
                'received_by' => 3, // Robert Johnson (Purchasing Manager) - no change needed
                'po_ref' => 'PO-2024-003',
                'created_at' => '2024-11-15 10:30:00',
                'updated_at' => '2024-11-15 10:30:00'
            ],
            [
                'memo_ref' => 'MEMO-2024-005',
                'memo_remarks' => 'Holiday stock delivery pending - expected within 5 business days',
                'received_date' => '2024-11-15',
                'received_by' => 4, // Maria Garcia (Inventory Manager)
                'po_ref' => 'PO-2024-005',
                'created_at' => '2024-11-15 14:00:00',
                'updated_at' => '2024-11-15 14:00:00'
            ],
            [
                'memo_ref' => 'MEMO-2024-006',
                'memo_remarks' => 'Cancelled order memo - no delivery received due to sufficient existing supplies',
                'received_date' => '2024-11-13',
                'received_by' => 4, // Maria Garcia (Inventory Manager)
                'po_ref' => 'PO-2024-006',
                'created_at' => '2024-11-13 08:30:00',
                'updated_at' => '2024-11-13 08:30:00'
            ]
        ];

        foreach ($memos as $memo) {
            DB::table('memos')->insert($memo);
        }
    }
}