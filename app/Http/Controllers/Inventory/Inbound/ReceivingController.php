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
            Log::error('Error calculating total inventory value: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get purchase order details for delivery receiving
     */
    public function getPurchaseOrder($id)
    {
        try {
            $purchaseOrder = PurchaseOrder::with(['supplier', 'purchaseOrderItems.item.unit'])
                ->findOrFail($id);
            
            // Check if PO can receive delivery
            if (!in_array($purchaseOrder->status, ['sent', 'confirmed', 'partial'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Purchase order cannot receive delivery in its current status.'
                ], 400);
            }

            // Calculate remaining quantities for each item
            $items = $purchaseOrder->purchaseOrderItems->map(function ($poItem) use ($purchaseOrder) {
                $quantityReceived = $poItem->quantity_received ?? 0;
                $quantityRemaining = $poItem->quantity_ordered - $quantityReceived;
                
                // Debug logging
                Log::debug('PO Item Debug', [
                    'po_id' => $purchaseOrder->id,
                    'item_id' => $poItem->id,
                    'quantity_ordered' => $poItem->quantity_ordered,
                    'quantity_received' => $quantityReceived,
                    'quantity_remaining' => $quantityRemaining,
                    'can_receive' => $quantityRemaining > 0
                ]);
                
                return [
                    'id' => $poItem->id,
                    'item_id' => $poItem->item_id,
                    'item_name' => $poItem->item->name,
                    'sku' => $poItem->item->sku ?? 'N/A',
                    'unit_symbol' => $poItem->item->unit->symbol ?? '',
                    'quantity_ordered' => $poItem->quantity_ordered,
                    'quantity_received' => $quantityReceived,
                    'quantity_remaining' => $quantityRemaining,
                    'unit_cost' => $poItem->unit_price,
                    'is_perishable' => $poItem->item->is_perishable ?? false,
                    'can_receive' => $quantityRemaining > 0
                ];
            })->filter(function ($item) {
                return $item['can_receive'];
            })->values();

            // Debug: Log how many items passed the filter
            Log::debug('PO Filter Results', [
                'po_id' => $purchaseOrder->id,
                'total_items' => $purchaseOrder->purchaseOrderItems->count(),
                'items_after_filter' => $items->count()
            ]);

            if ($items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'All items in this purchase order have been fully received.'
                ], 400);
            }

            $data = [
                'id' => $purchaseOrder->id,
                'po_number' => $purchaseOrder->po_number,
                'supplier_name' => $purchaseOrder->supplier->name,
                'order_date' => $purchaseOrder->order_date,
                'expected_delivery_date' => $purchaseOrder->expected_delivery_date,
                'status' => $purchaseOrder->status,
                'items' => $items
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting purchase order for delivery: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load purchase order details.'
            ], 500);
        }
    }

    /**
     * Search purchase orders for delivery receiving
     */
    public function searchPurchaseOrder(Request $request)
    {
        try {
            $query = $request->get('q', '');
            $status = $request->get('status', ['sent', 'confirmed', 'partial']);

            if (empty($query)) {
                return response()->json([
                    'success' => true,
                    'data' => []
                ]);
            }

            $purchaseOrders = PurchaseOrder::with(['supplier'])
                ->whereIn('status', is_array($status) ? $status : [$status])
                ->where(function ($q) use ($query) {
                    $q->where('po_number', 'ilike', "%{$query}%")
                      ->orWhereHas('supplier', function ($sq) use ($query) {
                          $sq->where('name', 'ilike', "%{$query}%");
                      });
                })
                ->orderBy('expected_delivery_date', 'asc')
                ->limit(20)
                ->get()
                ->map(function ($po) {
                    return [
                        'id' => $po->id,
                        'po_number' => $po->po_number,
                        'supplier_name' => $po->supplier->name,
                        'expected_delivery_date' => $po->expected_delivery_date,
                        'status' => $po->status,
                        'text' => $po->po_number . ' - ' . $po->supplier->name . 
                                 ' (Expected: ' . Carbon::parse($po->expected_delivery_date)->format('M d, Y') . ')'
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $purchaseOrders
            ]);

        } catch (\Exception $e) {
            Log::error('Error searching purchase orders: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Search failed.'
            ], 500);
        }
    }

    /**
     * Process received delivery
     */
    public function processDelivery(Request $request)
    {
        try {
            $request->validate([
                'purchase_order_id' => 'required|exists:purchase_orders,id',
                'items' => 'required|array',
                'items.*' => 'required|array'
            ]);

            $purchaseOrder = PurchaseOrder::findOrFail($request->purchase_order_id);
            
            // Check if PO can receive delivery
            if (!in_array($purchaseOrder->status, ['sent', 'confirmed', 'partial'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Purchase order cannot receive delivery in its current status.'
                ], 400);
            }

            $processedItems = 0;
            $totalQuantity = 0;
            $batchesCreated = 0;
            $itemsData = $request->items;

            DB::beginTransaction();

            try {
                foreach ($itemsData as $itemId => $itemData) {
                    $quantityReceived = floatval($itemData['quantity_received'] ?? 0);
                    
                    if ($quantityReceived <= 0) {
                        continue; // Skip items with no quantity
                    }

                    $purchaseOrderItem = PurchaseOrderItem::findOrFail($itemData['purchase_order_item_id']);
                    
                    // Validate quantity doesn't exceed remaining
                    $quantityRemaining = $purchaseOrderItem->quantity_ordered - ($purchaseOrderItem->quantity_received ?? 0);
                    if ($quantityReceived > $quantityRemaining) {
                        throw new \Exception("Quantity received for {$purchaseOrderItem->item->name} cannot exceed remaining amount ({$quantityRemaining}).");
                    }

                    // Create batch record
                    $batch = Batch::create([
                        'batch_number' => $itemData['batch_number'],
                        'item_id' => $purchaseOrderItem->item_id,
                        'supplier_id' => $purchaseOrder->supplier_id,
                        'purchase_order_id' => $purchaseOrder->id,
                        'quantity' => $quantityReceived,
                        'unit_cost' => $purchaseOrderItem->unit_price,
                        'received_date' => now(),
                        'expiry_date' => $itemData['expiry_date'] ?? null,
                        'condition' => $itemData['condition'] ?? 'good',
                        'receiving_notes' => $itemData['receiving_notes'] ?? null,
                        'damage_description' => $itemData['damage_description'] ?? null,
                        'status' => 'active',
                        'created_by' => Auth::id()
                    ]);

                    // Update current stock
                    $currentStock = CurrentStock::firstOrNew(['item_id' => $purchaseOrderItem->item_id]);
                    $currentStock->current_quantity += $quantityReceived;
                    
                    // Update average cost (simple method - could be enhanced)
                    if ($currentStock->current_quantity > 0) {
                        $totalValue = ($currentStock->current_quantity - $quantityReceived) * ($currentStock->average_cost ?? 0) + 
                                     $quantityReceived * $purchaseOrderItem->unit_cost;
                        $currentStock->average_cost = $totalValue / $currentStock->current_quantity;
                    } else {
                        $currentStock->average_cost = $purchaseOrderItem->unit_cost;
                    }
                    
                    $currentStock->save();

                    // Create stock movement record
                    StockMovement::create([
                        'item_id' => $purchaseOrderItem->item_id,
                        'batch_id' => $batch->id,
                        'movement_type' => 'purchase',
                        'quantity' => $quantityReceived,
                        'unit_cost' => $purchaseOrderItem->unit_price,
                        'total_cost' => $quantityReceived * $purchaseOrderItem->unit_price,
                        'batch_number' => $batch->batch_number,
                        'reference_type' => 'purchase_order',
                        'reference_id' => $purchaseOrder->id,
                        'notes' => 'Received from PO: ' . $purchaseOrder->po_number,
                        'user_id' => Auth::id()
                    ]);

                    // Update purchase order item received quantity
                    $purchaseOrderItem->quantity_received = ($purchaseOrderItem->quantity_received ?? 0) + $quantityReceived;
                    $purchaseOrderItem->save();

                    $processedItems++;
                    $totalQuantity += $quantityReceived;
                    $batchesCreated++;
                }

                // Check if PO is fully received
                $allItems = $purchaseOrder->purchaseOrderItems;
                $isFullyReceived = true;
                
                foreach ($allItems as $item) {
                    $received = $item->quantity_received ?? 0;
                    if ($received < $item->quantity_ordered) {
                        $isFullyReceived = false;
                        break;
                    }
                }

                // Update PO status
                if ($isFullyReceived) {
                    $purchaseOrder->status = 'completed';
                } else {
                    $purchaseOrder->status = 'partial';
                }
                $purchaseOrder->save();

                DB::commit();

                // Log the receiving activity
                Log::info('Delivery processed successfully', [
                    'purchase_order_id' => $purchaseOrder->id,
                    'po_number' => $purchaseOrder->po_number,
                    'items_processed' => $processedItems,
                    'total_quantity' => $totalQuantity,
                    'batches_created' => $batchesCreated,
                    'user_id' => Auth::id(),
                    'user_name' => Auth::user()->name
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "Successfully processed {$processedItems} items with total quantity of {$totalQuantity}. {$batchesCreated} batches created.",
                    'data' => [
                        'items_processed' => $processedItems,
                        'total_quantity' => $totalQuantity,
                        'batches_created' => $batchesCreated,
                        'po_status' => $purchaseOrder->status
                    ]
                ]);

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error processing delivery: ' . $e->getMessage(), [
                'purchase_order_id' => $request->purchase_order_id,
                'items_data' => $request->items,
                'user_id' => Auth::id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process delivery: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Validate delivery data before processing
     */
    public function validateDeliveryData(Request $request)
    {
        try {
            $request->validate([
                'purchase_order_id' => 'required|exists:purchase_orders,id',
                'items' => 'required|array'
            ]);

            $errors = [];
            $warnings = [];

            $purchaseOrder = PurchaseOrder::findOrFail($request->purchase_order_id);
            $itemsData = $request->items;

            foreach ($itemsData as $itemId => $itemData) {
                $quantityReceived = floatval($itemData['quantity_received'] ?? 0);
                
                if ($quantityReceived <= 0) {
                    continue;
                }

                $purchaseOrderItem = PurchaseOrderItem::findOrFail($itemData['purchase_order_item_id']);
                $quantityRemaining = $purchaseOrderItem->quantity_ordered - ($purchaseOrderItem->quantity_received ?? 0);
                
                if ($quantityReceived > $quantityRemaining) {
                    $errors[] = "Quantity for {$purchaseOrderItem->item->name} exceeds remaining amount ({$quantityRemaining})";
                }

                // Check for missing batch number
                if (empty($itemData['batch_number'])) {
                    $errors[] = "Batch number is required for {$purchaseOrderItem->item->name}";
                }

                // Check for expiry date on perishable items
                if ($purchaseOrderItem->item->is_perishable && empty($itemData['expiry_date'])) {
                    $errors[] = "Expiry date is required for perishable item: {$purchaseOrderItem->item->name}";
                }

                // Check for damage description when condition is not 'good'
                $condition = $itemData['condition'] ?? 'good';
                if ($condition !== 'good' && empty($itemData['damage_description'])) {
                    $errors[] = "Damage description is required for {$purchaseOrderItem->item->name} when condition is not 'good'";
                }

                // Warning for expiry dates in the past
                if (!empty($itemData['expiry_date'])) {
                    $expiryDate = Carbon::parse($itemData['expiry_date']);
                    if ($expiryDate->isPast()) {
                        $errors[] = "Expiry date for {$purchaseOrderItem->item->name} cannot be in the past";
                    }
                }
            }

            if (empty($itemsData) || collect($itemsData)->every(fn($item) => floatval($item['quantity_received'] ?? 0) <= 0)) {
                $errors[] = "At least one item must have a quantity greater than zero";
            }

            return response()->json([
                'success' => empty($errors),
                'errors' => $errors,
                'warnings' => $warnings
            ]);

        } catch (\Exception $e) {
            Log::error('Error validating delivery data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'errors' => ['Validation failed due to system error'],
                'warnings' => []
            ], 500);
        }
    }

    /**
     * Show the delivery receiving form
     */
    public function receiveDelivery()
    {
        try {
            // Get pending purchase orders that can receive delivery
            $purchaseOrders = PurchaseOrder::with(['supplier'])
                ->whereIn('status', ['sent', 'confirmed', 'partial'])
                ->where(function($query) {
                    $query->whereNull('expected_delivery_date')
                          ->orWhere('expected_delivery_date', '>=', now()->subDays(30));
                })
                ->orderBy('expected_delivery_date', 'asc')
                ->orderBy('created_at', 'desc')
                ->get();

            return view('Inventory.inbound.receive_delivery', compact('purchaseOrders'));

        } catch (\Exception $e) {
            Log::error('Error loading receiving form: ' . $e->getMessage());
            return view('Inventory.inbound.receive_delivery')->with('error', 'Unable to load purchase orders.');
        }
    }

    /**
     * Get receiving statistics for dashboard
     */
    public function getReceivingStatistics()
    {
        try {
            $today = now()->startOfDay();
            
            // Today's receiving statistics
            $todayDeliveries = Batch::whereDate('received_date', $today)
                ->distinct('purchase_order_id')
                ->count('purchase_order_id');
            
            $todayItemsReceived = Batch::whereDate('received_date', $today)
                ->sum('quantity');
            
            $todayBatchesCreated = Batch::whereDate('received_date', $today)
                ->count();

            // Weekly statistics
            $weekStart = now()->startOfWeek();
            $weekDeliveries = Batch::where('received_date', '>=', $weekStart)
                ->distinct('purchase_order_id')
                ->count('purchase_order_id');
            
            $weekItemsReceived = Batch::where('received_date', '>=', $weekStart)
                ->sum('quantity');

            // Pending deliveries
            $pendingDeliveries = PurchaseOrder::whereIn('status', ['sent', 'confirmed'])
                ->where('expected_delivery_date', '<=', now()->addDays(7))
                ->count();

            // Partial deliveries needing attention
            $partialDeliveries = PurchaseOrder::where('status', 'partial')
                ->where('expected_delivery_date', '>=', now()->subDays(30))
                ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'today' => [
                        'deliveries' => $todayDeliveries,
                        'items_received' => round($todayItemsReceived, 3),
                        'batches_created' => $todayBatchesCreated
                    ],
                    'week' => [
                        'deliveries' => $weekDeliveries,
                        'items_received' => round($weekItemsReceived, 3)
                    ],
                    'pending' => [
                        'deliveries' => $pendingDeliveries,
                        'partial_deliveries' => $partialDeliveries
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting receiving statistics: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'data' => [
                    'today' => [
                        'deliveries' => 0,
                        'items_received' => 0,
                        'batches_created' => 0
                    ],
                    'week' => [
                        'deliveries' => 0,
                        'items_received' => 0
                    ],
                    'pending' => [
                        'deliveries' => 0,
                        'partial_deliveries' => 0
                    ]
                ]
            ]);
        }
    }

}