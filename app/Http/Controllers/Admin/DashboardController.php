<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Item;
use App\Models\Category;
use App\Models\Requisition;
use App\Models\PurchaseOrder;
use App\Models\Batch;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard overview.
     *
     * @return \Illuminate\Http\Response
     */
    public function systemOverview()
    {
        // Get greeting based on time of day
        $hour = Carbon::now()->hour;
        if ($hour < 12) {
            $greeting = 'Good Morning';
        } elseif ($hour < 17) {
            $greeting = 'Good Afternoon';
        } else {
            $greeting = 'Good Evening';
        }

        // Get current user name
        $userName = auth()->user()->name ?? 'Admin';

        // Get database health info
        $databaseHealth = $this->getDatabaseHealth();

        // Get security alerts count
        $securityAlertsCount = $this->getSecurityAlertsCount();

        $data = [
            // Welcome message data
            'greeting' => $greeting,
            'userName' => $userName,
            'currentTime' => Carbon::now(),
            'currentDate' => Carbon::now()->format('F j, Y'),
            'currentDay' => Carbon::now()->format('l'),

            // User Statistics
            'totalUsers' => User::count(),
            'activeUsers' => User::where('is_active', true)->count(),
            'inactiveUsers' => User::where('is_active', false)->count(),

            // Item Statistics
            'totalItems' => Item::count(),
            'activeItems' => Item::where('is_active', true)->count(),
            'categoryCount' => Category::where('is_active', true)->count(),

            // Database Health
            'databaseHealth' => $databaseHealth,

            // Security Alerts
            'securityAlertsCount' => $securityAlertsCount,

            // Inventory Health
            'itemsWithLowStock' => $this->getItemsWithLowStock(),
            'itemsOutOfStock' => $this->getItemsOutOfStock(),
            'lowStockCount' => 0, // Will be calculated
            'outOfStockCount' => 0, // Will be calculated

            // Requisition Statistics
            'requisitions' => $this->getRequisitionStatistics(),

            // Purchase Order Statistics
            'purchaseOrders' => $this->getPurchaseOrderStatistics(),

            // Low Stock Alerts (Top 5 items with lowest stock)
            'lowStockAlerts' => $this->getLowStockAlerts(),

            // Near Expiry Alerts (Top 5 batches expiring within 7 days)
            'expiringBatches' => $this->getExpiringBatches(),

            // Recent Database Updates (Last 5 stock movements or audit logs)
            'recentUpdates' => $this->getRecentDatabaseUpdates(),

            // Security Log (Last 5 security-related audit logs)
            'securityLogs' => $this->getSecurityLogs(),
        ];

        // Calculate low stock and out of stock counts
        $data['lowStockCount'] = count($data['itemsWithLowStock']);
        $data['outOfStockCount'] = count($data['itemsOutOfStock']);

        return view('Admin.system_overview', $data);
    }

    /**
     * Get database health information.
     *
     * @return array
     */
    private function getDatabaseHealth()
    {
        try {
            // Test database connection
            DB::connection()->getPdo();
            
            // Get last backup info from audit logs
            $lastBackup = AuditLog::where('action', 'backup')
                ->where('table_name', 'database')
                ->orderBy('created_at', 'desc')
                ->first();
                
            return [
                'status' => 'healthy',
                'status_color' => 'green', // green for healthy status
                'message' => 'Database connection stable',
                'last_backup' => $lastBackup ? $lastBackup->created_at->diffForHumans() : 'No backups found'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'status_color' => 'red', // red for error status
                'message' => 'Database connection failed',
                'last_backup' => 'Backup information unavailable'
            ];
        }
    }

    /**
     * Get security alerts count.
     *
     * @return int
     */
    private function getSecurityAlertsCount()
    {
        return AuditLog::where('table_name', 'users')
            ->whereIn('action', ['LOGIN_FAILED', 'PASSWORD_BREACH'])
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->count();
    }

    /**
     * Get items with low stock.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getItemsWithLowStock()
    {
        return Item::where('is_active', true)
            ->with(['currentStockRecord', 'unit'])
            ->get()
            ->filter(function ($item) {
                $currentStock = $item->currentStockRecord ? $item->currentStockRecord->current_quantity : 0;
                // Use reorder_point if set, otherwise use min_stock_level
                $threshold = $item->reorder_point > 0 ? $item->reorder_point : $item->min_stock_level;
                // Include items at or below threshold (including out of stock)
                return $threshold > 0 && $currentStock <= $threshold;
            })
            ->sortBy(function ($item) {
                $currentStock = $item->currentStockRecord ? $item->currentStockRecord->current_quantity : 0;
                return $currentStock;
            })
            ->take(5)
            ->values();
    }

    /**
     * Get items that are out of stock.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getItemsOutOfStock()
    {
        return Item::where('is_active', true)
            ->with(['currentStockRecord', 'unit'])
            ->get()
            ->filter(function ($item) {
                $currentStock = $item->currentStockRecord ? $item->currentStockRecord->current_quantity : 0;
                return $currentStock <= 0;
            })
            ->sortBy(function ($item) {
                $currentStock = $item->currentStockRecord ? $item->currentStockRecord->current_quantity : 0;
                return $currentStock;
            })
            ->take(5)
            ->values();
    }

    /**
     * Get requisition statistics.
     *
     * @return array
     */
    private function getRequisitionStatistics()
    {
        return [
            'pendingApproval' => Requisition::where('status', 'pending')->count(),
            'approvedToday' => Requisition::where('status', 'approved')
                ->whereDate('approved_at', Carbon::today())
                ->count(),
            'rejected' => Requisition::where('status', 'rejected')->count(),
            'totalRequisitions' => Requisition::count(),
        ];
    }

    /**
     * Get purchase order statistics.
     *
     * @return array
     */
    private function getPurchaseOrderStatistics()
    {
        return [
            'draft' => PurchaseOrder::where('status', 'draft')->count(),
            'ordered' => PurchaseOrder::whereIn('status', ['sent', 'confirmed'])->count(),
            'delivered' => PurchaseOrder::where('status', 'completed')->count(),
            'totalOrders' => PurchaseOrder::count(),
            'averageDeliveryTime' => $this->calculateAverageDeliveryTime(),
        ];
    }

    /**
     * Calculate average delivery time for purchase orders.
     *
     * @return float
     */
    private function calculateAverageDeliveryTime()
    {
        $completedOrders = PurchaseOrder::where('status', 'completed')
            ->whereNotNull('actual_delivery_date')
            ->get();

        if ($completedOrders->isEmpty()) {
            return 0;
        }

        $totalDays = 0;
        $count = 0;

        foreach ($completedOrders as $order) {
            if ($order->order_date && $order->actual_delivery_date) {
                $totalDays += $order->order_date->diffInDays($order->actual_delivery_date);
                $count++;
            }
        }

        return $count > 0 ? round($totalDays / $count, 1) : 0;
    }

    /**
     * Get low stock alerts data.
     *
     * @return array
     */
    private function getLowStockAlerts()
    {
        $items = $this->getItemsWithLowStock();
        
        return $items->map(function ($item) {
            $currentStock = $item->currentStockRecord ? $item->currentStockRecord->current_quantity : 0;
            // Use reorder_point if set, otherwise use min_stock_level
            $threshold = $item->reorder_point > 0 ? $item->reorder_point : $item->min_stock_level;
            
            return [
                'id' => $item->id,
                'name' => $item->name,
                'item_code' => $item->item_code,
                'current_stock' => $currentStock,
                'reorder_level' => $threshold,
                'unit' => $item->unit->symbol ?? '',
            ];
        })->toArray();
    }

    /**
     * Get batches that are expiring soon.
     *
     * @return array
     */
    private function getExpiringBatches()
    {
        $sevenDaysFromNow = Carbon::now()->addDays(7);
        
        return Batch::with(['item', 'supplier'])
            ->where('status', 'active')
            ->whereNotNull('expiry_date')
            ->where(function($query) use ($sevenDaysFromNow) {
                // Include batches that are already expired OR will expire within 7 days
                $query->where('expiry_date', '<=', $sevenDaysFromNow);
            })
            ->orderBy('expiry_date')
            ->take(5)
            ->get()
            ->map(function ($batch) {
                $daysUntilExpiry = (int) Carbon::now()->startOfDay()->diffInDays($batch->expiry_date->startOfDay(), false);
                
                return [
                    'id' => $batch->id,
                    'item_name' => $batch->item->name ?? 'Unknown Item',
                    'batch_number' => $batch->batch_number,
                    'expiry_date' => $batch->expiry_date->format('M j, Y'),
                    'days_until_expiry' => $daysUntilExpiry,
                    'expiry_status' => ($daysUntilExpiry < 0) ? 'Expired ' . abs($daysUntilExpiry) . ' days ago' : 
                                     (($daysUntilExpiry == 0) ? 'Expires Today' :
                                     (($daysUntilExpiry == 1) ? 'Expires Tomorrow' : 
                                     "Expires in {$daysUntilExpiry} days")),
                ];
            })->toArray();
    }

    /**
     * Get recent database updates.
     *
     * @return array
     */
    private function getRecentDatabaseUpdates()
    {
        // Get recent audit logs (simulating stock movements for demo)
        return AuditLog::with(['user'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($log) {
                $action = strtolower($log->action);
                $icon = 'fas fa-edit'; // default
                
                if ($action === 'create') {
                    $icon = 'fas fa-plus';
                } elseif ($action === 'delete') {
                    $icon = 'fas fa-trash';
                } elseif ($action === 'update') {
                    $icon = 'fas fa-edit';
                }

                return [
                    'id' => $log->id,
                    'action' => $action,
                    'table_name' => $log->table_name,
                    'record_id' => $log->record_id,
                    'description' => $this->generateLogDescription($log),
                    'user_name' => $log->user->name ?? 'System',
                    'created_at' => $log->created_at,
                    'time_ago' => $log->created_at->diffForHumans(),
                    'icon' => $icon,
                ];
            })->toArray();
    }

    /**
     * Get security-related logs.
     *
     * @return array
     */
    private function getSecurityLogs()
    {
        // For demo purposes, we'll simulate security logs from audit logs
        // In a real system, you might have a dedicated security logs table
        return AuditLog::with(['user'])
            ->where(function ($query) {
                $query->where('table_name', 'users')
                      ->orWhere('action', 'login')
                      ->orWhere('action', 'logout');
            })
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($log) {
                $color = 'bg-green-500'; // default color
                $description = 'System Activity';
                
                if (strtolower($log->action) === 'login') {
                    $color = 'bg-blue-500';
                    $description = 'User Login';
                } elseif (strtolower($log->action) === 'update' && $log->table_name === 'users') {
                    $color = 'bg-amber-500';
                    $description = 'User Role Modified';
                }
                
                return [
                    'id' => $log->id,
                    'description' => $description,
                    'details' => $this->generateSecurityLogDetails($log),
                    'time_ago' => $log->created_at->diffForHumans(),
                    'created_at' => $log->created_at,
                    'color' => $color,
                ];
            })->toArray();
    }

    /**
     * Generate log description for audit log entries.
     *
     * @param \App\Models\AuditLog $log
     * @return string
     */
    private function generateLogDescription($log)
    {
        $action = strtolower($log->action);
        $table = $log->table_name;
        
        switch ($table) {
            case 'items':
                if ($action === 'create') return 'New Item Added';
                if ($action === 'update') return 'Item Updated';
                if ($action === 'delete') return 'Item Removed';
                break;
            case 'users':
                if ($action === 'create') return 'New User Added';
                if ($action === 'update') return 'User Modified';
                if ($action === 'delete') return 'User Removed';
                break;
            case 'purchase_orders':
                if ($action === 'create') return 'Purchase Order Created';
                if ($action === 'update') return 'Purchase Order Updated';
                break;
            case 'suppliers':
                if ($action === 'create') return 'Supplier Added';
                if ($action === 'update') return 'Supplier Updated';
                if ($action === 'delete') return 'Supplier Removed';
                break;
            default:
                return ucfirst($action) . ' on ' . ucwords(str_replace('_', ' ', $table));
        }
        
        return ucfirst($action) . ' on ' . ucwords(str_replace('_', ' ', $table));
    }

    /**
     * Generate security log details.
     *
     * @param \App\Models\AuditLog $log
     * @return string
     */
    private function generateSecurityLogDetails($log)
    {
        $action = strtolower($log->action);
        $table = $log->table_name;
        
        if ($action === 'login') {
            return 'User login attempt recorded.';
        } elseif ($action === 'update' && $table === 'users') {
            return 'User profile or permissions modified.';
        } elseif ($action === 'create') {
            return 'New system record created.';
        } elseif ($action === 'delete') {
            return 'System record removed.';
        }
        
        return 'System activity recorded.';
    }
}