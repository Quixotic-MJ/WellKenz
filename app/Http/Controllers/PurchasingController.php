<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\CurrentStock;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestPurchaseOrderLink;
use App\Models\SupplierItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class PurchasingController extends Controller
{
    /**
     * Show the purchasing dashboard/home page.
     */
    public function home()
    {
        // Get all dashboard data
        $lowStockItems = $this->getLowStockItems();
        $openPoValue = $this->getOpenPurchaseOrderValue();
        $openPoCount = $this->getOpenPurchaseOrderCount();
        $overdueDeliveries = $this->getOverdueDeliveries();
        $recentPurchaseOrders = $this->getRecentPurchaseOrders();
        $frequentSuppliers = $this->getFrequentSuppliers();

        return view('Purchasing.home', compact(
            'lowStockItems',
            'openPoValue', 
            'openPoCount',
            'overdueDeliveries',
            'recentPurchaseOrders',
            'frequentSuppliers'
        ));
    }

    /**
     * Get items that are below reorder level (Low Stock Alerts)
     */
    private function getLowStockItems()
    {
        $items = Item::where('is_active', true)
            ->with(['currentStockRecord', 'unit'])
            ->get()
            ->map(function ($item) {
                $currentStock = $item->currentStockRecord ? $item->currentStockRecord->current_quantity : 0;
                
                // Check if stock is below reorder point
                if ($currentStock <= $item->reorder_point) {
                    return [
                        'id' => $item->id,
                        'name' => $item->name,
                        'item_code' => $item->item_code,
                        'current_stock' => $currentStock,
                        'min_stock' => $item->reorder_point,
                        'max_stock' => $item->max_stock_level,
                        'unit' => $item->unit ? $item->unit->symbol : '',
                        'percentage' => $item->reorder_point > 0 ? round(($currentStock / $item->reorder_point) * 100, 1) : 0
                    ];
                }
                return null;
            })
            ->filter()
            ->sortByDesc('percentage')
            ->values();

        return $items;
    }

    /**
     * Get total value of open purchase orders
     */
    private function getOpenPurchaseOrderValue()
    {
        $value = PurchaseOrder::whereIn('status', ['sent', 'confirmed', 'partial'])
            ->sum('grand_total');

        return $value ?: 0;
    }

    /**
     * Get count of open purchase orders
     */
    private function getOpenPurchaseOrderCount()
    {
        return PurchaseOrder::whereIn('status', ['sent', 'confirmed', 'partial'])->count();
    }

    /**
     * Get overdue deliveries
     */
    private function getOverdueDeliveries()
    {
        $overduePOs = PurchaseOrder::whereIn('status', ['sent', 'confirmed', 'partial'])
            ->where('expected_delivery_date', '<', Carbon::now()->toDateString())
            ->whereNull('actual_delivery_date')
            ->with('supplier')
            ->get()
            ->map(function ($po) {
                $daysOverdue = Carbon::parse($po->expected_delivery_date)->diffInDays(Carbon::now());
                
                return [
                    'po_number' => $po->po_number,
                    'supplier_name' => $po->supplier ? $po->supplier->name : 'Unknown Supplier',
                    'supplier_contact_person' => $po->supplier ? $po->supplier->contact_person : null,
                    'supplier_phone' => $po->supplier ? $po->supplier->phone : null,
                    'supplier_email' => $po->supplier ? $po->supplier->email : null,
                    'expected_delivery_date' => $po->expected_delivery_date,
                    'days_overdue' => $daysOverdue,
                    'grand_total' => $po->grand_total,
                    'priority' => $daysOverdue > 7 ? 'urgent' : ($daysOverdue > 3 ? 'high' : 'normal')
                ];
            })
            ->sortByDesc('days_overdue')
            ->values();

        return $overduePOs;
    }

    /**
     * Get recent purchase orders for display
     */
    private function getRecentPurchaseOrders()
    {
        $recentOrders = PurchaseOrder::with(['supplier', 'purchaseOrderItems'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($po) {
                return [
                    'id' => $po->id,
                    'po_number' => $po->po_number,
                    'supplier_name' => $po->supplier ? $po->supplier->name : 'Unknown Supplier',
                    'status' => $po->status,
                    'total_amount' => $po->grand_total,
                    'order_date' => $po->order_date,
                    'expected_delivery_date' => $po->expected_delivery_date,
                    'item_count' => $po->purchaseOrderItems->count(),
                    'created_at' => $po->created_at,
                    'is_overdue' => $this->isOrderOverdue($po)
                ];
            });

        return $recentOrders;
    }

    /**
     * Get frequent suppliers (based on recent purchase orders)
     */
    private function getFrequentSuppliers()
    {
        // Get suppliers with most recent purchase orders
        $frequentSuppliers = PurchaseOrder::selectRaw('supplier_id, COUNT(*) as order_count')
            ->where('created_at', '>=', Carbon::now()->subDays(30)) // Last 30 days
            ->whereIn('status', ['sent', 'confirmed', 'partial', 'completed'])
            ->groupBy('supplier_id')
            ->orderByDesc('order_count')
            ->limit(10)
            ->with('supplier')
            ->get()
            ->map(function ($result) {
                if ($result->supplier) {
                    return [
                        'id' => $result->supplier->id,
                        'name' => $result->supplier->name,
                        'supplier_code' => $result->supplier->supplier_code,
                        'contact_person' => $result->supplier->contact_person,
                        'phone' => $result->supplier->phone,
                        'email' => $result->supplier->email,
                        'order_count' => $result->order_count,
                        'rating' => $result->supplier->rating
                    ];
                }
                return null;
            })
            ->filter()
            ->values();

        return $frequentSuppliers;
    }

    /**
     * Check if a purchase order is overdue
     */
    private function isOrderOverdue($purchaseOrder)
    {
        return $purchaseOrder->expected_delivery_date && 
               $purchaseOrder->expected_delivery_date->isPast() && 
               !in_array($purchaseOrder->status, ['completed', 'cancelled']);
    }

    /**
     * Generate a unique PO number
     */
    private function generatePONumber()
    {
        $year = date('Y');
        $lastPo = PurchaseOrder::where('po_number', 'like', "PO-{$year}-%")
            ->orderBy('po_number', 'desc')
            ->first();
        
        if ($lastPo) {
            $lastNumber = (int) substr($lastPo->po_number, -6);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return "PO-{$year}-" . str_pad($newNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * API endpoint to search items for PO creation
     */
    public function searchItems(Request $request)
    {
        $query = $request->get('q', '');
        
        $items = Item::where('is_active', true)
            ->where(function($q) use ($query) {
                if ($query) {
                    $q->where('name', 'ilike', "%{$query}%")
                      ->orWhere('item_code', 'ilike', "%{$query}%");
                }
            })
            ->with(['unit', 'currentStockRecord'])
            ->orderBy('name')
            ->limit(10)
            ->get();

        return response()->json($items->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'item_code' => $item->item_code,
                'unit' => $item->unit ? $item->unit->symbol : '',
                'current_stock' => $item->currentStockRecord ? $item->currentStockRecord->current_quantity : 0,
                'cost_price' => $item->cost_price,
                'reorder_point' => $item->reorder_point
            ];
        }));
    }

    /**
     * API endpoint to get supplier items with pricing
     */
    public function getSupplierItems(Supplier $supplier)
    {
        $supplierItems = \App\Models\SupplierItem::where('supplier_id', $supplier->id)
            ->with('item.unit')
            ->get()
            ->map(function ($supplierItem) {
                return [
                    'item_id' => $supplierItem->item_id,
                    'item_name' => $supplierItem->item->name,
                    'item_code' => $supplierItem->item->item_code,
                    'unit_symbol' => $supplierItem->item->unit ? $supplierItem->item->unit->symbol : '',
                    'supplier_item_code' => $supplierItem->supplier_item_code,
                    'unit_price' => $supplierItem->unit_price,
                    'minimum_order_quantity' => $supplierItem->minimum_order_quantity,
                    'lead_time_days' => $supplierItem->lead_time_days,
                    'is_preferred' => $supplierItem->is_preferred
                ];
            });

        return response()->json($supplierItems);
    }

    /**
     * Get purchasing dashboard summary statistics
     */
    public function getDashboardSummary()
    {
        $today = Carbon::now()->toDateString();
        $thisWeek = Carbon::now()->startOfWeek()->toDateString();
        $thisMonth = Carbon::now()->startOfMonth()->toDateString();

        $summary = [
            'today_orders' => PurchaseOrder::whereDate('order_date', $today)->count(),
            'week_orders' => PurchaseOrder::whereDate('order_date', '>=', $thisWeek)->count(),
            'month_orders' => PurchaseOrder::whereDate('order_date', '>=', $thisMonth)->count(),
            'pending_approvals' => PurchaseOrder::where('status', 'draft')->count(),
            'awaiting_delivery' => PurchaseOrder::whereIn('status', ['sent', 'confirmed'])->count(),
            'low_stock_alerts' => $this->getLowStockItems()->count(),
            'overdue_orders' => $this->getOverdueDeliveries()->count(),
            'total_active_suppliers' => Supplier::where('is_active', true)->count(),
            'average_delivery_time' => $this->getAverageDeliveryTime(),
            'last_updated' => Carbon::now()->toISOString()
        ];

        return response()->json($summary);
    }

    /**
     * Calculate average delivery time in days
     */
    private function getAverageDeliveryTime()
    {
        $completedOrders = PurchaseOrder::where('status', 'completed')
            ->whereNotNull('actual_delivery_date')
            ->whereNotNull('order_date')
            ->limit(50) // Last 50 orders for calculation
            ->get();

        if ($completedOrders->isEmpty()) {
            return 0;
        }

        $totalDays = 0;
        $count = 0;

        foreach ($completedOrders as $order) {
            $days = $order->order_date->diffInDays($order->actual_delivery_date);
            $totalDays += $days;
            $count++;
        }

        return $count > 0 ? round($totalDays / $count, 1) : 0;
    }

    /**
     * Show purchase orders list
     */
    public function purchaseOrders()
    {
        $purchaseOrders = PurchaseOrder::with(['supplier', 'purchaseOrderItems'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('Purchasing.purchase_orders.index', compact('purchaseOrders'));
    }

    /**
     * Show purchase order details
     */
    public function showPurchaseOrder(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'purchaseOrderItems.item', 'createdBy', 'approvedBy']);
        
        return view('Purchasing.purchase_orders.show', compact('purchaseOrder'));
    }

    /**
     * Show draft purchase orders
     */
    public function drafts()
    {
        $draftOrders = PurchaseOrder::where('status', 'draft')
            ->with([
                'supplier', 
                'purchaseOrderItems',
                'sourcePurchaseRequests',
                'createdBy'
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        // Add computed properties to each order
        $draftOrders->getCollection()->transform(function ($order) {
            // Total items count
            $order->total_items_count = $order->purchaseOrderItems->count();
            
            // Total quantity ordered
            $order->total_quantity_ordered = $order->purchaseOrderItems->sum('quantity_ordered');
            
            // Total quantity received
            $order->total_quantity_received = $order->purchaseOrderItems->sum('quantity_received');
            
            // Formatted total
            $order->formatted_total = '₱' . number_format($order->grand_total, 2);
            
            // Status badge HTML
            $statusColors = [
                'draft' => 'bg-gray-100 text-gray-800',
                'sent' => 'bg-blue-100 text-blue-800',
                'confirmed' => 'bg-yellow-100 text-yellow-800',
                'partial' => 'bg-orange-100 text-orange-800',
                'completed' => 'bg-green-100 text-green-800',
                'cancelled' => 'bg-red-100 text-red-800'
            ];
            $order->status_badge = '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ' . 
                                  ($statusColors[$order->status] ?? 'bg-gray-100 text-gray-800') . '">' . 
                                  ucfirst($order->status) . '</span>';
            
            // Is overdue
            $order->is_overdue = $order->expected_delivery_date && 
                                $order->expected_delivery_date->isPast() && 
                                !in_array($order->status, ['completed', 'cancelled']);
            
            // Delivery status
            if ($order->expected_delivery_date) {
                $daysDiff = $order->expected_delivery_date->diffInDays(now());
                if ($daysDiff < 0) {
                    $order->delivery_status = ['text' => abs($daysDiff) . ' days overdue', 'class' => 'text-red-500'];
                } elseif ($daysDiff <= 3) {
                    $order->delivery_status = ['text' => $daysDiff . ' days remaining', 'class' => 'text-orange-500'];
                } else {
                    $order->delivery_status = ['text' => $daysDiff . ' days remaining', 'class' => 'text-green-500'];
                }
            } else {
                $order->delivery_status = ['text' => 'Not scheduled', 'class' => 'text-gray-500'];
            }
            
            // Action capabilities
            $order->action_capabilities = [
                'can_edit' => in_array($order->status, ['draft']),
                'can_submit' => in_array($order->status, ['draft']),
                'can_delete' => in_array($order->status, ['draft']),
            ];
            
            return $order;
        });

        return view('Purchasing.purchase_orders.drafts', compact('draftOrders'));
    }

    /**
     * Show open purchase orders
     */
    public function openOrders()
    {
        $openOrders = PurchaseOrder::whereIn('status', ['sent', 'confirmed', 'partial'])
            ->with(['supplier', 'purchaseOrderItems'])
            ->orderBy('expected_delivery_date', 'asc')
            ->paginate(15);

        return view('Purchasing.purchase_orders.open_orders', compact('openOrders'));
    }

    /**
     * Show partial orders - redirect to history since partial orders view was removed
     */
    public function partialOrders()
    {
        return redirect('/purchasing/po/history');
    }

    /**
     * Show completed purchase orders history
     */
    public function completedHistory(Request $request)
    {
        $query = PurchaseOrder::where('status', 'completed')
            ->with(['supplier', 'purchaseOrderItems'])
            ->orderBy('actual_delivery_date', 'desc');

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('po_number', 'ilike', "%{$search}%")
                  ->orWhereHas('supplier', function($sq) use ($search) {
                      $sq->where('name', 'ilike', "%{$search}%")
                        ->orWhere('supplier_code', 'ilike', "%{$search}%");
                  });
            });
        }

        // Apply date filters
        if ($request->filled('date_from')) {
            $query->whereDate('actual_delivery_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('actual_delivery_date', '<=', $request->date_to);
        }

        // Export functionality
        if ($request->filled('export') && $request->export === 'csv') {
            return $this->exportCompletedHistory($query->get());
        }

        $completedOrders = $query->paginate(15);

        return view('Purchasing.purchase_orders.completed_history', compact('completedOrders'));
    }

    /**
     * Export completed history to CSV
     */
    private function exportCompletedHistory($orders)
    {
        $filename = 'completed_history_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($orders) {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($file, [
                'PO Number',
                'Supplier',
                'Order Date',
                'Delivery Date',
                'Total Amount',
                'Status'
            ]);
            
            // Add data rows
            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->po_number,
                    $order->supplier->name ?? 'Unknown',
                    $order->order_date->format('Y-m-d'),
                    $order->actual_delivery_date ? $order->actual_delivery_date->format('Y-m-d') : 'N/A',
                    $order->grand_total,
                    $order->status
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Show suppliers list
     */
    public function suppliers(Request $request)
    {
        // Build the query
        $query = Supplier::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('supplier_code', 'ilike', "%{$search}%")
                  ->orWhere('contact_person', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%")
                  ->orWhere('phone', 'ilike', "%{$search}%")
                  ->orWhere('tax_id', 'ilike', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Payment terms filter
        if ($request->filled('payment_terms')) {
            $query->where('payment_terms', $request->payment_terms);
        }

        // Rating filter
        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        // Load relationships and get results
        $suppliers = $query->withCount(['purchaseOrders'])
            ->with(['batches' => function($query) {
                $query->selectRaw('supplier_id, COUNT(*) as batch_count')->groupBy('supplier_id');
            }, 'supplierItems' => function($query) {
                $query->selectRaw('supplier_id, COUNT(*) as supplier_item_count')->groupBy('supplier_id');
            }])
            ->orderBy('name')
            ->paginate(15);

        // Get unique payment terms for filter dropdown
        $paymentTerms = Supplier::distinct()
            ->whereNotNull('payment_terms')
            ->orderBy('payment_terms')
            ->pluck('payment_terms')
            ->values();

        // Get supplier statistics for the view
        $stats = [
            'total_suppliers' => Supplier::count(),
            'active_suppliers' => Supplier::where('is_active', true)->count(),
            'inactive_suppliers' => Supplier::where('is_active', false)->count(),
            'avg_rating' => Supplier::whereNotNull('rating')->avg('rating') ?: 0,
            'suppliers_with_po' => Supplier::has('purchaseOrders')->count(),
        ];

        return view('Purchasing.suppliers.supplier_masterlist', compact('suppliers', 'paymentTerms', 'stats'));
    }

    /**
     * Store a new supplier
     */
    public function storeSupplier(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'supplier_code' => 'required|string|max:50|unique:suppliers,supplier_code',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'tax_id' => 'nullable|string|max:50',
            'payment_terms' => 'nullable|integer|min:0',
            'credit_limit' => 'nullable|numeric|min:0',
            'rating' => 'nullable|integer|min:1|max:5',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        Supplier::create($request->all());

        return redirect()->route('purchasing.suppliers.index')
            ->with('success', 'Supplier created successfully!');
    }

    /**
     * Update supplier
     */
    public function updateSupplier(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'supplier_code' => 'required|string|max:50|unique:suppliers,supplier_code,' . $supplier->id,
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'tax_id' => 'nullable|string|max:50',
            'payment_terms' => 'nullable|integer|min:0',
            'credit_limit' => 'nullable|numeric|min:0',
            'rating' => 'nullable|integer|min:1|max:5',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $supplier->update($request->all());

        return redirect()->route('purchasing.suppliers.index')
            ->with('success', 'Supplier updated successfully!');
    }

    /**
     * Delete supplier
     */
    public function destroySupplier(Supplier $supplier)
    {
        // Check for various foreign key constraints
        $conflicts = [];
        
        // Check for purchase orders
        if ($supplier->purchaseOrders()->count() > 0) {
            $conflicts[] = 'purchase orders';
        }
        
        // Check for batches (inventory records)
        if (\App\Models\Batch::where('supplier_id', $supplier->id)->count() > 0) {
            $conflicts[] = 'inventory batches';
        }
        
        // Check for supplier items (pricing records)
        if (\App\Models\SupplierItem::where('supplier_id', $supplier->id)->count() > 0) {
            $conflicts[] = 'supplier item records';
        }
        
        // If there are conflicts, show detailed error message
        if (!empty($conflicts)) {
            $conflictList = implode(', ', $conflicts);
            return redirect()->back()
                ->with('error', "Cannot delete supplier '{$supplier->name}' because it has existing {$conflictList}. Please remove all related records first or deactivate the supplier instead.");
        }

        $supplier->delete();

        return redirect()->route('purchasing.suppliers.index')
            ->with('success', 'Supplier deleted successfully!');
    }

    /**
     * Toggle supplier status
     */
    public function toggleSupplierStatus(Supplier $supplier)
    {
        $supplier->update(['is_active' => !$supplier->is_active]);
        
        $status = $supplier->is_active ? 'activated' : 'deactivated';
        
        return redirect()->route('purchasing.suppliers.index')
            ->with('success', "Supplier {$status} successfully!");
    }

    /**
     * Show supplier price list
     */
    public function supplierPriceList(Request $request)
    {
        // Build query for supplier items with relationships
        $query = \App\Models\SupplierItem::with([
            'supplier',
            'item.unit',
            'item.category'
        ])->whereHas('supplier', function($q) {
            $q->where('is_active', true);
        })->whereHas('item', function($q) {
            $q->where('is_active', true);
        });

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('item', function($itemQuery) use ($search) {
                    $itemQuery->where('name', 'ilike', "%{$search}%")
                              ->orWhere('item_code', 'ilike', "%{$search}%");
                });
            });
        }

        // Apply supplier filter
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Order by supplier_id and item_id for consistent ordering
        $supplierItems = $query->orderBy('supplier_id')
            ->orderBy('item_id')
            ->paginate(20);

        // Get all active suppliers for filter dropdown
        $suppliers = Supplier::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        // Get statistics for the view
        $stats = [
            'total_supplier_items' => \App\Models\SupplierItem::whereHas('supplier', function($q) {
                $q->where('is_active', true);
            })->whereHas('item', function($q) {
                $q->where('is_active', true);
            })->count(),
            'total_active_suppliers' => Supplier::where('is_active', true)->count(),
            'preferred_supplier_items' => \App\Models\SupplierItem::where('is_preferred', true)->whereHas('supplier', function($q) {
                $q->where('is_active', true);
            })->whereHas('item', function($q) {
                $q->where('is_active', true);
            })->count(),
        ];

        return view('Purchasing.suppliers.pricelist', compact('supplierItems', 'suppliers', 'stats'));
    }

    /**
     * Show price update form
     */
    public function showPriceUpdate(Request $request, \App\Models\SupplierItem $supplierItem = null)
    {
        // If specific supplier item is provided, show update form for that item
        if ($supplierItem) {
            $supplierItem->load(['supplier', 'item.unit']);
            return view('Purchasing.suppliers.update_price', compact('supplierItem'));
        }

        // Otherwise show bulk update interface
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $items = Item::where('is_active', true)->orderBy('name')->get();

        return view('Purchasing.suppliers.bulk_price_update', compact('suppliers', 'items'));
    }

    /**
     * Update single supplier item price
     */
    public function updateSupplierItemPrice(Request $request, \App\Models\SupplierItem $supplierItem)
    {
        $request->validate([
            'unit_price' => 'required|numeric|min:0.01',
            'minimum_order_quantity' => 'required|numeric|min:0.001',
            'lead_time_days' => 'required|integer|min:0',
            'is_preferred' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $supplierItem->update([
            'unit_price' => $request->unit_price,
            'minimum_order_quantity' => $request->minimum_order_quantity,
            'lead_time_days' => $request->lead_time_days,
            'is_preferred' => $request->is_preferred ?? false,
        ]);

        // Log the price update in audit trail if needed
        if (class_exists('App\Models\AuditLog')) {
            \App\Models\AuditLog::create([
                'table_name' => 'supplier_items',
                'record_id' => $supplierItem->id,
                'action' => 'UPDATE',
                'old_values' => $supplierItem->getOriginal(),
                'new_values' => $supplierItem->fresh()->toArray(),
                'user_id' => auth()->id(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Price updated successfully!',
            'data' => [
                'unit_price' => $supplierItem->unit_price,
                'minimum_order_quantity' => $supplierItem->minimum_order_quantity,
                'lead_time_days' => $supplierItem->lead_time_days,
                'is_preferred' => $supplierItem->is_preferred,
                'updated_at' => $supplierItem->updated_at->format('M d, Y H:i')
            ]
        ]);
    }

    /**
     * Bulk update supplier item prices
     */
    public function bulkUpdateSupplierPrices(Request $request)
    {
        $request->validate([
            'updates' => 'required|array',
            'updates.*.supplier_item_id' => 'required|exists:supplier_items,id',
            'updates.*.unit_price' => 'required|numeric|min:0.01',
            'updates.*.minimum_order_quantity' => 'required|numeric|min:0.001',
            'updates.*.lead_time_days' => 'required|integer|min:0',
            'updates.*.is_preferred' => 'boolean',
        ]);

        $updatedCount = 0;
        $errors = [];

        foreach ($request->updates as $update) {
            try {
                $supplierItem = SupplierItem::find($update['supplier_item_id']);
                if ($supplierItem) {
                    $oldData = $supplierItem->toArray();
                    
                    $supplierItem->update([
                        'unit_price' => $update['unit_price'],
                        'minimum_order_quantity' => $update['minimum_order_quantity'],
                        'lead_time_days' => $update['lead_time_days'],
                        'is_preferred' => $update['is_preferred'] ?? false,
                    ]);

                    $updatedCount++;

                    // Log each update if AuditLog exists
                    if (class_exists('App\Models\AuditLog')) {
                        \App\Models\AuditLog::create([
                            'table_name' => 'supplier_items',
                            'record_id' => $supplierItem->id,
                            'action' => 'UPDATE',
                            'old_values' => $oldData,
                            'new_values' => $supplierItem->toArray(),
                            'user_id' => auth()->id(),
                            'ip_address' => $request->ip(),
                            'user_agent' => $request->userAgent(),
                        ]);
                    }
                }
            } catch (\Exception $e) {
                $errors[] = "Failed to update item ID {$update['supplier_item_id']}: " . $e->getMessage();
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully updated {$updatedCount} price records.",
            'updated_count' => $updatedCount,
            'errors' => $errors
        ]);
    }

    /**
     * Get supplier items for editing (API endpoint)
     */
    public function getSupplierItemsForEdit(Request $request)
    {
        $supplierId = $request->get('supplier_id');
        $itemIds = $request->get('item_ids', []);

        $query = \App\Models\SupplierItem::with(['supplier', 'item.unit', 'item.category'])
            ->whereHas('supplier', function($q) use ($supplierId) {
                if ($supplierId) {
                    $q->where('id', $supplierId);
                }
                $q->where('is_active', true);
            })
            ->whereHas('item', function($q) {
                $q->where('is_active', true);
            });

        if (!empty($itemIds)) {
            $query->whereIn('item_id', $itemIds);
        }

        $supplierItems = $query->get();

        return response()->json($supplierItems->map(function($item) {
            return [
                'id' => $item->id,
                'supplier_id' => $item->supplier_id,
                'item_id' => $item->item_id,
                'supplier_name' => $item->supplier->name,
                'item_name' => $item->item->name,
                'item_code' => $item->item->item_code,
                'unit_symbol' => $item->item->unit->symbol ?? '',
                'current_unit_price' => $item->unit_price,
                'current_min_order_qty' => $item->minimum_order_quantity,
                'current_lead_time' => $item->lead_time_days,
                'is_preferred' => $item->is_preferred,
            ];
        }));
    }

    /**
     * Export supplier price list to CSV
     */
    public function exportSupplierPriceList(Request $request)
    {
        // Build query similar to supplierPriceList but get all records
        $query = \App\Models\SupplierItem::with([
            'supplier',
            'item.unit',
            'item.category'
        ])->whereHas('supplier', function($q) {
            $q->where('is_active', true);
        })->whereHas('item', function($q) {
            $q->where('is_active', true);
        });

        // Apply same filters as the main view
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('item', function($itemQuery) use ($search) {
                    $itemQuery->where('name', 'ilike', "%{$search}%")
                              ->orWhere('item_code', 'ilike', "%{$search}%");
                });
            });
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Order by supplier_id and item_id for consistent ordering
        $supplierItems = $query->orderBy('supplier_id')
            ->orderBy('item_id')
            ->get();

        $filename = 'supplier_pricelist_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($supplierItems) {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($file, [
                'Item Code',
                'Item Name',
                'Category',
                'Supplier Code',
                'Supplier Name',
                'Unit Price',
                'Unit',
                'Minimum Order Qty',
                'Lead Time (Days)',
                'Preferred Status',
                'Last Purchase Price',
                'Last Purchase Date',
                'Updated At'
            ]);
            
            // Add data rows
            foreach ($supplierItems as $supplierItem) {
                fputcsv($file, [
                    $supplierItem->item->item_code ?? '',
                    $supplierItem->item->name ?? '',
                    $supplierItem->item->category->name ?? '',
                    $supplierItem->supplier->supplier_code ?? '',
                    $supplierItem->supplier->name ?? '',
                    $supplierItem->unit_price,
                    $supplierItem->item->unit->symbol ?? '',
                    $supplierItem->minimum_order_quantity,
                    $supplierItem->lead_time_days,
                    $supplierItem->is_preferred ? 'Preferred' : 'Alternate',
                    $supplierItem->last_purchase_price ?? '',
                    $supplierItem->last_purchase_date ? $supplierItem->last_purchase_date->format('Y-m-d') : '',
                    $supplierItem->updated_at ? $supplierItem->updated_at->format('Y-m-d H:i:s') : ''
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Show purchase history reports
     */
    public function purchaseHistory()
    {
        $purchaseHistory = PurchaseOrder::with(['supplier'])
            ->where('status', 'completed')
            ->orderBy('actual_delivery_date', 'desc')
            ->paginate(20);

        return view('Purchasing.reports.purchase_history', compact('purchaseHistory'));
    }

    /**
     * Show supplier performance report
     */
    public function supplierPerformance()
    {
        $supplierPerformance = Supplier::withCount(['purchaseOrders' => function($query) {
                $query->where('status', 'completed');
            }])
            ->with(['purchaseOrders' => function($query) {
                $query->selectRaw('supplier_id, AVG(grand_total) as avg_order_value, COUNT(*) as total_orders')
                    ->where('status', 'completed')
                    ->groupBy('supplier_id');
            }])
            ->where('is_active', true)
            ->orderBy('purchase_orders_count', 'desc')
            ->get();

        return view('Purchasing.reports.supplier_performance', compact('supplierPerformance'));
    }

    /**
     * Show RTV (Return to Vendor) report
     */
    public function rtv()
    {
        // This would typically involve stock movements with return type
        // For now, showing a placeholder
        $rtvRecords = collect([]); // Placeholder

        return view('Purchasing.reports.RTV', compact('rtvRecords'));
    }

    /**
     * Show notifications page
     */
    public function notifications()
    {
        $notifications = \App\Models\Notification::forCurrentUser()
            ->paginate(20);

        return view('Purchasing.notification', compact('notifications'));
    }

    /**
     * API endpoint to search suppliers for the home page vendor lookup
     */
    public function searchSuppliers(Request $request)
    {
        $query = $request->get('q', '');
        
        $suppliers = Supplier::where('is_active', true)
            ->where(function($q) use ($query) {
                if ($query) {
                    $q->where('name', 'ilike', "%{$query}%")
                      ->orWhere('supplier_code', 'ilike', "%{$query}%")
                      ->orWhere('contact_person', 'ilike', "%{$query}%");
                }
            })
            ->orderBy('name')
            ->limit(10)
            ->get(['id', 'name', 'supplier_code', 'contact_person', 'phone', 'email']);

        return response()->json($suppliers);
    }

    /**
     * API endpoint to get supplier details for contact info
     */
    public function getSupplierDetails(Supplier $supplier)
    {
        return response()->json([
            'id' => $supplier->id,
            'name' => $supplier->name,
            'supplier_code' => $supplier->supplier_code,
            'contact_person' => $supplier->contact_person,
            'phone' => $supplier->phone,
            'mobile' => $supplier->mobile,
            'email' => $supplier->email,
            'address' => $supplier->address,
            'city' => $supplier->city,
            'province' => $supplier->province,
            'postal_code' => $supplier->postal_code,
            'tax_id' => $supplier->tax_id,
            'payment_terms' => $supplier->payment_terms,
            'credit_limit' => $supplier->credit_limit,
            'rating' => $supplier->rating,
            'is_active' => $supplier->is_active,
            'notes' => $supplier->notes,
            'created_at' => $supplier->created_at,
            'updated_at' => $supplier->updated_at
        ]);
    }

    /**
     * Get purchase request details for purchasing users
     */
    public function getPurchaseRequestDetails(PurchaseRequest $purchaseRequest)
    {
        try {
            // Only allow access to approved purchase requests
            if ($purchaseRequest->status !== 'approved') {
                return response()->json([
                    'message' => 'Purchase request is not approved or not available for viewing.'
                ], 403);
            }

            $purchaseRequest->load([
                'requestedBy:id,name,email',
                'approvedBy:id,name,email',
                'purchaseRequestItems' => function($query) {
                    $query->with([
                        'item:id,name,item_code,unit_id',
                        'item.unit:id,name,symbol'
                    ]);
                }
            ]);

            return response()->json([
                'purchaseRequest' => $purchaseRequest
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching purchase request details: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch purchase request details.'
            ], 500);
        }
    }

    /**
     * API endpoint to get supplier items for selected purchase requests
     * Only returns items that both: 1) are in the selected PRs, and 2) the supplier provides
     */
    public function getSupplierItemsForPRs(Request $request, Supplier $supplier)
    {
        try {
            $request->validate([
                'pr_ids' => 'required|string'
            ]);
            
            // Parse and validate PR IDs
            $prIds = array_map('intval', explode(',', $request->pr_ids));
            $prIds = array_filter($prIds); // Remove any empty values
            
            if (empty($prIds)) {
                return response()->json([
                    'message' => 'At least one purchase request must be selected.',
                    'items' => []
                ], 422);
            }
            
            // Validate that all PR IDs exist and are approved
            $existingPRs = PurchaseRequest::whereIn('id', $prIds)->where('status', 'approved')->count();
            if ($existingPRs !== count($prIds)) {
                return response()->json([
                    'message' => 'Some selected purchase requests are invalid or not approved.',
                    'items' => []
                ], 422);
            }



            // Get approved purchase requests with their items
            $purchaseRequests = PurchaseRequest::whereIn('id', $prIds)
                ->where('status', 'approved')
                ->with(['purchaseRequestItems.item.unit'])
                ->get();

            if ($purchaseRequests->isEmpty()) {
                return response()->json([
                    'message' => 'No valid approved purchase requests found.',
                    'items' => []
                ]);
            }

            // Collect all item IDs from selected PRs
            $prItemIds = collect();
            foreach ($purchaseRequests as $pr) {
                foreach ($pr->purchaseRequestItems as $item) {
                    $prItemIds->push($item->item_id);
                }
            }
            $prItemIds = $prItemIds->unique()->values();

            // Get supplier items that match the PR items
            $supplierItems = SupplierItem::where('supplier_id', $supplier->id)
                ->whereIn('item_id', $prItemIds)
                ->with(['item.unit', 'item.category'])
                ->get()
                ->map(function ($supplierItem) use ($purchaseRequests) {
                    // Calculate total requested quantity across all selected PRs
                    $totalRequestedQuantity = 0;
                    $sourcePRs = [];
                    
                    foreach ($purchaseRequests as $pr) {
                        foreach ($pr->purchaseRequestItems as $prItem) {
                            if ($prItem->item_id === $supplierItem->item_id) {
                                $totalRequestedQuantity += $prItem->quantity_requested;
                                $sourcePRs[] = [
                                    'pr_id' => $pr->id,
                                    'pr_number' => $pr->pr_number,
                                    'quantity' => $prItem->quantity_requested
                                ];
                            }
                        }
                    }

                    return [
                        'item_id' => (int)$supplierItem->item_id,
                        'item_name' => $supplierItem->item->name,
                        'item_code' => $supplierItem->item->item_code,
                        'unit_symbol' => $supplierItem->item->unit->symbol ?? '',
                        'category' => $supplierItem->item->category->name ?? 'No Category',
                        'supplier_item_code' => $supplierItem->supplier_item_code,
                        'unit_price' => (float)$supplierItem->unit_price,
                        'minimum_order_quantity' => (float)$supplierItem->minimum_order_quantity,
                        'lead_time_days' => (int)$supplierItem->lead_time_days,
                        'is_preferred' => (bool)$supplierItem->is_preferred,
                        'total_requested_quantity' => (float)$totalRequestedQuantity,
                        'source_prs' => $sourcePRs,
                        'estimated_total' => (float)($totalRequestedQuantity * $supplierItem->unit_price)
                    ];
                })
                ->sortByDesc('is_preferred')
                ->values();

            // Group items by availability status
            $availableItems = $supplierItems->where('unit_price', '>', 0);
            $unavailableItems = $supplierItems->filter(function($item) {
                return $item['unit_price'] <= 0;
            });

            return response()->json([
                'supplier' => [
                    'id' => $supplier->id,
                    'name' => $supplier->name,
                    'supplier_code' => $supplier->supplier_code,
                    'payment_terms' => $supplier->payment_terms
                ],
                'items' => [
                    'available' => $availableItems->values(),
                    'unavailable' => $unavailableItems->values()
                ],
                'summary' => [
                    'total_items_requested' => (int)$prItemIds->count(),
                    'items_available_from_supplier' => (int)$availableItems->count(),
                    'items_not_available' => (int)$unavailableItems->count(),
                    'total_estimated_cost' => (float)$availableItems->sum('estimated_total')
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching supplier items for PRs: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to fetch supplier items for selected purchase requests.',
                'error' => $e->getMessage(),
                'items' => []
            ], 500);
        }
    }

    /**
     * Show purchase order creation form
     */
    public function createPurchaseOrder()
    {
        // Get approved purchase requests with their items and relationships
        $approvedRequests = PurchaseRequest::where('status', 'approved')
            ->with([
                'requestedBy',
                'purchaseRequestItems.item.unit',
                'purchaseRequestItems.item.category'
            ])
            ->orderBy('request_date', 'desc')
            ->paginate(15);

        // Get all active suppliers
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        
        // Get unique departments from approved requests
        $departments = PurchaseRequest::where('status', 'approved')
            ->whereNotNull('department')
            ->distinct()
            ->pluck('department')
            ->sort()
            ->values();
        
        // Get non-paginated data for JavaScript
        $approvedRequestsForJS = PurchaseRequest::where('status', 'approved')
            ->with([
                'requestedBy',
                'purchaseRequestItems.item.unit',
                'purchaseRequestItems.item.category'
            ])
            ->orderBy('request_date', 'desc')
            ->get()
            ->map(function ($pr) {
                return [
                    'id' => $pr->id,
                    'pr_number' => $pr->pr_number,
                    'department' => $pr->department,
                    'priority' => $pr->priority,
                    'total_estimated_cost' => (float) $pr->total_estimated_cost,
                    'request_date' => $pr->request_date?->format('Y-m-d'),
                    'requestedBy' => [
                        'name' => $pr->requestedBy->name ?? 'N/A'
                    ],
                    'purchaseRequestItems' => $pr->purchaseRequestItems->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'item_id' => $item->item_id,
                            'quantity_requested' => (float) $item->quantity_requested,
                            'unit_price_estimate' => (float) $item->unit_price_estimate,
                            'total_estimated_cost' => (float) $item->total_estimated_cost,
                            'item' => [
                                'id' => $item->item->id,
                                'name' => $item->item->name,
                                'item_code' => $item->item->item_code,
                                'category' => [
                                    'name' => $item->item->category->name ?? 'No Category'
                                ],
                                'unit' => [
                                    'symbol' => $item->item->unit->symbol ?? 'pcs'
                                ]
                            ]
                        ];
                    })->toArray()
                ];
            })->toArray();
        
        return view('Purchasing.purchase_orders.create_po', compact('approvedRequests', 'suppliers', 'departments', 'approvedRequestsForJS'));
    }

    /**
     * Store new purchase order from selected purchase requests
     */
    public function storePurchaseOrder(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'expected_delivery_date' => 'required|date|after_or_equal:today',
            'notes' => 'nullable|string',
            'selected_pr_ids' => 'required|string',
            'save_option' => 'required|in:draft,create',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity_ordered' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0.01',
            'items.*.source_pr_id' => 'required|exists:purchase_requests,id',
        ]);

        // Parse selected PR IDs
        $selectedPRIds = array_map('intval', explode(',', $request->selected_pr_ids));
        
        // Verify all PR IDs exist and are approved
        $purchaseRequests = PurchaseRequest::whereIn('id', $selectedPRIds)
            ->where('status', 'approved')
            ->get();

        if ($purchaseRequests->count() !== count($selectedPRIds)) {
            return redirect()->back()
                ->with('error', 'Some selected purchase requests are invalid or not approved.')
                ->withInput();
        }

        // Generate PO number
        $poNumber = $this->generatePONumber();

        $totalAmount = 0;
        $poItems = [];

        // Calculate totals and prepare items
        foreach ($request->items as $itemData) {
            $quantity = floatval($itemData['quantity_ordered']);
            $unitPrice = floatval($itemData['unit_price']);
            $totalPrice = $quantity * $unitPrice;
            $totalAmount += $totalPrice;

            $poItems[] = [
                'item_id' => $itemData['item_id'],
                'quantity_ordered' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
                'source_pr_id' => $itemData['source_pr_id'],
            ];
        }

        // Determine status based on save option
        $status = $request->save_option === 'create' ? 'sent' : 'draft';

        // Create purchase order
        $purchaseOrder = PurchaseOrder::create([
            'po_number' => $poNumber,
            'supplier_id' => $request->supplier_id,
            'order_date' => Carbon::now()->toDateString(),
            'expected_delivery_date' => $request->expected_delivery_date,
            'status' => $status,
            'total_amount' => $totalAmount,
            'grand_total' => $totalAmount,
            'payment_terms' => 30, // Default, you might want to get from supplier
            'notes' => $request->notes,
            'created_by' => auth()->id(),
        ]);

        // Create purchase order items and link to PRs
        foreach ($poItems as $item) {
            PurchaseOrderItem::create(array_merge($item, [
                'purchase_order_id' => $purchaseOrder->id,
                'created_at' => Carbon::now(),
            ]));
        }

        // Create links between purchase requests and purchase order
        foreach ($selectedPRIds as $prId) {
            PurchaseRequestPurchaseOrderLink::create([
                'purchase_request_id' => $prId,
                'purchase_order_id' => $purchaseOrder->id,
                'consolidated_by' => auth()->id(),
                'consolidated_at' => Carbon::now(),
                'created_at' => Carbon::now(),
            ]);
        }

        // Update PR status to 'converted'
        PurchaseRequest::whereIn('id', $selectedPRIds)->update(['status' => 'converted']);

        // Determine redirect route and success message based on save option
        if ($request->save_option === 'create') {
            // Redirect to open orders for submitted POs
            return redirect()->route('purchasing.po.open')
                ->with('success', "Purchase Order {$poNumber} created and sent successfully from " . count($selectedPRIds) . " purchase request(s)!");
        } else {
            // Redirect to drafts for draft POs
            return redirect()->route('purchasing.po.drafts')
                ->with('success', "Purchase Order {$poNumber} saved as draft successfully from " . count($selectedPRIds) . " purchase request(s)!");
        }
    }

    /**
     * API endpoint to get dashboard metrics for real-time updates
     */
    public function getDashboardMetrics()
    {
        return response()->json([
            'low_stock_count' => $this->getLowStockItems()->count(),
            'open_po_value' => $this->getOpenPurchaseOrderValue(),
            'open_po_count' => $this->getOpenPurchaseOrderCount(),
            'overdue_deliveries' => $this->getOverdueDeliveries()->count(),
            'last_updated' => Carbon::now()->toISOString()
        ]);
    }

    /**
     * Print purchase order
     */
    public function printPurchaseOrder(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'supplier',
            'purchaseOrderItems.item.unit',
            'purchaseOrderItems.item.category',
            'createdBy',
            'approvedBy',
            'sourcePurchaseRequests'
        ]);

        return view('Purchasing.purchase_orders.print_po', compact('purchaseOrder'));
    }

    /**
     * Submit purchase order for approval
     */
    public function submitPurchaseOrder(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'draft') {
            return redirect()->back()->with('error', 'Only draft purchase orders can be submitted.');
        }

        $purchaseOrder->update([
            'status' => 'sent',
            'approved_by' => auth()->id(),
            'approved_at' => Carbon::now(),
        ]);

        return redirect()->route('purchasing.po.drafts')
            ->with('success', "Purchase Order {$purchaseOrder->po_number} submitted for approval successfully!");
    }

    /**
     * Edit purchase order
     */
    public function editPurchaseOrder(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'draft') {
            return redirect()->back()->with('error', 'Only draft purchase orders can be edited.');
        }

        // Redirect to create page with the order data for editing
        return redirect()->route('purchasing.po.create')
            ->with('edit_order', $purchaseOrder->id);
    }

    /**
     * Delete purchase order
     */
    public function destroyPurchaseOrder(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'draft') {
            return redirect()->back()->with('error', 'Only draft purchase orders can be deleted.');
        }

        // Restore PR status to 'approved' if this PO was created from PRs
        if ($purchaseOrder->sourcePurchaseRequests->count() > 0) {
            $prIds = $purchaseOrder->sourcePurchaseRequests->pluck('id');
            PurchaseRequest::whereIn('id', $prIds)->update(['status' => 'approved']);
            
            // Remove the links
            PurchaseRequestPurchaseOrderLink::where('purchase_order_id', $purchaseOrder->id)->delete();
        }

        $poNumber = $purchaseOrder->po_number;
        $purchaseOrder->delete();

        return redirect()->route('purchasing.po.drafts')
            ->with('success', "Purchase Order {$poNumber} deleted successfully!");
    }
}