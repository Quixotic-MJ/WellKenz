<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;

class PurchasingNotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get purchasing users
        $purchasingUsers = User::where('role', 'purchasing')->get();
        
        if ($purchasingUsers->isEmpty()) {
            $this->command->info('No purchasing users found. Skipping notification seeding.');
            return;
        }

        $notifications = [
            // Low stock alerts
            [
                'title' => 'Low Stock Alert',
                'message' => 'All-Purpose Flour is below minimum stock level (15kg remaining). Reorder point: 25kg.',
                'type' => 'stock_alert',
                'priority' => 'high',
                'metadata' => [
                    'item_name' => 'All-Purpose Flour',
                    'current_stock' => '15.000',
                    'reorder_point' => '25.000',
                    'supplier_recommended' => 'Manila Flour Mills'
                ]
            ],
            [
                'title' => 'Critical Stock Level',
                'message' => 'Fresh Yeast is critically low (2kg remaining). Production may be affected.',
                'type' => 'stock_alert',
                'priority' => 'urgent',
                'metadata' => [
                    'item_name' => 'Fresh Yeast',
                    'current_stock' => '2.000',
                    'reorder_point' => '10.000',
                    'supplier_recommended' => 'Golden Grains Inc'
                ]
            ],
            [
                'title' => 'Low Stock Alert',
                'message' => 'Cocoa Powder is below minimum stock level (8kg remaining). Reorder point: 15kg.',
                'type' => 'stock_alert',
                'priority' => 'high',
                'metadata' => [
                    'item_name' => 'Cocoa Powder',
                    'current_stock' => '8.000',
                    'reorder_point' => '15.000',
                    'supplier_recommended' => 'Spice Masters'
                ]
            ],
            // Purchase Order updates
            [
                'title' => 'Purchase Order Confirmed',
                'message' => 'PO-2024-000123 from Manila Flour Mills has been confirmed for delivery on Jan 25, 2024.',
                'type' => 'delivery_update',
                'priority' => 'normal',
                'metadata' => [
                    'po_number' => 'PO-2024-000123',
                    'supplier' => 'Manila Flour Mills',
                    'expected_delivery' => '2024-01-25',
                    'total_amount' => '8750.00'
                ]
            ],
            [
                'title' => 'Purchase Order Delivered',
                'message' => 'PO-2024-000115 from Pure Oils Philippines has been delivered and received.',
                'type' => 'delivery_update',
                'priority' => 'normal',
                'metadata' => [
                    'po_number' => 'PO-2024-000115',
                    'supplier' => 'Pure Oils Philippines',
                    'delivery_date' => '2024-01-24',
                    'total_amount' => '12450.00'
                ]
            ],
            // Purchase request notifications
            [
                'title' => 'New Purchase Request',
                'message' => 'PR-2024-000156: Baker John requested ingredients for special cake orders.',
                'type' => 'approval_req',
                'priority' => 'normal',
                'metadata' => [
                    'pr_number' => 'PR-2024-000156',
                    'requested_by' => 'Baker John',
                    'department' => 'Production',
                    'total_estimated' => '3250.00'
                ]
            ],
            [
                'title' => 'Purchase Request Approved',
                'message' => 'PR-2024-000154 for baking supplies has been approved by supervisor.',
                'type' => 'approval_req',
                'priority' => 'normal',
                'metadata' => [
                    'pr_number' => 'PR-2024-000154',
                    'approved_by' => 'Production Supervisor',
                    'department' => 'Production',
                    'total_estimated' => '5670.00'
                ]
            ],
            // Supplier notifications
            [
                'title' => 'Supplier Price Update',
                'message' => 'Fresh Dairy Corp has updated prices for dairy products. Review recommended.',
                'type' => 'purchasing',
                'priority' => 'normal',
                'metadata' => [
                    'supplier' => 'Fresh Dairy Corp',
                    'items_affected' => '8',
                    'average_increase' => '5.2%'
                ]
            ],
            [
                'title' => 'Supplier Performance Alert',
                'message' => 'Sweet Sugar Co has consistently late deliveries (avg 3.2 days). Consider alternative suppliers.',
                'type' => 'purchasing',
                'priority' => 'high',
                'metadata' => [
                    'supplier' => 'Sweet Sugar Co',
                    'late_deliveries' => '5',
                    'average_delay' => '3.2 days',
                    'period' => 'Last 30 days'
                ]
            ],
            // System notifications
            [
                'title' => 'Monthly Purchasing Report Ready',
                'message' => 'Your monthly purchasing summary and supplier performance report is ready for review.',
                'type' => 'system_info',
                'priority' => 'low',
                'metadata' => [
                    'report_type' => 'Monthly Summary',
                    'period' => 'December 2024',
                    'report_url' => '/purchasing/reports/history'
                ]
            ]
        ];

        // Create notifications for each purchasing user
        foreach ($purchasingUsers as $user) {
            foreach ($notifications as $index => $notificationData) {
                // Stagger the created_at dates
                $createdAt = Carbon::now()->subDays(rand(1, 30))->subHours(rand(1, 23));
                
                $notification = new Notification([
                    'user_id' => $user->id,
                    'title' => $notificationData['title'],
                    'message' => $notificationData['message'],
                    'type' => $notificationData['type'],
                    'priority' => $notificationData['priority'],
                    'metadata' => $notificationData['metadata'],
                    'is_read' => rand(0, 1) === 1, // Randomly mark as read/unread
                    'created_at' => $createdAt,
                    'expires_at' => $createdAt->copy()->addDays(30) // Expires in 30 days
                ]);
                
                $notification->save();
            }
        }

        $this->command->info('Created ' . (count($notifications) * $purchasingUsers->count()) . ' purchasing notifications for ' . $purchasingUsers->count() . ' users.');
    }
}