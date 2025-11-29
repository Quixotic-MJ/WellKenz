@extends('Supervisor.layout.app')

@section('content')
<div class="space-y-8 font-sans text-gray-600">

    {{-- 1. HEADER & ACTIONS --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <h1 class="font-display text-3xl font-bold text-chocolate mb-1">Expiry Monitoring</h1>
            <p class="text-sm text-gray-500">Track expiring batches to minimize waste and prioritize usage.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('supervisor.reports.export_use_first_list_pdf') }}" 
               class="inline-flex items-center justify-center px-4 py-2.5 bg-white border border-border-soft text-gray-600 text-sm font-bold rounded-lg hover:bg-cream-bg hover:text-chocolate transition-all shadow-sm group">
                <i class="fas fa-file-pdf mr-2 opacity-70 group-hover:opacity-100 text-red-500"></i> Download "Use First" PDF
            </a>
            <button onclick="alertBakers()" 
                    id="alertBtn"
                    class="inline-flex items-center justify-center px-5 py-2.5 bg-chocolate text-white text-sm font-bold rounded-lg hover:bg-chocolate-dark transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                <i class="fas fa-bullhorn mr-2"></i> Alert Bakers
            </button>
        </div>
    </div>

    {{-- 2. RISK SUMMARY --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Critical (Today/Tomorrow) -->
        <div class="bg-white border border-border-soft border-l-4 border-l-red-500 rounded-xl p-6 shadow-sm flex flex-col justify-between group hover:shadow-md transition-all relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-red-50 rounded-bl-full -mr-6 -mt-6 transition-transform group-hover:scale-110"></div>
            
            <div class="flex justify-between items-start relative z-10">
                <div>
                    <p class="text-xs font-bold text-red-600 uppercase tracking-widest">Critical Action</p>
                    <p class="text-3xl font-display font-bold text-gray-900 mt-2">{{ $summary['critical_count'] }}</p>
                    <p class="text-sm text-gray-500 font-medium">Batches expiring ≤ 48h</p>
                </div>
                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center text-red-600 shadow-sm">
                    <i class="fas fa-hourglass-end text-lg"></i>
                </div>
            </div>
            
            @if($summary['critical_count'] > 0)
                <div class="mt-4 pt-3 border-t border-gray-100 relative z-10">
                    <p class="text-xs font-bold text-red-600 flex items-center">
                        <i class="fas fa-coins mr-1.5"></i> ₱{{ number_format($summary['critical_value'], 2) }} at risk
                    </p>
                </div>
            @endif
        </div>

        <!-- Warning (Next 7 Days) -->
        <div class="bg-white border border-border-soft border-l-4 border-l-amber-400 rounded-xl p-6 shadow-sm flex flex-col justify-between group hover:shadow-md transition-all relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-amber-50 rounded-bl-full -mr-6 -mt-6 transition-transform group-hover:scale-110"></div>
            
            <div class="flex justify-between items-start relative z-10">
                <div>
                    <p class="text-xs font-bold text-amber-600 uppercase tracking-widest">Warning (7 Days)</p>
                    <p class="text-3xl font-display font-bold text-gray-900 mt-2">{{ $summary['warning_count'] }}</p>
                    <p class="text-sm text-gray-500 font-medium">Plan into production</p>
                </div>
                <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center text-amber-600 shadow-sm">
                    <i class="fas fa-calendar-week text-lg"></i>
                </div>
            </div>

            @if($summary['warning_count'] > 0)
                <div class="mt-4 pt-3 border-t border-gray-100 relative z-10">
                    <p class="text-xs font-bold text-amber-600 flex items-center">
                        <i class="fas fa-coins mr-1.5"></i> ₱{{ number_format($summary['warning_value'], 2) }} potential loss
                    </p>
                </div>
            @endif
        </div>

        <!-- Total Value at Risk -->
        <div class="bg-white border border-border-soft rounded-xl p-6 shadow-sm flex flex-col justify-between group hover:border-chocolate/30 transition-all relative overflow-hidden">
            <div class="absolute top-0 right-0 w-20 h-20 bg-chocolate/5 rounded-bl-full -mr-6 -mt-6 transition-transform group-hover:scale-110"></div>
            
            <div class="flex justify-between items-start relative z-10">
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Total Risk Value</p>
                    <p class="text-3xl font-display font-bold text-chocolate mt-2">{{ $summary['formatted_total_value'] }}</p>
                    <p class="text-sm text-gray-500 font-medium">Cumulative inventory exposure</p>
                </div>
                <div class="w-10 h-10 bg-cream-bg border border-border-soft rounded-lg flex items-center justify-center text-chocolate shadow-sm">
                    <i class="fas fa-chart-pie text-lg"></i>
                </div>
            </div>

            @if($summary['expired_count'] > 0)
                <div class="mt-4 pt-3 border-t border-gray-100 relative z-10">
                    <p class="text-xs font-bold text-gray-400 flex items-center">
                        <span class="w-2 h-2 rounded-full bg-gray-400 mr-2"></span>
                        {{ $summary['expired_count'] }} items already expired
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{-- 3. EXPIRY TABLE --}}
    <div class="bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden">
        
        <!-- Toolbar -->
        <div class="p-5 border-b border-border-soft bg-white flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-3 w-full md:w-auto">
                <span class="text-xs font-bold text-chocolate uppercase tracking-wide">Timeframe:</span>
                <select class="block w-full md:w-48 py-2 px-3 border border-gray-200 bg-cream-bg rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel cursor-pointer font-medium text-gray-700 transition-all" 
                        onchange="updateFilter(this.value)">
                    <option value="7days" {{ $filter == '7days' ? 'selected' : '' }}>Next 7 Days</option>
                    <option value="30days" {{ $filter == '30days' ? 'selected' : '' }}>Next 30 Days</option>
                    <option value="expired" {{ $filter == 'expired' ? 'selected' : '' }}>Already Expired</option>
                </select>
            </div>
            
            <form method="GET" action="{{ route('supervisor.reports.expiry') }}" class="w-full md:w-auto flex gap-2">
                <div class="relative flex-1 md:w-64 group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400 group-focus-within:text-caramel transition-colors text-xs"></i>
                    </div>
                    <input type="text" 
                           name="search" 
                           value="{{ $search }}" 
                           class="block w-full pl-9 pr-3 py-2 border border-gray-200 bg-white rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all placeholder-gray-400" 
                           placeholder="Filter by item name...">
                    <input type="hidden" name="filter" value="{{ $filter }}">
                </div>
                <button type="submit" class="px-4 py-2 bg-chocolate text-white text-sm font-bold rounded-lg hover:bg-chocolate-dark transition-colors shadow-sm">
                    Filter
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-border-soft">
                <thead class="bg-cream-bg">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Item / Batch Info</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Expiry Date</th>
                        <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-caramel uppercase tracking-widest font-display">Countdown</th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-caramel uppercase tracking-widest font-display">Remaining Qty</th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-caramel uppercase tracking-widest font-display">Value</th>
                        <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-caramel uppercase tracking-widest font-display">Status</th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-caramel uppercase tracking-widest font-display">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    
                    @forelse($expiringBatches as $batch)
                        @php
                            // Recalculate logic for display purposes within template
                            $now = \Carbon\Carbon::now();
                            $expiryDate = \Carbon\Carbon::parse($batch->expiry_date);
                            $daysUntilExpiry = $now->diffInDays($expiryDate, false);
                            $isPastExpiry = $expiryDate->isPast();
                            
                            $countdownText = '';
                            $countdownClass = '';
                            $rowClass = 'hover:bg-cream-bg/50 transition-colors group';
                            $statusBadge = '';
                            $urgentAction = false;
                            
                            if ($isPastExpiry) {
                                $countdownText = 'EXPIRED';
                                $countdownClass = 'bg-gray-100 text-gray-500 border border-gray-200';
                                $rowClass = 'bg-gray-50 hover:bg-gray-100 transition-colors opacity-75';
                                $statusBadge = '<span class="text-xs font-bold text-gray-500 uppercase tracking-wide">Expired</span>';
                            } elseif ($daysUntilExpiry <= 1) {
                                $countdownText = $daysUntilExpiry <= 0 ? 'EXPIRES TODAY' : '1 Day Left';
                                $countdownClass = 'bg-red-600 text-white shadow-sm animate-pulse';
                                $rowClass = 'bg-red-50/40 hover:bg-red-50 transition-colors border-l-4 border-l-red-500';
                                $statusBadge = '<span class="text-xs font-bold text-red-600 uppercase tracking-wide">Critical</span>';
                                $urgentAction = true;
                            } elseif ($daysUntilExpiry <= 3) {
                                $countdownText = $daysUntilExpiry . ' Days Left';
                                $countdownClass = 'bg-red-100 text-red-800 border border-red-200';
                                $rowClass = 'bg-red-50/20 hover:bg-red-50 transition-colors';
                                $statusBadge = '<span class="text-xs font-bold text-red-600 uppercase tracking-wide">High Priority</span>';
                                $urgentAction = true;
                            } elseif ($daysUntilExpiry <= 7) {
                                $countdownText = $daysUntilExpiry . ' Days Left';
                                $countdownClass = 'bg-amber-100 text-amber-800 border border-amber-200';
                                $statusBadge = '<span class="text-xs font-bold text-amber-600 uppercase tracking-wide">Warning</span>';
                            } else {
                                $countdownText = $daysUntilExpiry . ' Days Left';
                                $countdownClass = 'bg-white text-gray-600 border border-gray-200';
                                $statusBadge = '<span class="text-xs font-bold text-green-600 uppercase tracking-wide">Monitor</span>';
                            }
                        @endphp
                    
                        <tr class="{{ $rowClass }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 {{ $urgentAction ? 'bg-red-50 border-red-200 text-red-500' : 'bg-white border-border-soft text-gray-400' }} rounded-lg border flex items-center justify-center shadow-sm">
                                        @if($urgentAction)
                                            <i class="fas fa-exclamation-triangle"></i>
                                        @else
                                            <i class="fas fa-box"></i>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-bold text-gray-900">{{ $batch->item->name ?? 'Unknown Item' }}</div>
                                        <div class="text-xs text-gray-500 mt-0.5 font-mono">Batch #{{ $batch->batch_number }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm {{ $urgentAction ? 'text-red-700 font-bold' : 'text-gray-900' }}">
                                {{ \Carbon\Carbon::parse($batch->expiry_date)->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="px-2.5 py-1 text-[10px] font-bold rounded-md {{ $countdownClass }} inline-block min-w-[80px] text-center">
                                    {{ $countdownText }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm font-bold text-gray-900">{{ number_format($batch->quantity, 1) }} {{ $batch->item->unit->symbol ?? '' }}</div>
                                <div class="text-[10px] text-gray-400">@ ₱{{ number_format($batch->unit_cost, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm font-bold text-chocolate">₱{{ number_format($batch->quantity * $batch->unit_cost, 2) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                {!! $statusBadge !!}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                @if($urgentAction)
                                    <button class="inline-flex items-center justify-center px-3 py-1.5 bg-red-600 text-white text-xs font-bold rounded-lg hover:bg-red-700 transition-all shadow-sm">
                                        Use Now
                                    </button>
                                @else
                                    <button onclick="viewBatchDetails({{ $batch->id }})" class="text-gray-400 hover:text-chocolate hover:bg-cream-bg p-2 rounded-lg transition-all" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-cream-bg rounded-full flex items-center justify-center mb-4 border border-border-soft shadow-inner">
                                        <i class="fas fa-shield-alt text-chocolate/30 text-3xl"></i>
                                    </div>
                                    <h3 class="font-display text-lg font-bold text-chocolate mb-1">Safe Zone</h3>
                                    <p class="text-sm text-gray-500">
                                        @if($search)
                                            No batches match your search for "{{ $search }}"
                                        @else
                                            No batches found matching the current expiry filter.
                                        @endif
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        @if($expiringBatches->hasPages())
            <div class="bg-white px-6 py-4 border-t border-border-soft">
                {{ $expiringBatches->appends(['filter' => $filter, 'search' => $search])->links() }}
            </div>
        @endif
        
        <!-- Footer Note -->
        <div class="bg-gray-50 px-6 py-3 border-t border-gray-200">
            <p class="text-[10px] text-gray-400 text-center uppercase tracking-wide font-medium">
                <i class="fas fa-info-circle mr-1"></i> FIFO Policy: Prioritize using batches marked as "Critical" or "Warning" first.
            </p>
        </div>
    </div>

</div>

<!-- Confirmation Modal -->
<div id="confirmModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-96 lg:w-1/3 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                <h3 class="text-lg font-bold text-gray-900" id="confirmTitle">Confirm Action</h3>
                <button onclick="closeConfirmModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="mt-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-amber-500 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-gray-700" id="confirmMessage">Are you sure you want to proceed?</p>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                <button onclick="closeConfirmModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                    Cancel
                </button>
                <button onclick="confirmAction()" id="confirmButton" class="px-4 py-2 bg-chocolate text-white rounded-lg hover:bg-chocolate-dark transition-colors">
                    Confirm
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Notification Modal -->
<div id="notificationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-96 lg:w-1/3 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                <h3 class="text-lg font-bold text-gray-900" id="notificationTitle">Notification</h3>
                <button onclick="closeNotificationModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="mt-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0" id="notificationIcon">
                        <i class="fas fa-info-circle text-blue-500 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-gray-700" id="notificationMessage">Message</p>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end pt-4 border-t border-gray-200">
                <button onclick="closeNotificationModal()" class="px-4 py-2 bg-chocolate text-white rounded-lg hover:bg-chocolate-dark transition-colors">
                    OK
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Batch Details Modal -->
<div id="batchDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-3 border-b border-gray-200">
                <h3 class="text-lg font-bold text-gray-900" id="modalTitle">Batch Details</h3>
                <button onclick="closeBatchDetailsModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="mt-4" id="modalContent">
                <div class="animate-pulse">
                    <div class="h-4 bg-gray-200 rounded w-3/4 mb-4"></div>
                    <div class="h-4 bg-gray-200 rounded w-1/2 mb-4"></div>
                    <div class="h-4 bg-gray-200 rounded w-2/3"></div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end pt-4 border-t border-gray-200">
                <button onclick="closeBatchDetailsModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Batch Details Modal Functions
function viewBatchDetails(batchId) {
    const modal = document.getElementById('batchDetailsModal');
    const modalContent = document.getElementById('modalContent');

    // Show modal with loading state
    modal.classList.remove('hidden');
    modalContent.innerHTML = `
        <div class="animate-pulse">
            <div class="h-4 bg-gray-200 rounded w-3/4 mb-4"></div>
            <div class="h-4 bg-gray-200 rounded w-1/2 mb-4"></div>
            <div class="h-4 bg-gray-200 rounded w-2/3"></div>
        </div>
    `;

    // Fetch batch details
    fetch(`{{ url('supervisor/reports/batch') }}/${batchId}/details`, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const batch = data.data;
            modalContent.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Basic Information -->
                    <div>
                        <h4 class="font-bold text-gray-900 mb-3">Basic Information</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Batch Number:</span>
                                <span class="font-medium">${batch.batch_number}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Item:</span>
                                <span class="font-medium">${batch.item.name} (${batch.item.item_code})</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Supplier:</span>
                                <span class="font-medium">${batch.supplier.name}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Status:</span>
                                <span class="font-medium capitalize">${batch.status}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quantity & Value -->
                    <div>
                        <h4 class="font-bold text-gray-900 mb-3">Quantity & Value</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Quantity:</span>
                                <span class="font-medium">${batch.quantity} ${batch.item.unit_symbol}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Unit Cost:</span>
                                <span class="font-medium">₱${batch.unit_cost}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Total Value:</span>
                                <span class="font-medium text-chocolate">₱${batch.total_value}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Dates -->
                    <div>
                        <h4 class="font-bold text-gray-900 mb-3">Dates</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Manufacturing:</span>
                                <span class="font-medium">${batch.manufacturing_date}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Expiry:</span>
                                <span class="font-medium ${batch.is_expired ? 'text-red-600' : ''}">${batch.expiry_date}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Days Until Expiry:</span>
                                <span class="font-medium ${batch.days_until_expiry <= 7 ? 'text-red-600' : ''}">${batch.days_until_expiry} days</span>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Info -->
                    <div>
                        <h4 class="font-bold text-gray-900 mb-3">Additional Information</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Location:</span>
                                <span class="font-medium">${batch.location}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Priority:</span>
                                <span class="font-medium ${batch.priority_class}">${batch.priority}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Created:</span>
                                <span class="font-medium">${batch.created_at}</span>
                            </div>
                        </div>
                    </div>
                </div>

                ${batch.notes ? `
                    <div class="mt-6 pt-4 border-t border-gray-200">
                        <h4 class="font-bold text-gray-900 mb-2">Notes</h4>
                        <p class="text-gray-700">${batch.notes}</p>
                    </div>
                ` : ''}
            `;
        } else {
            modalContent.innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-exclamation-triangle text-red-500 text-3xl mb-4"></i>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Error Loading Details</h3>
                    <p class="text-gray-600">${data.message || 'Unable to load batch details. Please try again.'}</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        modalContent.innerHTML = `
            <div class="text-center py-8">
                <i class="fas fa-exclamation-triangle text-red-500 text-3xl mb-4"></i>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Connection Error</h3>
                <p class="text-gray-600">Unable to connect to the server. Please check your connection and try again.</p>
            </div>
        `;
    });
}

function closeBatchDetailsModal() {
    const modal = document.getElementById('batchDetailsModal');
    modal.classList.add('hidden');
}

function updateFilter(filter) {
    const url = new URL(window.location);
    url.searchParams.set('filter', filter);
    window.location.href = url.toString();
}

// Alert Bakers functionality
let alertCallback = null;

function alertBakers() {
    showConfirmModal(
        'Send Alerts',
        'Send expiry alerts to all bakers? This will notify them about items expiring soon.',
        'Send Alerts',
        function() {
            proceedWithAlerts();
        }
    );
}

function proceedWithAlerts() {
    
    const button = document.getElementById('alertBtn');
    const originalHTML = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sending...';
    button.classList.add('opacity-75', 'cursor-not-allowed');
    
    fetch('{{ route("supervisor.reports.alert_bakers") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotificationModal('Success', data.message, 'success');
        } else {
            showNotificationModal('Error', 'Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotificationModal('Connection Error', 'An error occurred while sending alerts. Please try again.', 'error');
    })
    .finally(() => {
        button.disabled = false;
        button.innerHTML = originalHTML;
        button.classList.remove('opacity-75', 'cursor-not-allowed');
    });
}

// Auto-refresh every 5 minutes
setInterval(function() {
    if (document.hidden) return; 
    
    const currentTime = new Date();
    const lastUpdate = localStorage.getItem('lastExpiryRefresh');
    
    if (!lastUpdate || (currentTime.getTime() - parseInt(lastUpdate)) > 300000) { 
        localStorage.setItem('lastExpiryRefresh', currentTime.getTime());
        window.location.reload();
    }
}, 60000);

// Modal Control Functions
function showConfirmModal(title, message, confirmText, callback) {
    const modal = document.getElementById('confirmModal');
    const titleElement = document.getElementById('confirmTitle');
    const messageElement = document.getElementById('confirmMessage');
    const confirmButton = document.getElementById('confirmButton');
    
    titleElement.textContent = title;
    messageElement.textContent = message;
    confirmButton.textContent = confirmText;
    alertCallback = callback;
    
    modal.classList.remove('hidden');
}

function closeConfirmModal() {
    const modal = document.getElementById('confirmModal');
    modal.classList.add('hidden');
    alertCallback = null;
}

function confirmAction() {
    if (alertCallback) {
        alertCallback();
    }
    closeConfirmModal();
}

function showNotificationModal(title, message, type = 'info') {
    const modal = document.getElementById('notificationModal');
    const titleElement = document.getElementById('notificationTitle');
    const messageElement = document.getElementById('notificationMessage');
    const iconElement = document.getElementById('notificationIcon');
    
    titleElement.textContent = title;
    messageElement.textContent = message;
    
    // Update icon based on type
    let iconClass = 'fas fa-info-circle text-blue-500 text-xl';
    if (type === 'success') {
        iconClass = 'fas fa-check-circle text-green-500 text-xl';
    } else if (type === 'error') {
        iconClass = 'fas fa-exclamation-circle text-red-500 text-xl';
    } else if (type === 'warning') {
        iconClass = 'fas fa-exclamation-triangle text-amber-500 text-xl';
    }
    
    iconElement.innerHTML = `<i class="${iconClass}"></i>`;
    
    modal.classList.remove('hidden');
}

function closeNotificationModal() {
    const modal = document.getElementById('notificationModal');
    modal.classList.add('hidden');
}

// Modal Keyboard and Backdrop Support
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        // Close any open modals
        closeConfirmModal();
        closeNotificationModal();
        closeBatchDetailsModal();
    }
});

// Close modals when clicking outside
document.getElementById('confirmModal').addEventListener('click', function(event) {
    if (event.target === this) {
        closeConfirmModal();
    }
});

document.getElementById('notificationModal').addEventListener('click', function(event) {
    if (event.target === this) {
        closeNotificationModal();
    }
});

document.getElementById('batchDetailsModal').addEventListener('click', function(event) {
    if (event.target === this) {
        closeBatchDetailsModal();
    }
});

// CSRF Token for AJAX
const meta = document.createElement('meta');
meta.name = "csrf-token";
meta.content = "{{ csrf_token() }}";
document.head.appendChild(meta);
</script>
@endsection