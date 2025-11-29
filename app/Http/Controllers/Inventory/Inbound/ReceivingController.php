<?php

namespace App\Http\Controllers\Inventory\Inbound;

use App\Http\Controllers\Controller;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\Batch;
use App\Models\Requisition;
use App\Models\StockMovement;
use App\Models\CurrentStock;
use App\Models\User;
use App\Models\Notification;
use App\Models\Category;
use App\Models\Unit;
use App\Models\RtvTransaction;
use App\Models\RtvItem;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Str;
use Carbon\Carbon;

class ReceivingController extends Controller
{
    /**
     * Display inventory dashboard home with auto-expiry notifications
     */
    public function home()
    {
        try {
            // Pending Purchase Orders
            $pendingPurchaseOrders = PurchaseOrder::with(['supplier', 'purchaseOrderItems'])
                ->whereIn('status', ['sent', 'confirmed', 'partial'])
                ->orderBy('expected_delivery_date', 'desc')
                ->limit(5)
                ->get();

            // Expiring batches with auto-notification check
            $expiringBatches = Batch::with(['item.unit', 'supplier'])
                ->whereIn('status', ['active', 'quarantine'])
                ->where(function($query) {
                    $query->whereBetween('expiry_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
                        ->orWhereBetween('expiry_date', ['2024-01-01', '2024-12-31']);
                })
                ->orderBy('expiry_date', 'asc')
                ->limit(10)
                ->get();

            // Get recent notifications
            $recentNotifications = Notification::where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            // Calculate dashboard statistics
            $statistics = $this->getDashboardStatistics();

            // Process expiring batches and create notifications
            $this->processExpiringBatchesNotifications($expiringBatches);

            // Check for critical stock levels and create notifications
            $this->checkCriticalStockLevels();

            // FEFO (First Expired, First Out) batch alerts for today
            $todayFefoBatches = $this->getTodayFefoBatches();

            return view('Inventory.home', compact(
                'pendingPurchaseOrders',
                'expiringBatches',
                'recentNotifications',
                'statistics',
                'todayFefoBatches'
            ));

        } catch (\Exception $e) {
            Log::error('Error loading inventory dashboard: ' . $e->getMessage());
            return view('Inventory.home')->with('error', 'Unable to load dashboard data.');
        }
    }

    /**
     * Get dashboard statistics for the inventory home page
     */
    private function getDashboardStatistics()
    {
        try {
            return [
                'total_items' => Item::where('is_active', true)->count(),
                'low_stock_items' => Item::with(['currentStockRecord', 'reorder_point'])
                    ->where('is_active', true)
                    ->whereHas('currentStockRecord', function($query) {
                        $query->where('current_quantity', '<=', DB::raw('COALESCE(items.reorder_point, 10)'));
                    })
                    ->count(),
                'pending_purchase_orders' => PurchaseOrder::whereIn('status', ['sent', 'confirmed', 'partial'])->count(),
                'pending_deliveries' => PurchaseOrder::whereIn('status', ['sent', 'confirmed'])
                    ->where('expected_delivery_date', '<=', now()->addDays(3))
                    ->count(),
                'expiring_batches' => Batch::whereIn('status', ['active', 'quarantine'])
                    ->whereBetween('expiry_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
                    ->count(),
                'critical_expiring_batches' => Batch::whereIn('status', ['active', 'quarantine'])
                    ->where('expiry_date', '<=', now()->addDays(2)->toDateString())
                    ->count()
            ];
        } catch (\Exception $e) {
            Log::error('Error calculating dashboard statistics: ' . $e->getMessage());
            return [
                'total_items' => 0,
                'low_stock_items' => 0,
                'pending_purchase_orders' => 0,
                'pending_deliveries' => 0,
                'expiring_batches' => 0,
                'critical_expiring_batches' => 0
            ];
        }
    }

    /**
     * Process expiring batches and create notifications for staff
     */
    private function processExpiringBatchesNotifications($expiringBatches)
    {
        try {
            $notificationCreated = false;
            foreach ($expiringBatches as $batch) {
                $daysUntilExpiry = Carbon::parse($batch->expiry_date)->diffInDays(now());
                
                // Determine notification priority
                $priority = 'normal';
                if ($daysUntilExpiry <= 1) {
                    $priority = 'urgent';
                } elseif ($daysUntilExpiry <= 3) {
                    $priority = 'high';
                }

                // Create notification for inventory staff
                $existingNotification = Notification::where('user_id', Auth::id())
                    ->where('title', 'like', '%' . $batch->batch_number . '%')
                    ->where('type', 'expiry_alert')
                    ->first();

                if (!$existingNotification) {
                    Notification::create([
                        'user_id' => Auth::id(),
                        'title' => 'Batch Expiring Soon: ' . $batch->batch_number,
                        'message' => $batch->item->name . ' (Batch: ' . $batch->batch_number . ') expires on ' . 
                                   Carbon::parse($batch->expiry_date)->format('M j, Y') . '. 
                                   Days until expiry: ' . $daysUntilExpiry,
                        'type' => 'expiry_alert',
                        'priority' => $priority,
                        'action_url' => route('inventory.inbound.batch.expiring'),
                        'metadata' => json_encode([
                            'batch_id' => $batch->id,
                            'item_id' => $batch->item_id,
                            'batch_number' => $batch->batch_number,
                            'expiry_date' => $batch->expiry_date,
                            'days_until_expiry' => $daysUntilExpiry,
                            'quantity' => $batch->quantity,
                            'supplier' => $batch->supplier->name ?? 'Unknown'
                        ])
                    ]);
                    $notificationCreated = true;
                }
            }

            if ($notificationCreated) {
                Log::info('Created expiring batch notifications for user: ' . Auth::user()->name);
            }

        } catch (\Exception $e) {
            Log::error('Error processing expiring batch notifications: ' . $e->getMessage());
        }
    }

    /**
     * Check for critical stock levels and create notifications
     */
    private function checkCriticalStockLevels()
    {
        try {
            $criticalItems = Item::with(['currentStockRecord', 'unit'])
                ->where('is_active', true)
                ->whereHas('currentStockRecord', function($query) {
                    $query->where('current_quantity', '<=', DB::raw('COALESCE(items.reorder_point * 0.5, 5)'));
                })
                ->get();

            foreach ($criticalItems as $item) {
                $currentStock = $item->currentStockRecord->current_quantity;
                $reorderPoint = $item->reorder_point ?? 10;

                // Check if notification already exists for today
                $todayStart = now()->startOfDay();
                $existingNotification = Notification::where('user_id', Auth::id())
                    ->where('title', 'like', '%Critical Stock: ' . $item->name . '%')
                    ->where('type', 'stock_alert')
                    ->where('created_at', '>=', $todayStart)
                    ->first();

                if (!$existingNotification) {
                    Notification::create([
                        'user_id' => Auth::id(),
                        'title' => 'Critical Stock Alert: ' . $item->name,
                        'message' => 'Stock level for ' . $item->name . ' is critically low. Current: ' . 
                                   number_format((float) $currentStock, 1) . ' ' . ($item->unit->symbol ?? '') . 
                                   ', Reorder Point: ' . $reorderPoint,
                        'type' => 'stock_alert',
                        'priority' => 'high',
                        'action_url' => route('inventory.stock.levels'),
                        'metadata' => json_encode([
                            'item_id' => $item->id,
                            'current_stock' => $currentStock,
                            'reorder_point' => $reorderPoint,
                            'stock_percentage' => round(($currentStock / $reorderPoint) * 100, 1)
                        ])
                    ]);
                }
            }

        } catch (\Exception $e) {
            Log::error('Error checking critical stock levels: ' . $e->getMessage());
        }
    }

    /**
     * Get FEFO (First Expired, First Out) batches for today
     */
    private function getTodayFefoBatches()
    {
        try {
            return Batch::with(['item.unit', 'supplier'])
                ->where('status', 'active')
                ->where('expiry_date', '<=', now()->addDays(3))
                ->where('expiry_date', '>=', now()->toDateString())
                ->orderBy('expiry_date', 'asc')
                ->get()
                ->map(function($batch) {
                    $daysUntilExpiry = Carbon::parse($batch->expiry_date)->diffInDays(now());
                    $urgency = 'normal';
                    
                    if ($daysUntilExpiry <= 0) {
                        $urgency = 'expired';
                    } elseif ($daysUntilExpiry <= 1) {
                        $urgency = 'critical';
                    } elseif ($daysUntilExpiry <= 2) {
                        $urgency = 'high';
                    }

                    return [
                        'batch' => $batch,
                        'days_until_expiry' => $daysUntilExpiry,
                        'urgency' => $urgency,
                        'expiry_date_formatted' => Carbon::parse($batch->expiry_date)->format('M j, Y'),
                        'total_value' => $batch->quantity * $batch->unit_cost
                    ];
                });
        } catch (\Exception $e) {
            Log::error('Error getting today FEFO batches: ' . $e->getMessage());
            return collect([]);
        }
    }

    /**
     * Get FEFO batches for prioritized picking
     */
    public function getFefoBatches(Request $request)
    {
        try {
            $itemId = $request->get('item_id');
            
            if (!$itemId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item ID is required'
                ], 400);
            }

            $batches = Batch::with(['supplier'])
                ->where('item_id', $itemId)
                ->where('status', 'active')
                ->where('quantity', '>', 0)
                ->orderBy('expiry_date', 'asc')
                ->get()
                ->map(function($batch) {
                    $daysUntilExpiry = Carbon::parse($batch->expiry_date)->diffInDays(now());
                    
                    return [
                        'id' => $batch->id,
                        'batch_number' => $batch->batch_number,
                        'quantity_available' => $batch->quantity,
                        'unit_cost' => $batch->unit_cost,
                        'expiry_date' => $batch->expiry_date,
                        'days_until_expiry' => $daysUntilExpiry,
                        'supplier_name' => $batch->supplier->name ?? 'Unknown',
                        'priority_score' => $this->calculateFefoPriority($batch, $daysUntilExpiry),
                        'is_expired' => $daysUntilExpiry < 0,
                        'is_critical' => $daysUntilExpiry <= 1
                    ];
                })
                ->sortByDesc('priority_score');

            return response()->json([
                'success' => true,
                'data' => $batches->values(),
                'total_batches' => $batches->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting FEFO batches: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load FEFO batches'
            ], 500);
        }
    }

