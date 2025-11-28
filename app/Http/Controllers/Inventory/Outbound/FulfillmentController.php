<?php

namespace App\Http\Controllers\Inventory\Outbound;

use App\Http\Controllers\Controller;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\Batch;
use App\Models\StockMovement;
use App\Models\Notification;
use App\Models\User;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FulfillmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display fulfill requests page for inventory staff
     */
    public function fulfillRequests(Request $request)
    {
        try {
            // Get approved requisitions ready for picking
            $requisitions = Requisition::with([
                'requestedBy:id,name',
                'requisitionItems.item.unit',
                'requisitionItems.item.currentStockRecord'
            ])
            ->whereIn('status', ['approved', 'fulfilled'])
            ->orderByRaw("CASE WHEN status = 'approved' THEN 0 ELSE 1 END")
            ->orderBy('request_date', 'asc')
            ->get();

            // If specific requisition is requested to be expanded
            $expandedRequisitionId = $request->get('expand');
            if ($expandedRequisitionId) {
                // Reorder to put the expanded one first
                $expandedRequisition = $requisitions->firstWhere('id', $expandedRequisitionId);
                if ($expandedRequisition) {
                    $requisitions = $requisitions->reject(function($req) use ($expandedRequisitionId) {
                        return $req->id == $expandedRequisitionId;
                    });
                    $requisitions->prepend($expandedRequisition);
                }
            }

            return view('Inventory.outbound.fullfill_request', compact('requisitions'));

        } catch (\Exception $e) {
            \Log::error('Error loading fulfill requests: ' . $e->getMessage());
            return view('Inventory.outbound.fullfill_request', ['requisitions' => collect()]);
        }
    }

    /**
     * Quick action: Start picking for requisition
     */
    public function startPicking($requisitionId)
    {
        try {
            // Use requisitionItems relationship (exists) instead of items (doesn't exist)
            $requisition = Requisition::with(['requisitionItems', 'requestedBy'])->findOrFail($requisitionId);
            
            if ($requisition->status !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Requisition is not approved for picking. Current status: ' . $requisition->status
                ], 400);
            }

            // Update requisition status to fulfilled
            $requisition->update([
                'status' => 'fulfilled',
                'fulfilled_by' => Auth::id(),
                'fulfilled_at' => now()
            ]);

            // Notify requesting employee
            try {
                Notification::create([
                    'user_id' => $requisition->requested_by,
                    'title' => 'Requisition Fulfilled',
                    'message' => "Requisition {$requisition->requisition_number} has been fulfilled and is ready for pickup.",
                    'type' => 'requisition_update',
                    'priority' => 'normal',
                    'action_url' => route('employee.requisitions.details', $requisition->id),
                    'metadata' => [
                        'requisition_number' => $requisition->requisition_number,
                        'requisition_status' => 'fulfilled',
                        'status_changed_by' => Auth::user()->name ?? 'Inventory',
                        'status_changed_at' => now()->toDateTimeString(),
                    ],
                    'created_at' => now(),
                ]);
            } catch (\Exception $e) {
                \Log::warning('Failed to notify employee about fulfillment: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Picking started for requisition ' . $requisition->requisition_number . ' with ' . $requisition->requisitionItems->count() . ' items'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error starting picking: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to start picking: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Track item picking status (AJAX)
     */
    public function trackPicking(Request $request)
    {
        try {
            $request->validate([
                'requisition_item_id' => 'required|exists:requisition_items,id',
                'picked' => 'required|boolean'
            ]);

            // Store in session
            session()->put("picking.{$request->requisition_item_id}", $request->picked);

            return response()->json([
                'success' => true,
                'message' => 'Picking status updated'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error tracking picking: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update picking status'
            ], 500);
        }
    }

    /**
     * Confirm issuance of requisition (AJAX)
     */
    public function confirmIssuance(Request $request)
    {
        try {
            \Log::info('Confirm issuance request received:', $request->all());
            
            // Use more flexible validation - updated to handle multi_batch_selections
            $validatedData = $request->validate([
                'requisition_id' => 'required|exists:requisitions,id',
                'multi_batch_selections' => 'sometimes|array',
                'processed_items' => 'sometimes|array', 
                'shortages' => 'sometimes|array',
                'partial_fulfillment' => 'sometimes|boolean',
                'auto_generate_pr' => 'sometimes|boolean'
            ]);

            \Log::info('Validation passed, starting transaction');
            DB::beginTransaction();

            $requisition = Requisition::with('requisitionItems.item')->findOrFail($request->requisition_id);
            $user = Auth::user();

            // Handle multi-batch selections from frontend
            $multiBatchSelections = $request->multi_batch_selections ?? [];
            $processedItems = $request->processed_items ?? [];
            $shortages = $request->shortages ?? [];
            $partialFulfillment = $request->partial_fulfillment ?? false;
            $autoGeneratePR = $request->auto_generate_pr ?? false;
            
            \Log::info('Processing requisition with multi-batch selections:', [
                'requisition_id' => $requisition->id,
                'requisition_number' => $requisition->requisition_number,
                'multi_batch_selections_count' => count($multiBatchSelections),
                'processed_items_count' => count($processedItems),
                'partial_fulfillment' => $partialFulfillment,
                'auto_generate_pr' => $autoGeneratePR,
                'multi_batch_selections' => $multiBatchSelections
            ]);

            foreach ($requisition->requisitionItems as $requisitionItem) {
                $item = $requisitionItem->item;
                
                \Log::info('Processing requisition item:', [
                    'requisition_item_id' => $requisitionItem->id,
                    'item_name' => $item->name,
                    'quantity_requested' => $requisitionItem->quantity_requested
                ]);

                // Check if we have multi-batch selections for this item
                $batchSelections = $multiBatchSelections[$requisitionItem->id] ?? [];
                
                if (empty($batchSelections)) {
                    // No batch selections provided, try auto-selection (legacy fallback)
                    \Log::info('No multi-batch selections, trying auto-selection for item:', ['item_name' => $item->name]);
                    
                    $batch = Batch::where('item_id', $item->id)
                        ->whereIn('status', ['active', 'quarantine'])
                        ->where('quantity', '>', 0)
                        ->orderBy('expiry_date', 'asc')
                        ->first();

                    if (!$batch) {
                        \Log::warning('No available batches found for item:', $item->name);
                        // Mark as fully backordered
                        $requisitionItem->update([
                            'quantity_issued' => 0
                        ]);
                        continue;
                    }

                    // Allow partial fulfillment - issue what we have available
                    $quantityToIssue = min($batch->quantity, $requisitionItem->quantity_requested);
                    
                    // Update batch quantity
                    $batch->decrement('quantity', $quantityToIssue);

                    // Update requisition item issued quantity
                    $requisitionItem->update([
                        'quantity_issued' => $quantityToIssue
                    ]);

                    // Create stock movement
                    StockMovement::create([
                        'item_id' => $item->id,
                        'movement_type' => 'transfer',
                        'reference_number' => $requisition->requisition_number,
                        'quantity' => -$quantityToIssue,
                        'unit_cost' => $batch->unit_cost,
                        'total_cost' => -($quantityToIssue * $batch->unit_cost),
                        'batch_number' => $batch->batch_number,
                        'expiry_date' => $batch->expiry_date,
                        'location' => 'Kitchen/Production',
                        'notes' => "Auto-issuance for requisition {$requisition->requisition_number}. Issued: {$quantityToIssue}, Requested: {$requisitionItem->quantity_requested}",
                        'user_id' => $user->id
                    ]);

                    \Log::info('Auto-issuance completed:', [
                        'item_id' => $item->id,
                        'quantity_issued' => $quantityToIssue,
                        'quantity_requested' => $requisitionItem->quantity_requested
                    ]);

                } else {
                    // Process multi-batch selections from frontend
                    \Log::info('Processing multi-batch selections for item:', ['item_name' => $item->name]);
                    
                    $totalIssued = 0;
                    
                    foreach ($batchSelections as $batchSelection) {
                        $batch = Batch::find($batchSelection['batch_id']);
                        if (!$batch) {
                            \Log::warning('Selected batch not found:', $batchSelection['batch_id']);
                            continue;
                        }
                        
                        $quantityToIssue = (float) $batchSelection['quantity'];
                        $actualQuantity = min($quantityToIssue, $batch->quantity);
                        
                        // Update batch quantity
                        $batch->decrement('quantity', $actualQuantity);
                        $totalIssued += $actualQuantity;
                        
                        // Create stock movement for this batch
                        StockMovement::create([
                            'item_id' => $item->id,
                            'movement_type' => 'transfer',
                            'reference_number' => $requisition->requisition_number,
                            'quantity' => -$actualQuantity,
                            'unit_cost' => $batch->unit_cost,
                            'total_cost' => -($actualQuantity * $batch->unit_cost),
                            'batch_number' => $batch->batch_number,
                            'expiry_date' => $batch->expiry_date,
                            'location' => 'Kitchen/Production',
                            'notes' => "Partial issuance for requisition {$requisition->requisition_number} (Multi-batch). Issued: {$actualQuantity} from Batch {$batch->batch_number}",
                            'user_id' => $user->id
                        ]);
                        
                        \Log::info('Batch issuance completed:', [
                            'batch_id' => $batch->id,
                            'batch_number' => $batch->batch_number,
                            'quantity_issued' => $actualQuantity
                        ]);
                    }
                    
                    // Update requisition item issued quantity
                    $requisitionItem->update([
                        'quantity_issued' => $totalIssued
                    ]);
                    
                    \Log::info('Total multi-batch issuance for item:', [
                        'item_name' => $item->name,
                        'total_issued' => $totalIssued,
                        'requested' => $requisitionItem->quantity_requested
                    ]);
                }

            }

            // Update requisition status to fulfilled (even if partial)
            $requisition->update([
                'status' => 'fulfilled',
                'fulfilled_by' => $user->id,
                'fulfilled_at' => now()
            ]);

            // Create notifications about partial fulfillment if applicable
            if ($partialFulfillment && !empty($shortages)) {
                foreach ($shortages as $shortage) {
                    // Create purchase request for shortages if auto_generate_pr is true
                    if ($autoGeneratePR) {
                        $this->createAutoPurchaseRequest($shortage, $user);
                    }
                }
            }

            DB::commit();

            // Prepare response based on fulfillment type
            $response = [
                'success' => true,
                'message' => 'Requisition issuance confirmed successfully'
            ];

            if ($partialFulfillment) {
                $response['partial_fulfillment'] = true;
                $response['will_issue'] = count($processedItems);
                $response['will_backorder'] = count($shortages);
                $response['message'] .= ' - Partial fulfillment processed';
            }

            return response()->json($response);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error confirming issuance: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm issuance: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Create automatic purchase request for shortages
     */
    private function createAutoPurchaseRequest($shortage, $user)
    {
        try {
            // Generate PR number
            $prNumber = 'PR-AUTO-' . date('Y') . '-' . str_pad(\App\Models\PurchaseRequest::count() + 1, 4, '0', STR_PAD_LEFT);

            // Find or create item
            $item = \App\Models\Item::find($shortage['itemId']);
            if (!$item) {
                \Log::warning('Item not found for auto PR:', $shortage['itemId']);
                return;
            }

            // Create purchase request
            $purchaseRequest = \App\Models\PurchaseRequest::create([
                'pr_number' => $prNumber,
                'requested_by' => $user->id,
                'department' => 'Auto-Replenishment',
                'priority' => 'normal',
                'request_date' => now()->toDateString(),
                'notes' => "Auto-generated due to shortage in requisition fulfillment. Original request: {$shortage['requestedQty']}, Shortage: {$shortage['shortageQty']}",
                'status' => 'pending',
                'total_estimated_cost' => $shortage['shortageQty'] * ($item->cost_price ?? 0)
            ]);

            // Create purchase request item
            \App\Models\PurchaseRequestItem::create([
                'purchase_request_id' => $purchaseRequest->id,
                'item_id' => $item->id,
                'quantity_requested' => $shortage['shortageQty'],
                'unit_price_estimate' => $item->cost_price ?? 0,
                'total_estimated_cost' => $shortage['shortageQty'] * ($item->cost_price ?? 0)
            ]);

            \Log::info('Auto purchase request created:', [
                'pr_number' => $prNumber,
                'item_name' => $item->name,
                'shortage_qty' => $shortage['shortageQty']
            ]);

        } catch (\Exception $e) {
            \Log::error('Error creating auto purchase request: ' . $e->getMessage(), [
                'shortage_data' => $shortage,
                'user_id' => $user->id
            ]);
        }
    }

    /**
     * FEFO batch picking functionality
     */
    public function pickBatch($batchId)
    {
        try {
            $batch = Batch::with(['item.unit', 'supplier'])->findOrFail($batchId);
            
            if ($batch->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Batch is not available for picking. Status: ' . $batch->status
                ], 400);
            }

            $daysUntilExpiry = Carbon::parse($batch->expiry_date)->diffInDays(now());
            $isSampleData = $batch->expiry_date < now()->toDateString();

            DB::beginTransaction();

            // Use 'quarantine' status instead of 'reserved'
            $batch->update([
                'status' => 'quarantine', // This will work with your constraint
                'location' => 'FEFO_PRIORITY_ZONE',
                'updated_at' => now()
            ]);

            // Create FEFO reservation record
            $fefoNote = $isSampleData 
                ? "FEFO Priority (Sample Data)" 
                : "FEFO Priority - Expires in {$daysUntilExpiry} days";

            DB::table('stock_movements')->insert([
                'item_id' => $batch->item_id,
                'movement_type' => 'adjustment',
                'reference_number' => 'FEFO-' . $batch->batch_number . '-' . time(),
                'quantity' => 0,
                'unit_cost' => $batch->unit_cost,
                'total_cost' => 0,
                'batch_number' => $batch->batch_number,
                'expiry_date' => $batch->expiry_date,
                'location' => 'FEFO_PRIORITY_ZONE',
                'notes' => $fefoNote,
                'user_id' => Auth::id(),
                'created_at' => now()
            ]);

            // Notify production team about FEFO reservation
            $this->notifyFefoReservation($batch, $daysUntilExpiry, $isSampleData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $isSampleData
                    ? "✅ FEFO Priority: Batch {$batch->batch_number} quarantined for priority usage! Production team notified."
                    : "✅ FEFO Priority: Batch {$batch->batch_number} quarantined for priority usage! Expires in {$daysUntilExpiry} days. Production team notified.",
                'is_sample_data' => $isSampleData,
                'batch_status' => 'quarantine'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('Error picking batch for FEFO: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to quarantine batch: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Notify production team about FEFO reservation
     */
    private function notifyFefoReservation($batch, $daysUntilExpiry, $isSampleData)
    {
        try {
            $productionUsers = User::where('role', 'employee')
                ->where('is_active', true)
                ->get();

            $message = $isSampleData
                ? "📍 FEFO QUARANTINE: {$batch->item->name} (Batch: {$batch->batch_number}) has been quarantined for priority FEFO usage. Please use this batch first in production."
                : "📍 FEFO QUARANTINE: {$batch->item->name} (Batch: {$batch->batch_number}) has been quarantined for priority FEFO usage. Expires in {$daysUntilExpiry} days. Please use this batch first in production.";

            foreach ($productionUsers as $user) {
                Notification::create([
                    'user_id' => $user->id,
                    'title' => '🎯 FEFO Priority Batch Quarantined',
                    'message' => $message,
                    'type' => 'fefo_reservation',
                    'priority' => $daysUntilExpiry <= 2 ? 'high' : 'normal',
                    'action_url' => '/employee/recipes?highlight=' . $batch->item_id,
                    'metadata' => [
                        'batch_id' => $batch->id,
                        'item_id' => $batch->item_id,
                        'batch_number' => $batch->batch_number,
                        'item_name' => $batch->item->name,
                        'expiry_date' => $batch->expiry_date,
                        'days_until_expiry' => $daysUntilExpiry,
                        'reserved_by' => Auth::user()->name,
                        'status' => 'quarantine'
                    ]
                ]);
            }

            \Log::info("FEFO quarantine notification sent", [
                'batch_id' => $batch->id,
                'notified_production_users' => $productionUsers->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('Error notifying FEFO quarantine: ' . $e->getMessage());
        }
    }
}