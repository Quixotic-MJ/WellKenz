<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NotificationSeeder extends Seeder
{
    public function run()
    {
        // Clear existing notifications
        DB::table('notifications')->truncate();

        // Sample notifications for testing
        $notifications = [
            // Stock alerts for admin user (id=1)
            [
                'user_id' => 1,
                'title' => 'Critical Stock Level: All-Purpose Flour',
                'message' => 'Current stock (5kg) is below the critical safety threshold. Immediate restocking required for scheduled production.',
                'type' => 'stock_alert',
                'priority' => 'urgent',
                'is_read' => false,
                'action_url' => '/admin/items?stock_status=out',
                'metadata' => [
                    'item_name' => 'All-Purpose Flour',
                    'current_stock' => '5kg',
                    'reorder_point' => '25kg',
                    'supplier' => 'Manila Flour Mills'
                ],
                'expires_at' => Carbon::now()->addDays(7),
                'created_at' => Carbon::now()->subMinutes(30),
            ],
            [
                'user_id' => 1,
                'title' => 'Low Stock Alert: Butter',
                'message' => 'Butter stock is running low. Current stock: 15kg, Reorder point: 20kg.',
                'type' => 'inventory',
                'priority' => 'high',
                'is_read' => false,
                'action_url' => '/admin/items?stock_status=low',
                'metadata' => [
                    'item_name' => 'Unsalted Butter',
                    'current_stock' => '15kg',
                    'reorder_point' => '20kg'
                ],
                'expires_at' => Carbon::now()->addDays(3),
                'created_at' => Carbon::now()->subHours(2),
            ],

            // Approval requests for admin
            [
                'user_id' => 1,
                'title' => 'New Requisition: #REQ-0011',
                'message' => 'Baker Juan has requested 50kg Bread Flour. This exceeds the daily average limit.',
                'type' => 'approval_req',
                'priority' => 'high',
                'is_read' => false,
                'action_url' => '/admin/requisitions/approval',
                'metadata' => [
                    'requester' => 'Baker Juan',
                    'requested_item' => 'Bread Flour',
                    'quantity' => '50kg',
                    'department' => 'Production'
                ],
                'expires_at' => Carbon::now()->addDays(1),
                'created_at' => Carbon::now()->subHours(4),
            ],

            // System information
            [
                'user_id' => 1,
                'title' => 'Automated Backup Successful',
                'message' => 'The daily system backup was completed successfully at 03:00 AM. Size: 45MB.',
                'type' => 'system_info',
                'priority' => 'normal',
                'is_read' => true,
                'action_url' => null,
                'metadata' => [
                    'backup_size' => '45MB',
                    'backup_time' => '03:00 AM',
                    'status' => 'success'
                ],
                'created_at' => Carbon::now()->subDays(1),
            ],

            // Delivery updates
            [
                'user_id' => 1,
                'title' => 'Order Delivered: PO-2023-099',
                'message' => 'Items from Golden Grain Supplies have been received and stocked.',
                'type' => 'delivery_update',
                'priority' => 'normal',
                'is_read' => true,
                'action_url' => '/admin/purchase-orders/99',
                'metadata' => [
                    'po_number' => 'PO-2023-099',
                    'supplier' => 'Golden Grain Supplies',
                    'delivery_status' => 'completed'
                ],
                'created_at' => Carbon::now()->subDays(2),
            ],

            // Production notifications
            [
                'user_id' => 1,
                'title' => 'Production Complete: French Bread',
                'message' => 'Production order #PROD-023 has been completed. Produced: 100 pieces.',
                'type' => 'production',
                'priority' => 'normal',
                'is_read' => false,
                'action_url' => '/admin/production/23',
                'metadata' => [
                    'production_order' => '#PROD-023',
                    'product' => 'French Bread',
                    'quantity_produced' => '100 pieces',
                    'completion_time' => Carbon::now()->subHours(6)->format('H:i')
                ],
                'created_at' => Carbon::now()->subHours(6),
            ],

            // Purchasing notifications
            [
                'user_id' => 1,
                'title' => 'Purchase Order Approved',
                'message' => 'PO-2023-115 has been approved and sent to supplier.',
                'type' => 'purchasing',
                'priority' => 'normal',
                'is_read' => false,
                'action_url' => '/admin/purchase-orders/115',
                'metadata' => [
                    'po_number' => 'PO-2023-115',
                    'supplier' => 'Pure Oils Philippines',
                    'total_amount' => '₱15,750.00',
                    'approval_date' => Carbon::now()->subHours(8)->format('Y-m-d H:i')
                ],
                'created_at' => Carbon::now()->subHours(8),
            ],

            // Quality control notifications
            [
                'user_id' => 1,
                'title' => 'Quality Check Completed',
                'message' => 'Batch QC-2024-001 passed all quality tests. Approved for production use.',
                'type' => 'quality',
                'priority' => 'normal',
                'is_read' => true,
                'action_url' => '/admin/quality/qc-001',
                'metadata' => [
                    'batch_number' => 'QC-2024-001',
                    'test_results' => 'All parameters within specification',
                    'approved_by' => 'Quality Control Team'
                ],
                'created_at' => Carbon::now()->subDays(3),
            ],

            // More notifications for pagination testing
            [
                'user_id' => 1,
                'title' => 'User Account Created',
                'message' => 'New employee account created for Maria Santos in the Production department.',
                'type' => 'system_info',
                'priority' => 'low',
                'is_read' => true,
                'action_url' => '/admin/users',
                'metadata' => [
                    'employee_name' => 'Maria Santos',
                    'department' => 'Production',
                    'role' => 'employee'
                ],
                'created_at' => Carbon::now()->subDays(5),
            ],
            [
                'user_id' => 1,
                'title' => 'Inventory Adjustment Made',
                'message' => 'Stock adjustment of -2kg for All-Purpose Flour due to measurement discrepancy.',
                'type' => 'inventory',
                'priority' => 'normal',
                'is_read' => true,
                'action_url' => '/admin/stock-adjustments',
                'metadata' => [
                    'item_name' => 'All-Purpose Flour',
                    'adjustment_type' => 'reduction',
                    'quantity' => '-2kg',
                    'reason' => 'Measurement discrepancy'
                ],
                'created_at' => Carbon::now()->subDays(7),
            ],
            [
                'user_id' => 1,
                'title' => 'Near Expiry Warning',
                'message' => '5 batches will expire within the next 7 days. Review recommended.',
                'type' => 'expiry_warning',
                'priority' => 'high',
                'is_read' => false,
                'action_url' => '/admin/expiry-report',
                'metadata' => [
                    'batches_affected' => 5,
                    'days_until_expiry' => 7,
                    'items' => 'Dairy products, Fresh ingredients'
                ],
                'expires_at' => Carbon::now()->addDays(7),
                'created_at' => Carbon::now()->subHours(12),
            ],
        ];

        // Insert notifications
        foreach ($notifications as $notification) {
            // Convert metadata array to JSON string for PostgreSQL
            if (isset($notification['metadata'])) {
                $notification['metadata'] = json_encode($notification['metadata']);
            }
            DB::table('notifications')->insert($notification);
        }

        // Add a few notifications for other users (supervisor, inventory, etc.)
        $otherUserNotifications = [
            [
                'user_id' => 2, // Supervisor
                'title' => 'Approval Required: Purchase Request',
                'message' => 'PR-2024-001 requires your approval for supplier selection.',
                'type' => 'approval_req',
                'priority' => 'high',
                'is_read' => false,
                'action_url' => '/supervisor/approvals/pr-001',
                'metadata' => json_encode([
                    'pr_number' => 'PR-2024-001',
                    'requester' => 'Purchasing Department',
                    'total_amount' => '₱25,000.00'
                ]),
                'created_at' => Carbon::now()->subMinutes(15),
            ],
            [
                'user_id' => 4, // Inventory
                'title' => 'Incoming Delivery',
                'message' => 'Expected delivery from Manila Flour Mills at 2:00 PM today.',
                'type' => 'delivery_update',
                'priority' => 'normal',
                'is_read' => false,
                'action_url' => '/inventory/inbound/receive',
                'metadata' => json_encode([
                    'supplier' => 'Manila Flour Mills',
                    'expected_time' => '2:00 PM',
                    'items' => 'Flour, Sugar'
                ]),
                'created_at' => Carbon::now()->subHours(1),
            ],
        ];

        foreach ($otherUserNotifications as $notification) {
            DB::table('notifications')->insert($notification);
        }

        $this->command->info('Sample notifications created successfully!');
        $this->command->info('Total notifications created: ' . (count($notifications) + count($otherUserNotifications)));
    }
}