<?php

namespace App\Http\Controllers\Inventory\StockManagement;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BatchLookupController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display batch lookup page
     */
    public function batchLookup()
    {
        try {
            // Get recent active batches for initial display
            $recentBatches = Batch::with(['item.unit', 'supplier'])
                ->whereIn('status', ['active', 'quarantine'])
                ->whereHas('item', function($query) {
                    $query->where('is_active', true);
                })
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            return view('Inventory.stock_management.batch_lookup', compact('recentBatches'));

        } catch (\Exception $e) {
            Log::error('Error loading batch lookup: ' . $e->getMessage());
            return view('Inventory.stock_management.batch_lookup', ['recentBatches' => collect()]);
        }
    }

    /**
     * Search batches by various criteria
     */
    public function searchBatches(Request $request)
    {
        try {
            $request->validate([
                'search' => 'required|string|min:1'
            ]);

            $searchTerm = trim($request->search);
            
            $query = Batch::with(['item.unit', 'supplier'])
                ->whereHas('item', function($q) {
                    $q->where('is_active', true);
                });

            // Search by batch number, item name, item code, or barcode
            $query->where(function($q) use ($searchTerm) {
                $q->where('batch_number', 'ilike', "%{$searchTerm}%")
                  ->orWhereHas('item', function($itemQuery) use ($searchTerm) {
                      $itemQuery->where('name', 'ilike', "%{$searchTerm}%")
                               ->orWhere('item_code', 'ilike', "%{$searchTerm}%")
                               ->orWhere('barcode', 'ilike', "%{$searchTerm}%");
                  })
                  ->orWhereHas('supplier', function($supplierQuery) use ($searchTerm) {
                      $supplierQuery->where('name', 'ilike', "%{$searchTerm}%");
                  });
            });

            // Filter by status if specified
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            // Filter by expiry status if specified
            if ($request->has('expiry_filter') && $request->expiry_filter !== 'all') {
                $now = now();
                switch ($request->expiry_filter) {
                    case 'expired':
                        $query->where('expiry_date', '<', $now->toDateString());
                        break;
                    case 'expiring_soon':
                        $query->whereBetween('expiry_date', [
                            $now->toDateString(),
                            $now->copy()->addDays(7)->toDateString()
                        ]);
                        break;
                    case 'no_expiry':
                        $query->whereNull('expiry_date');
                        break;
                }
            }

            $batches = $query->orderByRaw("CASE WHEN expiry_date IS NOT NULL THEN expiry_date ELSE '9999-12-31'::date END ASC")
                           ->orderBy('created_at', 'desc')
                           ->limit(20)
                           ->get();

            // Transform batches for display
            $transformedBatches = $batches->map(function($batch) {
                return $this->transformBatchForDisplay($batch);
            });

            return response()->json([
                'success' => true,
                'data' => $transformedBatches,
                'total' => $transformedBatches->count(),
                'search_term' => $searchTerm
            ]);

        } catch (\Exception $e) {
            Log::error('Error searching batches: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to search batches: ' . $e->getMessage(),
                'data' => [],
                'total' => 0
            ], 500);
        }
    }

    /**
     * Get batch details by ID
     */
    public function getBatchDetails($batchId)
    {
        try {
            $batch = Batch::with(['item.unit', 'supplier'])
                ->whereHas('item', function($query) {
                    $query->where('is_active', true);
                })
                ->findOrFail($batchId);

            return response()->json([
                'success' => true,
                'data' => $this->transformBatchForDisplay($batch)
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting batch details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Batch not found'
            ], 404);
        }
    }

    /**
     * Transform batch data for display
     */
    private function transformBatchForDisplay($batch)
    {
        $now = now();
        $expiryDate = $batch->expiry_date ? \Carbon\Carbon::parse($batch->expiry_date) : null;
        $manufacturingDate = $batch->manufacturing_date ? \Carbon\Carbon::parse($batch->manufacturing_date) : null;
        
        // Calculate expiry status
        $expiryStatus = 'no_expiry';
        $expiryDays = null;
        $isExpiringSoon = false;
        $isExpired = false;

        if ($expiryDate) {
            $expiryDays = $now->diffInDays($expiryDate, false);
            $isExpired = $expiryDays < 0;
            $isExpiringSoon = !$isExpired && $expiryDays <= 7;
            
            if ($isExpired) {
                $expiryStatus = 'expired';
            } elseif ($isExpiringSoon) {
                $expiryStatus = 'expiring_soon';
            } else {
                $expiryStatus = 'active';
            }
        }

        // Determine status badge
        $statusBadge = match($batch->status) {
            'active' => ['class' => 'bg-green-100 text-green-800', 'text' => 'Active'],
            'quarantine' => ['class' => 'bg-yellow-100 text-yellow-800', 'text' => 'Quarantine'],
            'expired' => ['class' => 'bg-red-100 text-red-800', 'text' => 'Expired'],
            'consumed' => ['class' => 'bg-gray-100 text-gray-800', 'text' => 'Consumed'],
            default => ['class' => 'bg-gray-100 text-gray-800', 'text' => ucfirst($batch->status)]
        };

        // Add expiry warning for status badge
        if ($isExpiringSoon && $batch->status === 'active') {
            $statusBadge = ['class' => 'bg-red-100 text-red-800 animate-pulse', 'text' => 'Expiring Soon'];
        }

        return [
            'id' => $batch->id,
            'batch_number' => $batch->batch_number,
            'item' => [
                'id' => $batch->item->id,
                'name' => $batch->item->name,
                'item_code' => $batch->item->item_code,
                'barcode' => $batch->item->barcode,
                'unit' => [
                    'symbol' => $batch->item->unit->symbol ?? 'pcs'
                ]
            ],
            'supplier' => [
                'name' => $batch->supplier->name ?? 'N/A'
            ],
            'quantity' => (float) $batch->quantity,
            'unit_cost' => (float) $batch->unit_cost,
            'manufacturing_date' => $manufacturingDate ? $manufacturingDate->format('M d, Y') : 'N/A',
            'expiry_date' => $expiryDate ? $expiryDate->format('M d, Y') : 'No Expiry',
            'expiry_date_raw' => $expiryDate ? $expiryDate->format('Y-m-d') : null,
            'location' => $batch->location ?? 'Main Storage',
            'status' => $batch->status,
            'status_badge' => $statusBadge,
            'expiry_status' => $expiryStatus,
            'expiry_days' => $expiryDays,
            'is_expired' => $isExpired,
            'is_expiring_soon' => $isExpiringSoon,
            'created_at' => $batch->created_at->format('M d, Y H:i'),
            'icon' => $this->getBatchIcon($batch->item, $batch->status),
            'priority_color' => $this->getBatchPriorityColor($batch, $isExpired, $isExpiringSoon)
        ];
    }

    /**
     * Get icon for batch based on item type and status
     */
    private function getBatchIcon($item, $status)
    {
        $itemType = $item->item_type ?? 'supply';
        
        $iconMap = [
            'raw_material' => ['class' => 'fas fa-seedling', 'bg' => 'bg-green-100', 'color' => 'text-green-700'],
            'finished_good' => ['class' => 'fas fa-birthday-cake', 'bg' => 'bg-purple-100', 'color' => 'text-purple-700'],
            'semi_finished' => ['class' => 'fas fa-cookie-bite', 'bg' => 'bg-orange-100', 'color' => 'text-orange-700'],
            'supply' => ['class' => 'fas fa-box', 'bg' => 'bg-blue-100', 'color' => 'text-blue-700'],
        ];

        $baseIcon = $iconMap[$itemType] ?? $iconMap['supply'];
        
        // Add status-based modifications
        if ($status === 'quarantine') {
            $baseIcon['color'] = 'text-yellow-700';
        } elseif ($status === 'expired') {
            $baseIcon['color'] = 'text-red-700';
        }
        
        return $baseIcon;
    }

    /**
     * Get priority color for batch border
     */
    private function getBatchPriorityColor($batch, $isExpired, $isExpiringSoon)
    {
        if ($isExpired) {
            return 'border-red-500';
        } elseif ($isExpiringSoon) {
            return 'border-yellow-500';
        } elseif ($batch->status === 'quarantine') {
            return 'border-yellow-500';
        } else {
            return 'border-green-500';
        }
    }
}
