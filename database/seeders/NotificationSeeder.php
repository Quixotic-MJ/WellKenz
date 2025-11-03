<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationSeeder extends Seeder
{
    public function run()
    {
        $notifications = [
            // Admin notifications
            [
                'notif_title' => 'New Requisition Submitted',
                'notif_content' => 'Employee Jane Doe submitted a new requisition request.',
                'related_id' => 1,
                'related_type' => 'requisition',
                'is_read' => false,
                'user_id' => 1, // admin
            ],
            [
                'notif_title' => 'Low Stock Alert',
                'notif_content' => 'Chocolate chips inventory is below reorder level.',
                'related_id' => 1,
                'related_type' => 'inventory',
                'is_read' => false,
                'user_id' => 1,
            ],
            [
                'notif_title' => 'Purchase Order Approved',
                'notif_content' => 'Purchase order #PO-001 has been approved.',
                'related_id' => 1,
                'related_type' => 'purchase_order',
                'is_read' => true,
                'user_id' => 1,
            ],

            // Employee notifications
            [
                'notif_title' => 'Requisition Approved',
                'notif_content' => 'Your requisition #REQ-001 has been approved.',
                'related_id' => 1,
                'related_type' => 'requisition',
                'is_read' => false,
                'user_id' => 2, // baker1
            ],
            [
                'notif_title' => 'Item Request Pending',
                'notif_content' => 'Your custom item request is pending approval.',
                'related_id' => 1,
                'related_type' => 'item_request',
                'is_read' => false,
                'user_id' => 2,
            ],

            // Inventory notifications
            [
                'notif_title' => 'Stock Level Critical',
                'notif_content' => 'Flour inventory is critically low.',
                'related_id' => 2,
                'related_type' => 'inventory',
                'is_read' => false,
                'user_id' => 4, // inventory
            ],

            // Purchasing notifications
            [
                'notif_title' => 'New Approved Requisition',
                'notif_content' => 'A new requisition requires purchase order creation.',
                'related_id' => 1,
                'related_type' => 'requisition',
                'is_read' => false,
                'user_id' => 5, // purchasing
            ],

            // Supervisor notifications
            [
                'notif_title' => 'Pending Approval',
                'notif_content' => '3 requisitions are waiting for your approval.',
                'related_id' => null,
                'related_type' => 'requisition',
                'is_read' => false,
                'user_id' => 6, // supervisor
            ],
        ];

        foreach ($notifications as $notification) {
            DB::table('notifications')->insert([
                'notif_title' => $notification['notif_title'],
                'notif_content' => $notification['notif_content'],
                'related_id' => $notification['related_id'],
                'related_type' => $notification['related_type'],
                'is_read' => $notification['is_read'],
                'user_id' => $notification['user_id'],
                'created_at' => now()->subHours(rand(1, 24)),
                'updated_at' => now(),
            ]);
        }
    }
}