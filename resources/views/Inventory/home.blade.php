@extends('Inventory.layout.app')

@section('content')
<div class="space-y-8 font-sans text-gray-600">

    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h1 class="font-display text-3xl font-bold text-chocolate mb-1">Warehouse Home</h1>
            <p class="text-sm text-gray-500 flex items-center gap-2">
                <span>Overview for {{ date('F d, Y') }}</span>
                <span class="text-border-soft">|</span>
                <span class="text-caramel font-bold bg-cream-bg px-2 py-0.5 rounded-md border border-border-soft">Shift A (Morning)</span>
            </p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('inventory.inbound.receive') }}" class="flex items-center justify-center px-5 py-2.5 bg-chocolate text-white text-sm font-bold rounded-lg hover:bg-chocolate-dark transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                <i class="fas fa-truck-loading mr-2"></i> Receive Delivery
            </a>
        </div>
    </div>

    {{-- 2. OPERATIONAL WIDGETS --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- WIDGET 1: INCOMING DELIVERIES (Column 1) --}}
        <div class="bg-white border border-border-soft rounded-xl shadow-sm flex flex-col h-full overflow-hidden">
            <div class="px-5 py-4 border-b border-border-soft bg-cream-bg flex justify-between items-center">
                <h3 class="font-display text-lg font-bold text-chocolate">Incoming</h3>
                <span class="bg-white border border-border-soft text-chocolate text-xs font-bold px-2.5 py-1 rounded-full shadow-sm">
                    {{ $pendingPurchaseOrders->count() }} Expected
                </span>
            </div>
            
            <div class="flex-1 overflow-y-auto p-0 custom-scrollbar h-96">
                <div class="divide-y divide-gray-100">
                    @forelse($pendingPurchaseOrders as $po)
                        <div class="p-4 hover:bg-gray-50 transition-colors group relative">
                            <div class="absolute left-0 top-0 bottom-0 w-1 bg-caramel group-hover:bg-chocolate transition-colors"></div>
                            <div class="flex items-start pl-2">
                                {{-- Date Box --}}
                                <div class="flex-shrink-0 mr-4 text-center bg-cream-bg rounded-lg p-2 border border-border-soft w-16">
                                    <span class="block text-lg font-bold text-chocolate leading-none">
                                        {{ $po->expected_delivery_date ? \Carbon\Carbon::parse($po->expected_delivery_date)->format('d') : '--' }}
                                    </span>
                                    <span class="block text-[10px] text-gray-500 uppercase mt-1">
                                        {{ $po->expected_delivery_date ? \Carbon\Carbon::parse($po->expected_delivery_date)->format('M') : 'N/A' }}
                                    </span>
                                </div>
                                
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-start">
                                        <h4 class="text-sm font-bold text-gray-900 truncate" title="{{ $po->supplier->name ?? 'Unknown Supplier' }}">
                                            {{ $po->supplier->name ?? 'Unknown Supplier' }}
                                        </h4>
                                        @php
                                            $statusConfig = [
                                                'sent' => ['color' => 'blue', 'icon' => 'fa-paper-plane'],
                                                'confirmed' => ['color' => 'green', 'icon' => 'fa-check'],
                                                'partial' => ['color' => 'amber', 'icon' => 'fa-box-open']
                                            ];
                                            $conf = $statusConfig[$po->status] ?? ['color' => 'gray', 'icon' => 'fa-circle'];
                                        @endphp
                                        <span class="text-[10px] bg-{{ $conf['color'] }}-50 text-{{ $conf['color'] }}-700 px-2 py-0.5 rounded border border-{{ $conf['color'] }}-100 capitalize">
                                            {{ $po->status }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1 font-mono">PO: {{ $po->po_number }}</p>
                                    <p class="text-xs text-caramel mt-1 font-medium">
                                        <i class="fas fa-box mr-1"></i> {{ $po->purchaseOrderItems ? $po->purchaseOrderItems->count() : 0 }} Line Items
                                    </p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center h-full py-10 text-gray-400">
                            <i class="fas fa-truck text-3xl mb-2 opacity-20"></i>
                            <p class="text-sm">No pending deliveries</p>
                        </div>
                    @endforelse
                </div>
            </div>
            <div class="p-3 bg-gray-50 border-t border-border-soft text-center">
                <a href="{{ route('inventory.purchase-orders.index') }}" class="text-xs font-bold text-caramel hover:text-chocolate transition-colors uppercase tracking-wide">
                    View All Orders &rarr;
                </a>
            </div>
        </div>

        {{-- WIDGET 2: EXPIRING SOON (Column 2) --}}
        <div class="bg-white border border-border-soft rounded-xl shadow-sm flex flex-col h-full overflow-hidden">
            <div class="px-5 py-4 border-b border-border-soft bg-cream-bg flex justify-between items-center">
                <div>
                    <h3 class="font-display text-lg font-bold text-red-700">Expiring Soon</h3>
                    <p class="text-[10px] text-red-500 uppercase tracking-wider font-bold">Priority: FEFO</p>
                </div>
                <div class="w-8 h-8 bg-red-50 rounded-full flex items-center justify-center text-red-600 border border-red-100">
                    <i class="fas fa-hourglass-half text-sm"></i>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-0 custom-scrollbar h-96">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-[10px] font-bold text-gray-500 uppercase tracking-wider">Item / Batch</th>
                            <th class="px-4 py-2 text-right text-[10px] font-bold text-gray-500 uppercase tracking-wider">Expiry</th>
                            <th class="px-4 py-2 text-right text-[10px] font-bold text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 bg-white">
                        @forelse($expiringBatches as $batch)
                            @php
                                $daysUntilExpiry = $batch->expiry_date ? \Carbon\Carbon::parse($batch->expiry_date)->diffInDays(now()) : 999;
                                $isCritical = $daysUntilExpiry <= 1;
                                $isWarning = $daysUntilExpiry <= 3;
                                $isQuarantined = $batch->status === 'quarantine';
                                
                                $rowClass = $isCritical ? 'bg-red-50/50' : '';
                                $textClass = $isCritical ? 'text-red-700' : ($isWarning ? 'text-amber-600' : 'text-gray-600');
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors {{ $rowClass }}">
                                <td class="px-4 py-3">
                                    <p class="text-xs font-bold text-gray-900 line-clamp-1" title="{{ $batch->item->name ?? '' }}">
                                        {{ $batch->item->name ?? 'Unknown' }}
                                    </p>
                                    <p class="text-[10px] text-gray-500 font-mono mt-0.5">{{ $batch->batch_number }}</p>
                                    
                                    @if($isQuarantined)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-purple-100 text-purple-800 mt-1">
                                            <i class="fas fa-lock mr-1"></i> Reserved
                                        </span>
                                    @elseif($isWarning)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-100 text-amber-800 mt-1">
                                            <i class="fas fa-bell mr-1"></i> Alerted
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right whitespace-nowrap">
                                    <span class="text-xs font-bold {{ $textClass }} block">
                                        {{ $isCritical ? 'Today' : $daysUntilExpiry . ' Days' }}
                                    </span>
                                    <span class="text-[10px] text-gray-400">
                                        {{ number_format($batch->quantity, 2) }} {{ $batch->item->unit->symbol ?? '' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if(!$isQuarantined)
                                        <button onclick="pickBatch({{ $batch->id }})" 
                                            class="text-xs font-bold text-white bg-chocolate hover:bg-chocolate-dark px-2 py-1 rounded shadow-sm transition-colors">
                                            Reserve
                                        </button>
                                    @else
                                        <span class="text-gray-400 text-xs"><i class="fas fa-check"></i></span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-10 text-center text-gray-400 text-xs italic">
                                    No immediate expiry risks.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($expiringBatches->count() > 0)
            <div class="p-2 bg-red-50/50 border-t border-red-100 text-center">
                <p class="text-[10px] text-red-600 font-medium flex justify-center items-center gap-2">
                    <i class="fas fa-exclamation-circle"></i> Auto-alerts sent to Production Team
                </p>
            </div>
            @endif
        </div>

        {{-- WIDGET 3: REQUISITIONS (Column 3 - Stacked) --}}
        <div class="space-y-6 h-full flex flex-col">
            
            {{-- 3A: PENDING APPROVAL --}}
            <div class="bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden flex-1 flex flex-col">
                <div class="px-4 py-3 border-b border-border-soft bg-cream-bg flex justify-between items-center">
                    <h3 class="font-display text-sm font-bold text-chocolate uppercase tracking-wide">Approvals</h3>
                    <span class="bg-amber-100 text-amber-800 text-xs font-bold px-2 py-0.5 rounded-full">
                        {{ $pendingApprovalRequisitions->count() }}
                    </span>
                </div>
                <div class="flex-1 overflow-y-auto p-0 custom-scrollbar max-h-48">
                    <ul class="divide-y divide-gray-100">
                        @forelse($pendingApprovalRequisitions as $req)
                            <li class="p-3 hover:bg-gray-50 transition-colors">
                                <div class="flex justify-between mb-1">
                                    <span class="text-xs font-mono font-bold text-gray-600">#{{ $req->requisition_number }}</span>
                                    <span class="text-[10px] text-gray-400">{{ $req->created_at->diffForHumans(null, true, true) }}</span>
                                </div>
                                <p class="text-xs text-gray-800 font-medium mb-1">
                                    {{ $req->department }} <span class="text-gray-400 font-normal">({{ $req->requestedBy->name ?? 'User' }})</span>
                                </p>
                                <div class="flex justify-between items-center">
                                    <span class="text-[10px] bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded border border-gray-200">
                                        {{ $req->requisitionItems->count() }} Items
                                    </span>
                                    <span class="text-[10px] text-amber-600 font-bold">Pending</span>
                                </div>
                            </li>
                        @empty
                            <li class="p-4 text-center text-xs text-gray-400 italic">No pending approvals</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            {{-- 3B: READY FOR PICKING --}}
            <div class="bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden flex-1 flex flex-col">
                <div class="px-4 py-3 border-b border-border-soft bg-cream-bg flex justify-between items-center">
                    <h3 class="font-display text-sm font-bold text-chocolate uppercase tracking-wide">Pick List</h3>
                    <span class="bg-green-100 text-green-800 text-xs font-bold px-2 py-0.5 rounded-full">
                        {{ $readyForPickingRequisitions->count() }}
                    </span>
                </div>
                <div class="flex-1 overflow-y-auto p-0 custom-scrollbar max-h-48">
                    <ul class="divide-y divide-gray-100">
                        @forelse($readyForPickingRequisitions as $req)
                            <li class="p-3 hover:bg-green-50/30 transition-colors group">
                                <div class="flex justify-between mb-1">
                                    <span class="text-xs font-mono font-bold text-gray-800">#{{ $req->requisition_number }}</span>
                                    <button onclick="startPicking({{ $req->id }})" 
                                        class="text-[10px] bg-green-600 text-white px-2 py-1 rounded hover:bg-green-700 transition shadow-sm">
                                        Start Pick
                                    </button>
                                </div>
                                <p class="text-xs text-gray-600">
                                    To: <span class="font-bold text-chocolate">{{ $req->department }}</span>
                                </p>
                                <p class="text-[10px] text-gray-400 mt-0.5">{{ $req->requisitionItems->count() }} items requested</p>
                            </li>
                        @empty
                            <li class="p-4 text-center text-xs text-gray-400 italic">Nothing to pick right now</li>
                        @endforelse
                    </ul>
                </div>
            </div>

        </div>
    </div>

    {{-- 3. QUICK STATS ROW --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-chocolate rounded-xl p-4 shadow-md flex items-center justify-between text-white relative overflow-hidden">
            <div class="absolute right-0 bottom-0 opacity-10 transform translate-x-2 translate-y-2">
                <i class="fas fa-coins text-6xl"></i>
            </div>
            <div class="relative z-10">
                <p class="text-[10px] uppercase tracking-widest opacity-80 font-bold">Inventory Value</p>
                <p class="font-display text-2xl font-bold mt-1">₱ {{ number_format($inventoryValue, 2) }}</p>
            </div>
        </div>

        <div class="bg-white border border-border-soft rounded-xl p-4 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[10px] text-gray-400 uppercase tracking-widest font-bold">Low Stock</p>
                <p class="font-display text-2xl font-bold text-chocolate mt-1">{{ $lowStockItemsCount }}</p>
            </div>
            <div class="w-10 h-10 rounded-full bg-amber-50 flex items-center justify-center text-amber-500">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>

        <div class="bg-white border border-border-soft rounded-xl p-4 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[10px] text-gray-400 uppercase tracking-widest font-bold">Active Items</p>
                <p class="font-display text-2xl font-bold text-chocolate mt-1">{{ $activeItemsCount }}</p>
            </div>
            <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-500">
                <i class="fas fa-boxes"></i>
            </div>
        </div>

        <div class="bg-white border border-border-soft rounded-xl p-4 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[10px] text-gray-400 uppercase tracking-widest font-bold">Movements (Today)</p>
                <p class="font-display text-2xl font-bold text-chocolate mt-1">{{ $todayMovementsCount }}</p>
            </div>
            <div class="w-10 h-10 rounded-full bg-green-50 flex items-center justify-center text-green-600">
                <i class="fas fa-exchange-alt"></i>
            </div>
        </div>
    </div>

</div>

<script>
// JavaScript logic preserved
function startPicking(requisitionId) {
    if (confirm('Start picking for this requisition?')) {
        fetch(`/inventory/requisitions/${requisitionId}/start-picking`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while starting picking');
        });
    }
}

function pickBatch(batchId) {
    if (confirm('Reserve this batch for FEFO priority usage?\n\nThis will:\n• Mark batch as FEFO priority\n• Notify production team\n• Create audit trail')) {
        
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = '...';
        button.disabled = true;
        
        fetch(`/inventory/batches/${batchId}/pick`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                button.textContent = 'Reserved';
                button.className = "text-xs font-bold text-green-600 bg-green-50 px-2 py-1 rounded border border-green-100";
                alert('✅ ' + data.message + '\n\n📢 Production team notified.');
                location.reload(); // Reload to update state properly
            } else {
                button.textContent = originalText;
                button.disabled = false;
                alert('❌ ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            button.textContent = originalText;
            button.disabled = false;
            alert('❌ An error occurred while reserving batch');
        });
    }
}
</script>

<style>
    /* Custom scrollbar for widgets */
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #e8dfd4; border-radius: 10px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: #c48d3f; }
</style>
@endsection