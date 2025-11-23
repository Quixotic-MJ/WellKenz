@extends('Inventory.layout.app')

@section('content')
<div class="space-y-6">
    
    {{-- 1. HEADER --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Warehouse Home</h1>
            <p class="text-sm text-gray-500">Overview for {{ date('F d, Y') }} • <span class="text-green-600 font-medium">Shift A (Morning)</span></p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('inventory.inbound.receive') }}" class="flex items-center justify-center px-4 py-2 bg-chocolate text-white rounded-lg hover:bg-chocolate-dark transition shadow-sm">
                <i class="fas fa-truck-loading mr-2"></i> Receive Delivery
            </a>
        </div>
    </div>

    {{-- 2. OPERATIONAL WIDGETS --}}
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        
        {{-- WIDGET 1: INCOMING DELIVERIES --}}
        <div class="lg:col-span-2">
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5 flex flex-col h-full">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Incoming Deliveries</h3>
                    <span class="bg-blue-100 text-blue-700 text-xs font-bold px-2 py-1 rounded-full">
                        {{ $pendingPurchaseOrders->count() }} Expected
                    </span>
                </div>
                
                <div class="flex-1 overflow-y-auto pr-1">
                    <div class="space-y-3">
                        @forelse($pendingPurchaseOrders as $po)
                        <div class="flex items-start p-3 bg-blue-50 border-l-4 border-blue-500 rounded">
                            <div class="flex-shrink-0 mr-3 text-center">
                                <span class="text-xs font-bold text-blue-800 block">
                                    {{ $po->expected_delivery_date ? \Carbon\Carbon::parse($po->expected_delivery_date)->format('h:i') : 'N/A' }}
                                </span>
                                <span class="text-[10px] text-blue-600 uppercase">
                                    {{ $po->expected_delivery_date ? \Carbon\Carbon::parse($po->expected_delivery_date)->format('A') : '' }}
                                </span>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-sm font-bold text-gray-900">{{ $po->supplier->name ?? 'Unknown Supplier' }}</h4>
                                <p class="text-xs text-gray-500">
                                    {{ $po->po_number }} • 
                                    {{ $po->purchaseOrderItems ? $po->purchaseOrderItems->count() : 0 }} Items
                                </p>
                            </div>
                            <div class="flex-shrink-0">
                                @php
                                    $statusColors = [
                                        'sent' => 'blue',
                                        'confirmed' => 'green', 
                                        'partial' => 'orange'
                                    ];
                                    $color = $statusColors[$po->status] ?? 'gray';
                                @endphp
                                <span class="text-[10px] bg-white border border-{{ $color }}-200 text-{{ $color }}-600 px-2 py-1 rounded capitalize">
                                    {{ $po->status }}
                                </span>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-4 text-gray-500">
                            <i class="fas fa-truck text-2xl mb-2 opacity-50"></i>
                            <p class="text-sm">No pending deliveries</p>
                        </div>
                        @endforelse
                    </div>
                </div>
                <div class="mt-4 text-center">
                    <a href="{{ route('inventory.purchase-orders.index') }}" class="text-xs font-bold text-blue-600 hover:underline">View All Purchase Orders</a>
                </div>
            </div>
        </div>

        {{-- WIDGET 2: EXPIRING SOON --}}
        <div class="lg:col-span-2">
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5 flex flex-col h-full border-t-4 border-t-red-500">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Expiring Soon</h3>
                        <p class="text-[10px] text-red-500 font-bold mt-0.5">Priority: FEFO (First Expired, First Out)</p>
                        <p class="text-[10px] text-gray-500 mt-1">
                            <i class="fas fa-bell text-amber-500 mr-1"></i>
                            Auto-notifications sent to production team
                        </p>
                    </div>
                    <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center text-red-600">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto pr-1">
                    <table class="min-w-full">
                        <tbody class="divide-y divide-gray-100">
                            @forelse($expiringBatches as $batch)
                            @php
                                $daysUntilExpiry = $batch->expiry_date ? \Carbon\Carbon::parse($batch->expiry_date)->diffInDays(now()) : 999;
                                $textColor = $daysUntilExpiry <= 1 ? 'text-red-600' : ($daysUntilExpiry <= 3 ? 'text-amber-600' : 'text-gray-600');
                                $statusText = $daysUntilExpiry <= 1 ? 'Today' : ($daysUntilExpiry . ' Days');
                                $badgeColor = $daysUntilExpiry <= 1 ? 'bg-red-100 text-red-800' : ($daysUntilExpiry <= 3 ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-800');
                                $isQuarantined = $batch->status === 'quarantine';
                            @endphp
                            <tr class="group hover:bg-gray-50">
                                <td class="py-2">
                                    <p class="text-xs font-bold text-gray-800">{{ $batch->item->name ?? 'Unknown Item' }}</p>
                                    <p class="text-[10px] text-gray-500">{{ $batch->batch_number }}</p>
                                    
                                    {{-- Show quarantine badge --}}
                                    @if($isQuarantined)
                                    <span class="text-[9px] bg-purple-100 text-purple-800 px-1.5 py-0.5 rounded-full mt-1 inline-block">
                                        <i class="fas fa-flag mr-1"></i>FEFO Priority
                                    </span>
                                    @endif
                                    
                                    @if($daysUntilExpiry <= 3 && !$isQuarantined)
                                    <span class="text-[9px] {{ $badgeColor }} px-1.5 py-0.5 rounded-full mt-1 inline-block">
                                        <i class="fas fa-bell mr-1"></i>Notified
                                    </span>
                                    @endif
                                </td>
                                <td class="py-2 text-right">
                                    <span class="text-xs font-bold {{ $textColor }} block">{{ $statusText }}</span>
                                    <span class="text-[10px] text-gray-400">{{ number_format($batch->quantity, 2) }} {{ $batch->item->unit->symbol ?? '' }} Left</span>
                                </td>
                                <td class="py-2 text-right pl-2">
                                    @if($isQuarantined)
                                        <span class="text-xs text-green-600 font-bold">✓ Reserved</span>
                                    @else
                                        <button onclick="pickBatch({{ $batch->id }})" 
                                                class="text-chocolate hover:text-chocolate-dark text-xs underline font-medium"
                                                title="Reserve for FEFO and notify production team">
                                            Reserve
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="py-4 text-center text-gray-500 text-sm">
                                    <i class="fas fa-check-circle text-green-400 text-lg mb-2"></i>
                                    <p>No items expiring soon</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                {{-- Notification summary --}}
                @if($expiringBatches->count() > 0)
                <div class="mt-4 pt-3 border-t border-gray-200">
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-gray-500">
                            <i class="fas fa-users mr-1"></i>
                            Notifying: Production Team
                        </span>
                        <span class="text-gray-500">
                            <i class="fas fa-bell mr-1"></i>
                            Auto-alerts: ≤3 days
                        </span>
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- WIDGET 3A: PENDING APPROVAL --}}
        <div class="lg:col-span-2">
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5 flex flex-col h-full border-t-4 border-t-blue-500">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Pending Approval</h3>
                    <span class="bg-blue-100 text-blue-800 text-xs font-bold px-2 py-1 rounded-full">
                        {{ $pendingApprovalRequisitions->count() }} Waiting
                    </span>
                </div>
                
                <div class="flex-1 space-y-3 overflow-y-auto pr-1">
                    @forelse($pendingApprovalRequisitions as $requisition)
                    <div class="p-3 border border-gray-200 rounded-lg hover:border-blue-300 transition cursor-pointer group">
                        <div class="flex justify-between items-start mb-1">
                            <span class="text-xs font-bold text-gray-900">{{ $requisition->requisition_number }}</span>
                            <span class="text-[10px] text-gray-400">
                                <i class="far fa-clock"></i> {{ $requisition->created_at->diffForHumans() }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-600">
                            From: <span class="font-medium">{{ $requisition->department }} ({{ $requisition->requestedBy->name ?? 'Unknown User' }})</span>
                        </p>
                        <div class="mt-2 flex items-center justify-between">
                            <span class="text-[10px] bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded">
                                {{ $requisition->requisitionItems->count() }} Items
                            </span>
                            <span class="text-[10px] text-blue-600 font-medium">Awaiting Approval</span>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4 text-gray-500">
                        <i class="fas fa-clipboard-check text-2xl mb-2 opacity-50"></i>
                        <p class="text-sm">No pending approvals</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- WIDGET 3B: READY FOR PICKING --}}
        <div class="lg:col-span-2">
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5 flex flex-col h-full border-t-4 border-t-amber-500">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Ready for Picking</h3>
                    <span class="bg-amber-100 text-amber-800 text-xs font-bold px-2 py-1 rounded-full">
                        {{ $readyForPickingRequisitions->count() }} To Pack
                    </span>
                </div>
                
                <div class="flex-1 space-y-3 overflow-y-auto pr-1">
                    @forelse($readyForPickingRequisitions as $requisition)
                    <div class="p-3 border border-gray-200 rounded-lg hover:border-amber-300 hover:shadow-md transition cursor-pointer group">
                        <div class="flex justify-between items-start mb-1">
                            <span class="text-xs font-bold text-gray-900">{{ $requisition->requisition_number }}</span>
                            <span class="text-[10px] text-gray-400">
                                <i class="far fa-clock"></i> {{ $requisition->created_at->diffForHumans() }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-600">
                            To: <span class="font-medium">{{ $requisition->department }} ({{ $requisition->requestedBy->name ?? 'Unknown User' }})</span>
                        </p>
                        <div class="mt-2 flex items-center justify-between">
                            <span class="text-[10px] bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded">
                                {{ $requisition->requisitionItems->count() }} Items
                            </span>
                            <button onclick="startPicking({{ $requisition->id }})" class="text-xs bg-amber-500 text-white px-2 py-1 rounded hover:bg-amber-600 transition">
                                Start Picking
                            </button>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4 text-gray-500">
                        <i class="fas fa-clipboard-list text-2xl mb-2 opacity-50"></i>
                        <p class="text-sm">No requisitions ready for picking</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

    {{-- 3. QUICK STATS ROW --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-chocolate text-white p-4 rounded-lg shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs opacity-75 uppercase">Inventory Value</p>
                <p class="text-lg font-bold">₱ {{ number_format($inventoryValue, 2) }}</p>
            </div>
            <i class="fas fa-coins text-2xl opacity-20"></i>
        </div>
        <div class="bg-white border border-gray-200 p-4 rounded-lg shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-400 uppercase font-bold">Low Stock Items</p>
                <p class="text-lg font-bold text-gray-800">{{ $lowStockItemsCount }}</p>
            </div>
            <i class="fas fa-exclamation-triangle text-amber-500 text-2xl"></i>
        </div>
        <div class="bg-white border border-gray-200 p-4 rounded-lg shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-400 uppercase font-bold">Active Items</p>
                <p class="text-lg font-bold text-gray-800">{{ $activeItemsCount }}</p>
            </div>
            <i class="fas fa-boxes text-gray-300 text-2xl"></i>
        </div>
        <div class="bg-white border border-gray-200 p-4 rounded-lg shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-400 uppercase font-bold">Today's Movements</p>
                <p class="text-lg font-bold text-gray-800">{{ $todayMovementsCount }}</p>
            </div>
            <i class="fas fa-exchange-alt text-gray-300 text-2xl"></i>
        </div>
    </div>

</div>

<script>
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
        button.textContent = 'Reserving...';
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
                button.textContent = '✓ Reserved';
                button.classList.remove('text-chocolate', 'hover:text-chocolate-dark', 'underline');
                button.classList.add('text-green-600', 'font-bold');
                
                // Show success message with production notification info
                setTimeout(() => {
                    alert('✅ ' + data.message + '\n\n📢 Production team has been notified to prioritize this batch.');
                    location.reload();
                }, 500);
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
@endsection