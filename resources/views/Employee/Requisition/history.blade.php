@extends('Employee.layout.app')

@php
    use Illuminate\Support\Str;
@endphp

@section('content')
<div class="space-y-8 font-sans text-gray-600">

    {{-- Success Notification --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 shadow-sm animate-fade-in-down">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-bold text-green-800">{{ session('success') }}</p>
                    @if(session('new_requisition'))
                        <p class="text-xs text-green-600 mt-1">Your new requisition (#{{ session('new_requisition') }}) is now listed below.</p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <h1 class="font-display text-3xl font-bold text-chocolate mb-2">My Request History</h1>
            <p class="text-sm text-gray-500">Track the status and details of your ingredient requisitions.</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.location.reload()" class="inline-flex items-center justify-center px-4 py-2.5 bg-white border border-border-soft text-gray-600 text-sm font-bold rounded-lg hover:bg-cream-bg hover:text-chocolate transition-all shadow-sm group">
                <i class="fas fa-sync-alt mr-2 group-hover:rotate-180 transition-transform duration-500"></i> Refresh
            </button>
            <a href="{{ route('employee.requisitions.create') }}" class="inline-flex items-center justify-center px-5 py-2.5 bg-chocolate text-white text-sm font-bold rounded-lg hover:bg-chocolate-dark transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                <i class="fas fa-plus mr-2"></i> New Request
            </a>
        </div>
    </div>

    {{-- 2. FILTERS --}}
    <div class="bg-white border border-border-soft rounded-xl p-6 shadow-sm">
        <form method="GET" action="{{ route('employee.requisitions.history') }}" class="flex flex-col md:flex-row items-center gap-4 w-full">
            <div class="relative w-full md:flex-1 group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400 group-focus-within:text-caramel transition-colors"></i>
                </div>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ request('search') }}" 
                    class="block w-full pl-11 pr-4 py-2.5 border border-gray-200 rounded-lg bg-cream-bg placeholder-gray-400 text-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all" 
                    placeholder="Search by Item Name or Request ID..."
                >
            </div>

            <div class="flex items-center gap-3 w-full md:w-auto relative">
                <select 
                    name="status" 
                    class="block w-full md:w-48 py-2.5 px-3 border border-gray-200 bg-white rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm appearance-none cursor-pointer"
                    onchange="this.form.submit()"
                >
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="fulfilled" {{ request('status') == 'fulfilled' ? 'selected' : '' }}>Fulfilled</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                    <i class="fas fa-chevron-down text-xs"></i>
                </div>
            </div>
            
            @if(request('search') || request('status'))
                <a href="{{ route('employee.requisitions.history') }}" class="text-sm font-bold text-chocolate hover:text-caramel hover:underline decoration-chocolate/30 whitespace-nowrap px-2">
                    Clear Filters
                </a>
            @endif
        </form>
    </div>

    {{-- 3. HISTORY TABLE --}}
    <div class="bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-border-soft">
                <thead class="bg-cream-bg">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Request ID</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Date Submitted</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Items Summary</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Status</th>
                        <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-caramel uppercase tracking-widest font-display">Actions</th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-caramel uppercase tracking-widest font-display">Notes</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-border-soft">
                    @forelse($requisitions as $requisition)
                        @php
                            $statusConfig = [
                                'pending' => ['class' => 'bg-amber-50 text-amber-700 border-amber-200', 'icon' => 'fa-clock', 'label' => 'Pending'],
                                'approved' => ['class' => 'bg-green-50 text-green-700 border-green-200', 'icon' => 'fa-check-circle', 'label' => 'Approved'],
                                'rejected' => ['class' => 'bg-red-50 text-red-700 border-red-200', 'icon' => 'fa-times-circle', 'label' => 'Rejected'],
                                'fulfilled' => ['class' => 'bg-gray-100 text-gray-600 border-gray-200', 'icon' => 'fa-box-open', 'label' => 'Fulfilled']
                            ];
                            
                            $status = $statusConfig[$requisition->status] ?? $statusConfig['pending'];
                            
                            // Format date nicely
                            $requestDate = \Carbon\Carbon::parse($requisition->request_date);
                            $now = \Carbon\Carbon::now();
                            
                            if ($requestDate->isToday()) {
                                $dateDisplay = 'Today, ' . $requestDate->format('h:i A');
                            } elseif ($requestDate->isYesterday()) {
                                $dateDisplay = 'Yesterday, ' . $requestDate->format('h:i A');
                            } else {
                                $dateDisplay = $requestDate->format('M j, Y') . ($requestDate->year == $now->year ? '' : ', ' . $requestDate->format('Y'));
                            }
                            
                            // Get items summary
                            $items = $requisition->requisitionItems ?? collect();
                            $itemNames = $items->take(2)->map(function($item) {
                                return $item->item->name ?? 'Unknown Item';
                            })->implode(', ');
                            $totalItems = $items->count();
                            
                            // Get quantities for display
                            $quantities = $items->take(2)->map(function($item) {
                                return number_format($item->quantity_requested, 0) . ' ' . ($item->item->unit->symbol ?? 'units');
                            })->implode(', ');
                        @endphp
                        
                        <tr class="group hover:bg-cream-bg transition-colors {{ $requisition->status === 'rejected' ? 'bg-red-50/20' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-mono text-sm font-bold text-chocolate bg-chocolate/5 px-2 py-1 rounded">
                                    #{{ $requisition->requisition_number ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $dateDisplay }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-bold text-chocolate">
                                    @if($totalItems > 0)
                                        {{ $itemNames }}
                                        @if($totalItems > 2)
                                            <span class="text-gray-400 font-normal text-xs ml-1">+{{ $totalItems - 2 }} more</span>
                                        @endif
                                    @else
                                        <span class="text-gray-400 italic">No items specified</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500 mt-0.5">
                                    @if($totalItems == 1)
                                        Requested: {{ $quantities }}
                                    @else
                                        Total: {{ $totalItems }} {{ Str::plural('Item', $totalItems) }}
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wide border {{ $status['class'] }}">
                                    <i class="fas {{ $status['icon'] }} mr-1.5"></i> {{ $status['label'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <button onclick="viewRequisitionDetails({{ $requisition->id }})" 
                                        class="text-gray-400 hover:text-caramel bg-white hover:bg-white p-2 rounded-lg border border-transparent hover:border-border-soft transition-all shadow-sm hover:shadow-md tooltip"
                                        title="View Details">
                                    <i class="fas fa-eye text-lg"></i>
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                @if($requisition->status === 'pending')
                                    <span class="text-gray-400 italic text-xs">Awaiting review...</span>
                                @elseif($requisition->status === 'approved')
                                    <span class="text-green-600 font-medium flex items-center justify-end text-xs">
                                        <i class="fas fa-box-open mr-1"></i> Ready for Pickup
                                    </span>
                                @elseif($requisition->status === 'rejected')
                                    @if($requisition->notes)
                                        <button onclick="showRejectionReason('{{ addslashes($requisition->notes) }}', '{{ $requisition->approvedBy->name ?? 'Supervisor' }}')" 
                                                class="text-red-600 hover:text-red-800 font-bold text-xs underline decoration-red-300 underline-offset-2 flex items-center justify-end ml-auto">
                                            <i class="fas fa-info-circle mr-1"></i> View Reason
                                        </button>
                                    @else
                                        <span class="text-red-500 text-xs">Declined</span>
                                    @endif
                                @elseif($requisition->status === 'fulfilled')
                                    <span class="text-gray-400 flex items-center justify-end text-xs">
                                        <i class="fas fa-check-double mr-1"></i> Received
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-cream-bg rounded-full flex items-center justify-center mb-4 border border-border-soft">
                                        <i class="fas fa-clipboard-list text-chocolate/30 text-3xl"></i>
                                    </div>
                                    <h3 class="font-display text-lg font-bold text-chocolate">No Requisitions Found</h3>
                                    <p class="text-sm text-gray-400 mt-1 max-w-xs mx-auto">Your requisition history will appear here once you submit requests.</p>
                                    <a href="{{ route('employee.requisitions.create') }}" class="mt-4 text-caramel font-bold text-sm hover:text-chocolate hover:underline decoration-caramel/30 underline-offset-2">Create First Request</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        @if(method_exists($requisitions, 'hasPages') && $requisitions->hasPages())
            <div class="bg-white px-6 py-4 border-t border-border-soft">
                {{ $requisitions->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

</div>

<div id="rejectionModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity backdrop-blur-sm" onclick="document.getElementById('rejectionModal').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-border-soft">
            <div class="bg-white px-6 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i class="fas fa-comment-alt text-red-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-bold text-chocolate font-display" id="modal-title">Rejection Details</h3>
                        <div class="mt-4 bg-red-50 p-4 rounded-lg border border-red-100">
                            <p class="text-sm text-gray-800 italic" id="reasonText">"..."</p>
                        </div>
                        <p class="mt-3 text-xs text-gray-500 text-right font-medium">Reviewed by: <span id="rejectorName" class="text-chocolate">Supervisor</span></p>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-3 sm:flex sm:flex-row-reverse border-t border-gray-100">
                <button type="button" onclick="document.getElementById('rejectionModal').classList.add('hidden')" class="w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-bold text-gray-700 hover:bg-cream-bg hover:text-chocolate focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-all">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<div id="requisitionDetailsModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity backdrop-blur-sm" onclick="document.getElementById('requisitionDetailsModal').classList.add('hidden')"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-border-soft">
            
            <div class="bg-chocolate px-6 py-4 flex justify-between items-center">
                <h3 class="font-display text-lg font-bold text-white" id="requisitionModalTitle">Requisition Details</h3>
                <button type="button" onclick="document.getElementById('requisitionDetailsModal').classList.add('hidden')" class="text-white/70 hover:text-white transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            
            <div class="px-6 py-6 max-h-[70vh] overflow-y-auto custom-scrollbar">
                
                <div id="detailsLoading" class="flex flex-col items-center justify-center py-12">
                    <div class="animate-spin rounded-full h-8 w-8 border-[3px] border-border-soft border-t-caramel"></div>
                    <span class="mt-3 text-sm font-bold text-chocolate">Loading details...</span>
                </div>
                
                <div id="detailsContent" class="hidden space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-cream-bg p-3 rounded-lg border border-border-soft">
                            <p class="text-[10px] font-bold text-caramel uppercase tracking-widest">Requisition ID</p>
                            <p class="text-sm font-mono font-bold text-chocolate mt-0.5" id="detailRequisitionNumber">-</p>
                        </div>
                        <div class="bg-cream-bg p-3 rounded-lg border border-border-soft">
                            <p class="text-[10px] font-bold text-caramel uppercase tracking-widest">Status</p>
                            <div class="mt-0.5" id="detailStatus">-</div>
                        </div>
                        <div class="bg-cream-bg p-3 rounded-lg border border-border-soft">
                            <p class="text-[10px] font-bold text-caramel uppercase tracking-widest">Date Submitted</p>
                            <p class="text-sm font-medium text-gray-700 mt-0.5" id="detailDate">-</p>
                        </div>
                        <div class="bg-cream-bg p-3 rounded-lg border border-border-soft">
                            <p class="text-[10px] font-bold text-caramel uppercase tracking-widest">Department</p>
                            <p class="text-sm font-medium text-gray-700 mt-0.5" id="detailDepartment">-</p>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-bold text-chocolate uppercase tracking-wide mb-2 flex items-center">
                            <i class="fas fa-sticky-note mr-1.5 text-caramel"></i> Purpose / Notes
                        </p>
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-100 text-sm text-gray-700 leading-relaxed italic" id="detailPurpose">
                            -
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-bold text-chocolate uppercase tracking-wide mb-3 flex items-center">
                            <i class="fas fa-list mr-1.5 text-caramel"></i> Items Requested
                        </p>
                        <div class="border border-border-soft rounded-lg overflow-hidden">
                            <table class="min-w-full divide-y divide-border-soft">
                                <thead class="bg-cream-bg">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-caramel uppercase tracking-wider">Item</th>
                                        <th class="px-4 py-3 text-left text-xs font-bold text-caramel uppercase tracking-wider">Qty</th>
                                        <th class="px-4 py-3 text-right text-xs font-bold text-caramel uppercase tracking-wider">Est. Value</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-100" id="detailItemsTable">
                                    </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td colspan="2" class="px-4 py-3 text-right text-xs font-bold text-gray-500 uppercase tracking-wide">Total Estimated Value</td>
                                        <td class="px-4 py-3 text-right text-sm font-bold text-chocolate" id="detailTotal">₱0.00</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse border-t border-gray-200">
                <button type="button" onclick="document.getElementById('requisitionDetailsModal').classList.add('hidden')" class="w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-bold text-gray-700 hover:bg-cream-bg hover:text-chocolate focus:outline-none sm:w-auto sm:text-sm transition-all">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function showRejectionReason(reason, user) {
        document.getElementById('reasonText').innerText = `"${reason}"`;
        document.getElementById('rejectorName').innerText = user;
        document.getElementById('rejectionModal').classList.remove('hidden');
    }

    function viewRequisitionDetails(requisitionId) {
        const modal = document.getElementById('requisitionDetailsModal');
        const loading = document.getElementById('detailsLoading');
        const content = document.getElementById('detailsContent');
        
        // Show modal and loading state
        modal.classList.remove('hidden');
        loading.classList.remove('hidden');
        content.classList.add('hidden');
        
        // Fetch requisition details via AJAX
        fetch(`/employee/requisitions/${requisitionId}/details`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    populateRequisitionDetails(data.requisition);
                } else {
                    alert('Failed to load requisition details.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading requisition details.');
            })
            .finally(() => {
                loading.classList.add('hidden');
                content.classList.remove('hidden');
            });
    }
    
    function populateRequisitionDetails(requisition) {
        // Format status with badge
        const statusConfig = {
            'pending': { label: 'Pending', class: 'bg-amber-100 text-amber-800' },
            'approved': { label: 'Approved', class: 'bg-green-100 text-green-800' },
            'rejected': { label: 'Rejected', class: 'bg-red-100 text-red-800' },
            'fulfilled': { label: 'Fulfilled', class: 'bg-gray-100 text-gray-600' }
        };
        
        const status = statusConfig[requisition.status] || statusConfig['pending'];
        
        // Populate header info
        document.getElementById('detailRequisitionNumber').textContent = `#${requisition.requisition_number}`;
        document.getElementById('detailStatus').innerHTML = `<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wide ${status.class}">
            ${status.label}
        </span>`;
        
        const dateObj = new Date(requisition.request_date);
        document.getElementById('detailDate').textContent = dateObj.toLocaleDateString('en-US', {
            year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit'
        });
        
        document.getElementById('detailDepartment').textContent = requisition.department || 'N/A';
        document.getElementById('detailPurpose').textContent = requisition.purpose || 'No purpose specified.';
        
        // Populate items table
        const itemsTable = document.getElementById('detailItemsTable');
        itemsTable.innerHTML = '';
        
        if (requisition.requisition_items && requisition.requisition_items.length > 0) {
            requisition.requisition_items.forEach(item => {
                const row = document.createElement('tr');
                row.className = "hover:bg-gray-50 transition-colors";
                row.innerHTML = `
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="text-sm font-bold text-gray-800">${item.item.name}</div>
                        <div class="text-xs text-gray-400 font-mono mt-0.5">${item.item.item_code}</div>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">
                        <span class="font-bold">${parseFloat(item.quantity_requested)}</span> 
                        <span class="text-xs text-gray-500">${item.item.unit.symbol}</span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium text-chocolate">
                        ₱${parseFloat(item.total_estimated_value).toFixed(2)}
                    </td>
                `;
                itemsTable.appendChild(row);
            });
        } else {
            itemsTable.innerHTML = '<tr><td colspan="3" class="px-4 py-8 text-center text-gray-500 italic">No items found</td></tr>';
        }
        
        // Populate total
        document.getElementById('detailTotal').textContent = `₱${parseFloat(requisition.total_estimated_value || 0).toFixed(2)}`;
    }
</script>

@endsection