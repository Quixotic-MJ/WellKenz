<?php

namespace App\Http\Controllers\Inventory\Inbound;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\Item;
use App\Models\Supplier;
use App\Models\Category;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BatchController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display batch logs interface
     */
    public function batchLogs(Request $request)
    {
        try {
            // Build query for all batch records
            $query = Batch::with(['item.unit', 'supplier', 'item.category'])
                ->whereHas('item', function($q) {
                    $q->where('is_active', true);
                });

            // Simplified filters - only essential ones
            // Search filter
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('batch_number', 'like', '%' . $search . '%')
                      ->orWhereHas('item', function($itemQuery) use ($search) {
                          $itemQuery->where('name', 'like', '%' . $search . '%')
                                   ->orWhere('item_code', 'like', '%' . $search . '%');
                      })
                      ->orWhereHas('supplier', function($supplierQuery) use ($search) {
                          $supplierQuery->where('name', 'like', '%' . $search . '%');
                      });
                });
            }

            // Status filter (simplified to Active/Expired)
            if ($request->has('status') && $request->status !== 'all') {
                if ($request->status === 'expired') {
                    // Include both 'expired' status and expired by date
                    $query->where(function($q) {
                        $q->where('status', 'expired')
                          ->orWhere('expiry_date', '<', now()->toDateString());
                    });
                } elseif ($request->status === 'active') {
                    $query->whereIn('status', ['active', 'quarantine'])
                          ->where(function($q) {
                              $q->whereNull('expiry_date')
                                ->orWhere('expiry_date', '>=', now()->toDateString());
                          });
                } else {
                    $query->where('status', $request->status);
                }
            }

            // Supplier filter
            if ($request->has('supplier_id') && $request->supplier_id) {
                $query->where('supplier_id', $request->supplier_id);
            }

            // Default sorting: expiry_date ASC (expiring first), then created_at DESC
            $query->orderByRaw('CASE WHEN expiry_date IS NOT NULL THEN expiry_date ELSE \'9999-12-31\'::date END ASC')
                  ->orderBy('created_at', 'desc');

            $batches = $query->paginate(15)->withQueryString();

            // Get filter options
            $categories = Category::where('is_active', true)->orderBy('name')->get(['id', 'name']);
            $suppliers = Supplier::where('is_active', true)->orderBy('name')->get(['id', 'name']);
            
            // Get batch statistics
            $stats = [
                'total' => Batch::has('item')->count(),
                'active' => Batch::has('item')->where('status', 'active')->count(),
                'quarantine' => Batch::has('item')->where('status', 'quarantine')->count(),
                'expired' => Batch::has('item')->where('status', 'expired')->count(),
                'consumed' => Batch::has('item')->where('status', 'consumed')->count(),
                'expiring_soon' => Batch::has('item')
                    ->whereIn('status', ['active', 'quarantine'])
                    ->whereBetween('expiry_date', [now(), now()->addDays(7)])
                    ->count(),
            ];

            return view('Inventory.inbound.batch_logs', compact('batches', 'categories', 'suppliers', 'stats'));

        } catch (\Exception $e) {
            \Log::error('Error loading batch logs: ' . $e->getMessage());
            return view('Inventory.inbound.batch_logs', [
                'batches' => collect(),
                'categories' => collect(),
                'suppliers' => collect(),
                'stats' => ['total' => 0, 'active' => 0, 'quarantine' => 0, 'expired' => 0, 'consumed' => 0, 'expiring_soon' => 0]
            ]);
        }
    }

    /**
     * Edit batch form
     */
    public function editBatch($batchId)
    {
        // This would typically redirect to an edit form
        // For now, we'll return a simple response
        return redirect()->route('inventory.inbound.batch-logs')
                        ->with('info', 'Batch editing functionality not yet implemented.');
    }

    /**
     * Update batch status
     */
    public function updateBatchStatus(Request $request, $batchId)
    {
        try {
            $request->validate([
                'status' => 'required|in:active,quarantine,expired,consumed'
            ]);

            $batch = Batch::findOrFail($batchId);
            $oldStatus = $batch->status;
            $batch->status = $request->status;
            $batch->save();

            // Log the status change
            \Log::info("Batch status updated", [
                'batch_id' => $batch->id,
                'batch_number' => $batch->batch_number,
                'old_status' => $oldStatus,
                'new_status' => $request->status,
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Batch status updated successfully'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error updating batch status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update batch status'
            ], 500);
        }
    }

    /**
     * Export batch logs
     */
    public function exportBatchLogs(Request $request)
    {
        try {
            // This would typically generate an Excel/CSV file
            // For now, we'll return a simple response
            return response()->json([
                'success' => true,
                'message' => 'Export functionality not yet implemented'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error exporting batch logs: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to export batch logs'
            ], 500);
        }
    }

    /**
     * Get batch details for printing
     */
    public function getBatchForPrint($batchId)
    {
        try {
            $batch = Batch::with(['item.unit', 'supplier'])
                ->where('id', $batchId)
                ->whereIn('status', ['active', 'quarantine'])
                ->first();

            if (!$batch) {
                return response()->json([
                    'success' => false,
                    'message' => 'Batch not found or not available for printing'
                ], 404);
            }

            // Generate QR code data
            $qrCodeData = [
                'batch_number' => $batch->batch_number,
                'item_name' => $batch->item->name,
                'item_code' => $batch->item->item_code,
                'quantity' => $batch->quantity,
                'unit' => $batch->item->unit->symbol ?? 'pcs',
                'manufacturing_date' => $batch->manufacturing_date?->format('Y-m-d'),
                'expiry_date' => $batch->expiry_date?->format('Y-m-d'),
                'supplier' => $batch->supplier->name ?? 'N/A',
                'location' => $batch->location ?? 'Main Storage',
                'generated_at' => now()->format('Y-m-d H:i:s')
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'batch' => [
                        'id' => $batch->id,
                        'batch_number' => $batch->batch_number,
                        'item_name' => $batch->item->name,
                        'item_code' => $batch->item->item_code,
                        'sku' => $batch->item->item_code,
                        'quantity' => (float) $batch->quantity,
                        'unit_symbol' => $batch->item->unit->symbol ?? 'pcs',
                        'manufacturing_date' => $batch->manufacturing_date?->format('M d, Y'),
                        'expiry_date' => $batch->expiry_date?->format('M d, Y'),
                        'supplier_name' => $batch->supplier->name ?? 'N/A',
                        'location' => $batch->location ?? 'Main Storage',
                        'status' => $batch->status,
                        'is_perishable' => $batch->item->shelf_life_days > 0,
                        'days_until_expiry' => $batch->expiry_date ? now()->diffInDays($batch->expiry_date, false) : null,
                    ],
                    'qr_code_data' => json_encode($qrCodeData)
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error getting batch for print: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get batch details'
            ], 500);
        }
    }

    /**
     * Stream batch labels for printing
     */
    public function streamLabels(Request $request)
    {
        try {
            // Fix Input Handling: Handle single batch parameter from button click
            if ($request->has('batch') && !$request->has('batch_ids')) {
                $request->merge(['batch_ids' => [$request->batch]]);
            }

            $request->validate([
                'batch_ids' => 'required|array|min:1',
                'batch_ids.*' => 'exists:batches,id'
            ]);

            $batchIds = $request->batch_ids;
            $autoPrint = $request->boolean('auto_print', false);

            // Fetch batches with required relationships
            $batches = Batch::with(['item.unit', 'supplier', 'item.category'])
                ->whereIn('id', $batchIds)
                ->whereIn('status', ['active', 'quarantine'])
                ->get();

            if ($batches->isEmpty()) {
                return redirect()->back()
                    ->with('error', 'No valid batches found for printing.');
            }

            // Prepare QR code data for each batch
            $batches->each(function ($batch) {
                $batch->qr_code_data = json_encode([
                    'batch_number' => $batch->batch_number,
                    'item_name' => $batch->item->name,
                    'item_code' => $batch->item->item_code,
                    'quantity' => $batch->quantity,
                    'unit' => $batch->item->unit->symbol ?? 'pcs',
                    'manufacturing_date' => $batch->manufacturing_date?->format('Y-m-d'),
                    'expiry_date' => $batch->expiry_date?->format('Y-m-d'),
                    'supplier' => $batch->supplier->name ?? 'N/A',
                    'location' => $batch->location ?? 'Main Storage',
                    'generated_at' => now()->format('Y-m-d H:i:s')
                ]);
            });

            // Log the printing activity
            \Log::info("Batch labels streamed for printing", [
                'batch_ids' => $batchIds,
                'batch_count' => $batches->count(),
                'user_id' => auth()->id(),
                'user_name' => auth()->user()->name
            ]);

            return view('Inventory.inbound.labels', compact('batches', 'autoPrint'));

        } catch (\Exception $e) {
            \Log::error('Error streaming batch labels: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to prepare batch labels for printing: ' . $e->getMessage());
        }
    }

    /**
     * Process batch labels printing
     */
    public function printBatchLabelsProcess(Request $request)
    {
        try {
            $request->validate([
                'batch_selections' => 'required|array|min:1',
                'batch_selections.*.batch_id' => 'required|exists:batches,id',
                'batch_selections.*.quantity' => 'required|integer|min:1|max:1000'
            ]);

            $user = Auth::user();
            $printedBatches = [];
            $errors = [];

            foreach ($request->batch_selections as $selection) {
                try {
                    $batch = Batch::with(['item.unit', 'supplier'])
                        ->where('id', $selection['batch_id'])
                        ->whereIn('status', ['active', 'quarantine'])
                        ->first();

                    if (!$batch) {
                        $errors[] = "Batch not found: ID {$selection['batch_id']}";
                        continue;
                    }

                    $quantity = (int) $selection['quantity'];

                    // Create print job record (you might want to create a print_jobs table)
                    $printJob = [
                        'batch_id' => $batch->id,
                        'batch_number' => $batch->batch_number,
                        'item_name' => $batch->item->name,
                        'quantity' => $quantity,
                        'printed_by' => $user->id,
                        'printed_at' => now(),
                        'qr_code_data' => json_encode([
                            'batch_number' => $batch->batch_number,
                            'item_name' => $batch->item->name,
                            'item_code' => $batch->item->item_code,
                            'quantity' => $batch->quantity,
                            'unit' => $batch->item->unit->symbol ?? 'pcs',
                            'manufacturing_date' => $batch->manufacturing_date?->format('Y-m-d'),
                            'expiry_date' => $batch->expiry_date?->format('Y-m-d'),
                            'supplier' => $batch->supplier->name ?? 'N/A',
                        ])
                    ];

                    $printedBatches[] = $printJob;

                    // Log the printing activity
                    \Log::info("Batch labels printed", [
                        'batch_id' => $batch->id,
                        'batch_number' => $batch->batch_number,
                        'quantity' => $quantity,
                        'user_id' => $user->id,
                        'user_name' => $user->name
                    ]);

                } catch (\Exception $e) {
                    $errors[] = "Error processing batch ID {$selection['batch_id']}: " . $e->getMessage();
                }
            }

            return response()->json([
                'success' => true,
                'message' => count($printedBatches) . ' batch label jobs created successfully',
                'printed_count' => count($printedBatches),
                'error_count' => count($errors),
                'errors' => $errors,
                'print_jobs' => $printedBatches,
                'print_ready' => true // This would trigger the browser print dialog
            ]);

        } catch (\Exception $e) {
            \Log::error('Error processing batch labels printing: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to process batch labels printing: ' . $e->getMessage()
            ], 500);
        }
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
            \Log::error('Error loading batch lookup: ' . $e->getMessage());
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

            $batches = $query->orderByRaw('CASE WHEN expiry_date IS NOT NULL THEN expiry_date ELSE \'9999-12-31\'::date END ASC')
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
            \Log::error('Error searching batches: ' . $e->getMessage());
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
            \Log::error('Error getting batch details: ' . $e->getMessage());
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