    /**
     * Calculate FEFO priority score for batch selection
     */
    private function calculateFefoPriority($batch, $daysUntilExpiry)
    {
        $priority = 100; // Base priority

        // Earlier expiry gets higher priority
        if ($daysUntilExpiry < 0) {
            $priority += 1000; // Expired batches highest priority
        } elseif ($daysUntilExpiry <= 1) {
            $priority += 500; // Critical expiry
        } elseif ($daysUntilExpiry <= 3) {
            $priority += 200; // High priority expiry
        } elseif ($daysUntilExpiry <= 7) {
            $priority += 100; // Medium priority expiry
        }

        // Lower quantity gets slightly higher priority (to clear smaller batches)
        if ($batch->quantity <= 10) {
            $priority += 50;
        } elseif ($batch->quantity <= 50) {
            $priority += 25;
        }

        return $priority;
    }

    /**
     * Mark notification as read
     */
    public function markNotificationAsRead(Notification $notification)
    {
        try {
            if ($notification->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $notification->update(['is_read' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);

        } catch (\Exception $e) {
            Log::error('Error marking notification as read: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notification as read'
            ], 500);
        }
    }

    /**
     * Get notification count for badge
     */
    public function getNotificationCount()
    {
        try {
            $count = Notification::where('user_id', Auth::id())
                ->where('is_read', false)
                ->count();

            return response()->json([
                'success' => true,
                'count' => $count
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting notification count: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'count' => 0
            ], 500);
        }
    }

    /**
     * Get recent activity feed for dashboard
     */
    public function getRecentActivity()
    {
        try {
            $activities = collect();

            // Recent stock movements
            $recentMovements = StockMovement::with(['item', 'user'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function($movement) {
                    return [
                        'type' => 'stock_movement',
                        'title' => 'Stock Movement: ' . $movement->item->name,
                        'description' => ($movement->quantity > 0 ? '+' : '') . $movement->quantity . 
                                       ' units ' . $movement->movement_type,
                        'user' => $movement->user->name ?? 'System',
                        'created_at' => $movement->created_at,
                        'time_ago' => $this->formatTimeAgo($movement->created_at)
                    ];
                });

            // Recent batch receipts
            $recentBatches = Batch::with(['item', 'supplier'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function($batch) {
                    return [
                        'type' => 'batch_receipt',
                        'title' => 'New Batch Received: ' . $batch->batch_number,
                        'description' => $batch->quantity . ' units of ' . $batch->item->name,
                        'user' => $batch->supplier->name ?? 'Unknown Supplier',
                        'created_at' => $batch->created_at,
                        'time_ago' => $this->formatTimeAgo($batch->created_at)
                    ];
                });

            // Merge and sort activities
            $activities = $recentMovements->concat($recentBatches)
                ->sortByDesc('created_at')
                ->take(10)
                ->values();

            return response()->json([
                'success' => true,
                'data' => $activities
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting recent activity: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => []
            ], 500);
        }
    }

    /**
     * Format time ago for activity display
     */
    private function formatTimeAgo($timestamp)
    {
        $diff = Carbon::now()->diffForHumans($timestamp, true);
        
        if (strpos($diff, 'minute') !== false) {
            return 'Just now';
        } elseif (strpos($diff, 'hour') !== false) {
            $hours = (int) filter_var($diff, FILTER_SANITIZE_NUMBER_INT);
            return $hours <= 1 ? '1 hr ago' : $hours . ' hrs ago';
        } elseif (strpos($diff, 'day') !== false) {
            $days = (int) filter_var($diff, FILTER_SANITIZE_NUMBER_INT);
            return $days === 1 ? '1 day ago' : $days . ' days ago';
        }
        
        return $diff;
    }

    /**
     * Get dashboard overview data for AJAX updates
     */
    public function getDashboardOverview()
    {
        try {
            $overview = [
                'statistics' => $this->getDashboardStatistics(),
                'critical_batches' => Batch::whereIn('status', ['active', 'quarantine'])
                    ->where('expiry_date', '<=', now()->addDays(2)->toDateString())
                    ->count(),
                'pending_deliveries' => PurchaseOrder::whereIn('status', ['sent', 'confirmed'])
                    ->where('expected_delivery_date', '<=', now()->addDays(3)->toDateString())
                    ->count(),
                'low_stock_alerts' => Item::with(['currentStockRecord', 'reorder_point'])
                    ->where('is_active', true)
                    ->whereHas('currentStockRecord', function($query) {
                        $query->where('current_quantity', '<=', DB::raw('COALESCE(items.reorder_point * 0.5, 5)'));
                    })
                    ->count(),
                'total_inventory_value' => $this->calculateTotalInventoryValue()
            ];

            return response()->json([
                'success' => true,
                'data' => $overview
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting dashboard overview: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load dashboard overview'
            ], 500);
        }
    }

    /**
     * Calculate total inventory value
     */
    private function calculateTotalInventoryValue()
    {
        try {
            $totalValue = CurrentStock::join('items', 'current_stock.item_id', '=', 'items.id')
                ->where('items.is_active', true)
                ->sum(DB::raw('current_stock.current_quantity * COALESCE(current_stock.average_cost, items.cost_price)'));

            return $totalValue;
        } catch (\Exception $e) {
            \Log::error('Error calculating total inventory value: ' . $e->getMessage());
            return 0;
        }
    }

}