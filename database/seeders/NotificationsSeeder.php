<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationsSeeder extends Seeder
{
    public function run()
    {
        $notifications = [
            [
                'notif_title' => 'Low Stock Alert',
                'notif_content' => 'All-Purpose Flour has reached reorder level. Current stock: 50kg, Reorder level: 100kg',
                'related_id' => '1',
                'related_type' => 'item',
                'is_read' => false,
                'user_id' => 4, // Maria Garcia (Inventory Manager)
                'created_at' => '2024-11-15 09:00:00',
                'updated_at' => '2024-11-15 09:00:00'
            ],
            [
                'notif_title' => 'Critical Stock Warning',
                'notif_content' => 'Testing Item Low Stock has reached critical stock level. Current stock: 5 pieces, Min stock level: 15 pieces',
                'related_id' => '16',
                'related_type' => 'item',
                'is_read' => false,
                'user_id' => 4, // Maria Garcia (Inventory Manager)
                'created_at' => '2024-11-15 09:15:00',
                'updated_at' => '2024-11-15 09:15:00'
            ],
            [
                'notif_title' => 'Item Request Approved',
                'notif_content' => 'Your item request for "Cake Turntable (12-inch)" has been approved by Jane Smith',
                'related_id' => '2',
                'related_type' => 'item_request',
                'is_read' => false,
                'user_id' => 6, // Bob Wilson
                'created_at' => '2024-11-14 16:45:00',
                'updated_at' => '2024-11-14 16:45:00'
            ],
            [
                'notif_title' => 'Requisition Status Update',
                'notif_content' => 'Requisition REQ-2024-001 has been approved and sent to purchasing',
                'related_id' => '1',
                'related_type' => 'requisition',
                'is_read' => false,
                'user_id' => 5, // Alice Brown
                'created_at' => '2024-11-15 09:45:00',
                'updated_at' => '2024-11-15 09:45:00'
            ],
            [
                'notif_title' => 'Purchase Order Delivered',
                'notif_content' => 'Purchase Order PO-2024-001 has been delivered and inventory updated',
                'related_id' => '1',
                'related_type' => 'purchase_order',
                'is_read' => true,
                'user_id' => 4, // Maria Garcia (Inventory Manager)
                'created_at' => '2024-11-15 10:30:00',
                'updated_at' => '2024-11-15 10:30:00'
            ],
            [
                'notif_title' => 'Expiry Alert',
                'notif_content' => 'Heavy Cream expires in 3 days (2024-11-18). Please prioritize usage or check expiration management',
                'related_id' => '5',
                'related_type' => 'item',
                'is_read' => false,
                'user_id' => 4, // Maria Garcia (Inventory Manager)
                'created_at' => '2024-11-15 08:30:00',
                'updated_at' => '2024-11-15 08:30:00'
            ],
            [
                'notif_title' => 'New Item Request',
                'notif_content' => 'Alice Brown has submitted a new item request for "Extra Large Piping Tips Set"',
                'related_id' => '1',
                'related_type' => 'item_request',
                'is_read' => false,
                'user_id' => 2, // Jane Smith (Supervisor)
                'created_at' => '2024-11-15 09:30:00',
                'updated_at' => '2024-11-15 09:30:00'
            ],
            [
                'notif_title' => 'Supervisor Approval Required',
                'notif_content' => 'Purchase Order PO-2024-003 (total: ₱1,575.00) requires your approval',
                'related_id' => '3',
                'related_type' => 'purchase_order',
                'is_read' => false,
                'user_id' => 2, // Jane Smith (Supervisor)
                'created_at' => '2024-11-15 10:30:00',
                'updated_at' => '2024-11-15 10:30:00'
            ],
            [
                'notif_title' => 'Inventory Adjustment',
                'notif_content' => 'Stock adjustment made: -2 kg Cream Cheese (damaged goods removal)',
                'related_id' => '10',
                'related_type' => 'inventory_transaction',
                'is_read' => true,
                'user_id' => 4, // Maria Garcia (Inventory Manager)
                'created_at' => '2024-11-15 12:00:00',
                'updated_at' => '2024-11-15 12:00:00'
            ],
            [
                'notif_title' => 'Weekly Report Ready',
                'notif_content' => 'Weekly inventory health report is ready for review and contains stock level analysis',
                'related_id' => 'weekly_report_2024_w46',
                'related_type' => 'report',
                'is_read' => false,
                'user_id' => 1, // John Doe (Admin)
                'created_at' => '2024-11-15 17:00:00',
                'updated_at' => '2024-11-15 17:00:00'
            ]
        ];

        foreach ($notifications as $notification) {
            DB::table('notifications')->insert($notification);
        }
    }
}