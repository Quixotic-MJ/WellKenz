@extends('Supervisor.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. HEADER & ACTIONS --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Expiry Monitoring Report</h1>
            <p class="text-sm text-gray-500 mt-1">Track expiring batches to minimize waste and prioritize usage.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('supervisor.reports.print_use_first_list') }}" target="_blank" 
               class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition shadow-sm">
                <i class="fas fa-file-pdf mr-2"></i> Print "Use First" List
            </a>
            <button onclick="alertBakers()" 
                    class="inline-flex items-center justify-center px-4 py-2 bg-chocolate text-white text-sm font-medium rounded-lg hover:bg-chocolate-dark transition shadow-sm">
                <i class="fas fa-bullhorn mr-2"></i> Alert Bakers
            </button>
        </div>
    </div>

    {{-- 2. RISK SUMMARY --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Critical (Today/Tomorrow) -->
        <div class="bg-white border-l-4 border-red-500 rounded-lg p-5 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-red-600 uppercase tracking-wider">Critical </p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $summary['critical_count'] }} Batches</p>
                <p class="text-xs text-gray-500 mt-1">Action required immediately</p>
                @if($summary['critical_count'] > 0)
                    <p class="text-xs font-medium text-red-600 mt-1">₱{{ number_format($summary['critical_value'], 2) }} at risk</p>
                @endif
            </div>
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center text-red-600">
                <i class="fas fa-hourglass-end text-xl"></i>
            </div>
        </div>

        <!-- Warning (Next 7 Days) -->
        <div class="bg-white border-l-4 border-amber-500 rounded-lg p-5 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-amber-600 uppercase tracking-wider">Warning (7 Days)</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $summary['warning_count'] }} Batches</p>
                <p class="text-xs text-gray-500 mt-1">Plan into production schedule</p>
                @if($summary['warning_count'] > 0)
                    <p class="text-xs font-medium text-amber-600 mt-1">₱{{ number_format($summary['warning_value'], 2) }} at risk</p>
                @endif
            </div>
            <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center text-amber-600">
                <i class="fas fa-calendar-week text-xl"></i>
            </div>
        </div>

        <!-- Total Value at Risk -->
        <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Value at Risk</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $summary['formatted_total_value'] }}</p>
                <p class="text-xs text-gray-500 mt-1">Potential loss if unused</p>
                @if($summary['expired_count'] > 0)
                    <p class="text-xs font-medium text-red-500 mt-1">{{ $summary['expired_count'] }} already expired</p>
                @endif
            </div>
            <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center text-gray-500">
                <i class="fas fa-coins text-xl"></i>
            </div>
        </div>
    </div>

    {{-- 3. EXPIRY TABLE --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        
        <!-- Toolbar -->
        <div class="p-4 border-b border-gray-200 bg-gray-50 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-2">
                <span class="text-sm font-medium text-gray-700">Show:</span>
                <select class="block w-40 py-1.5 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-xs" 
                        onchange="updateFilter(this.value)">
                    <option value="7days" {{ $filter == '7days' ? 'selected' : '' }}>Next 7 Days</option>
                    <option value="30days" {{ $filter == '30days' ? 'selected' : '' }}>Next 30 Days</option>
                    <option value="expired" {{ $filter == 'expired' ? 'selected' : '' }}>Already Expired</option>
                </select>
            </div>
            <div class="relative w-full md:w-64">
                <form method="GET" action="{{ route('supervisor.reports.expiry') }}" class="flex gap-2">
                    <input type="text" 
                           name="search" 
                           value="{{ $search }}" 
                           class="block w-full pl-9 pr-3 py-1.5 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-400 focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-xs" 
                           placeholder="Filter by item...">
                    <input type="hidden" name="filter" value="{{ $filter }}">
                    <button type="submit" class="px-3 py-1.5 bg-chocolate text-white text-sm rounded-md hover:bg-chocolate-dark">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400 text-xs"></i>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item / Batch Info</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expiry Date</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Countdown</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Remaining Qty</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                        <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Priority Status</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    
                    @forelse($expiringBatches as $batch)
                        @php
                            $batchData = $expiringBatches->firstWhere('id', $batch->id);
                            // Convert to formatBatchData array for backward compatibility
                            $formattedBatch = [
                                'id' => $batch->id,
                                'batch_number' => $batch->batch_number,
                                'item_name' => $batch->item->name ?? 'Unknown Item',
                                'item_code' => $batch->item->item_code ?? '',
                                'unit_symbol' => $batch->item->unit->symbol ?? '',
                                'quantity' => number_format($batch->quantity, 1),
                                'unit_cost' => number_format($batch->unit_cost, 2),
                                'total_value' => number_format($batch->quantity * $batch->unit_cost, 2),
                                'expiry_date' => \Carbon\Carbon::parse($batch->expiry_date)->format('M j, Y'),
                                'manufacturing_date' => $batch->manufacturing_date ? \Carbon\Carbon::parse($batch->manufacturing_date)->format('M j, Y') : 'N/A',
                                'days_until_expiry' => \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($batch->expiry_date), false),
                                'countdown_text' => '',
                                'countdown_class' => '',
                                'priority' => '',
                                'priority_class' => '',
                                'status_class' => '',
                                'supplier_name' => $batch->supplier->name ?? 'Unknown Supplier',
                                'location' => $batch->location ?? 'Storage',
                                'urgent_action' => false,
                                'is_expired' => \Carbon\Carbon::parse($batch->expiry_date)->isPast(),
                                'is_critical' => false,
                                'is_warning' => false
                            ];
                            
                            // Calculate countdown and priority
                            $now = \Carbon\Carbon::now();
                            $expiryDate = \Carbon\Carbon::parse($batch->expiry_date);
                            $daysUntilExpiry = $now->diffInDays($expiryDate, false);
                            $isPastExpiry = $expiryDate->isPast();
                            
                            if ($isPastExpiry) {
                                $formattedBatch['countdown_text'] = 'EXPIRED';
                                $formattedBatch['countdown_class'] = 'bg-red-600 text-white animate-pulse';
                                $formattedBatch['priority'] = 'EXPIRED';
                                $formattedBatch['priority_class'] = 'text-red-600 font-bold';
                                $formattedBatch['status_class'] = 'text-red-600 font-bold';
                                $formattedBatch['urgent_action'] = true;
                                $formattedBatch['is_expired'] = true;
                            } elseif ($daysUntilExpiry <= 1) {
                                $formattedBatch['countdown_text'] = $daysUntilExpiry == 0 ? 'EXPIRES TODAY' : '1 Day Left';
                                $formattedBatch['countdown_class'] = $daysUntilExpiry == 0 ? 'bg-red-600 text-white animate-pulse' : 'bg-red-600 text-white';
                                $formattedBatch['priority'] = $daysUntilExpiry == 0 ? 'Use Immediately' : 'High Priority';
                                $formattedBatch['priority_class'] = 'text-red-600 font-bold';
                                $formattedBatch['status_class'] = 'text-red-600 font-bold';
                                $formattedBatch['urgent_action'] = true;
                                $formattedBatch['is_critical'] = true;
                            } elseif ($daysUntilExpiry <= 3) {
                                $formattedBatch['countdown_text'] = $daysUntilExpiry . ' Days Left';
                                $formattedBatch['countdown_class'] = 'bg-red-100 text-red-800 border border-red-200';
                                $formattedBatch['priority'] = 'Plan Usage';
                                $formattedBatch['priority_class'] = 'text-red-600';
                                $formattedBatch['status_class'] = 'text-red-600';
                                $formattedBatch['is_warning'] = true;
                            } elseif ($daysUntilExpiry <= 7) {
                                $formattedBatch['countdown_text'] = $daysUntilExpiry . ' Days Left';
                                $formattedBatch['countdown_class'] = 'bg-amber-100 text-amber-800';
                                $formattedBatch['priority'] = 'Monitor';
                                $formattedBatch['priority_class'] = 'text-amber-600';
                                $formattedBatch['status_class'] = 'text-amber-600';
                                $formattedBatch['is_warning'] = true;
                            } else {
                                $formattedBatch['countdown_text'] = $daysUntilExpiry . ' Days Left';
                                $formattedBatch['countdown_class'] = 'bg-gray-100 text-gray-600';
                                $formattedBatch['priority'] = 'Normal';
                                $formattedBatch['priority_class'] = 'text-green-600';
                                $formattedBatch['status_class'] = 'text-green-600';
                            }
                        @endphp
                    
                        {{-- Dynamic Row --}}
                        <tr class="{{ $formattedBatch['urgent_action'] ? 'bg-red-50 hover:bg-red-100' : ($formattedBatch['is_warning'] ? 'hover:bg-amber-50' : 'hover:bg-gray-50') }} transition-colors border-l-4 {{ $formattedBatch['urgent_action'] ? 'border-red-500' : ($formattedBatch['is_warning'] ? 'border-amber-300' : 'border-transparent') }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 {{ $formattedBatch['urgent_action'] ? 'bg-white rounded border border-red-200 flex items-center justify-center text-red-600 font-bold text-xs' : 'bg-gray-100 rounded flex items-center justify-center text-gray-500 text-lg' }}">
                                        @if($formattedBatch['urgent_action'])
                                            {{ strtoupper(substr($formattedBatch['item_name'], 0, 4)) }}
                                        @else
                                            <i class="fas fa-{{ $formattedBatch['is_warning'] ? 'clock' : 'box' }}"></i>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-bold text-gray-900">{{ $formattedBatch['item_name'] }}</div>
                                        <div class="text-xs text-gray-500">Batch #{{ $formattedBatch['batch_number'] }}</div>
                                        @if($formattedBatch['supplier_name'] !== 'Unknown Supplier')
                                            <div class="text-xs text-gray-400">{{ $formattedBatch['supplier_name'] }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm {{ $formattedBatch['urgent_action'] ? 'text-red-600 font-bold' : 'text-gray-900' }}">
                                {{ $formattedBatch['expiry_date'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="px-2 py-1 text-xs font-bold {{ $formattedBatch['countdown_class'] }} rounded">
                                    {{ $formattedBatch['countdown_text'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm font-bold text-gray-900">{{ $formattedBatch['quantity'] }} {{ $formattedBatch['unit_symbol'] }}</div>
                                <div class="text-xs text-gray-500">@ ₱{{ $formattedBatch['unit_cost'] }}/{{ $formattedBatch['unit_symbol'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="text-sm font-bold text-gray-900">₱{{ $formattedBatch['total_value'] }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="text-xs font-bold {{ $formattedBatch['priority_class'] }} uppercase tracking-wide">
                                    {{ $formattedBatch['priority'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                @if($formattedBatch['urgent_action'])
                                    <button class="text-white bg-chocolate hover:bg-chocolate-dark px-3 py-1 rounded text-xs shadow-sm transition">
                                        Use Now
                                    </button>
                                @elseif($formattedBatch['is_warning'])
                                    <button class="text-chocolate hover:text-chocolate-dark font-bold text-xs border border-chocolate/30 px-3 py-1 rounded hover:bg-orange-50 transition">
                                        Use Now
                                    </button>
                                @else
                                    <button class="text-gray-500 hover:text-gray-700 font-bold text-xs border border-gray-300 px-3 py-1 rounded hover:bg-gray-50 transition">
                                        Details
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">No expiring batches found</h3>
                                    <p class="text-sm text-gray-500">
                                        @if($search)
                                            No batches match your search criteria for "{{ $search }}"
                                        @else
                                            All batches are within their shelf life
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
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $expiringBatches->appends(['filter' => $filter, 'search' => $search])->links() }}
            </div>
        @endif
        
        <!-- Footer Note -->
        <div class="bg-gray-50 px-4 py-3 border-t border-gray-200">
            <p class="text-xs text-gray-500 text-center italic">
                Note: Items marked "Use Immediately" should be prioritized in today's production or transferred to the staff cafeteria to avoid waste.
                @if($summary['total_batches'] > 0)
                    <br>
                    <strong>Summary:</strong> {{ $summary['critical_count'] }} critical, {{ $summary['warning_count'] }} warning, {{ $summary['expired_count'] }} expired batches. Total value at risk: {{ $summary['formatted_total_value'] }}
                @endif
            </p>
        </div>
    </div>

</div>

<script>
function updateFilter(filter) {
    const url = new URL(window.location);
    url.searchParams.set('filter', filter);
    window.location.href = url.toString();
}

// Alert Bakers functionality
function alertBakers() {
    if (!confirm('Send expiry alerts to all bakers? This will notify them about items expiring soon.')) {
        return;
    }
    
    const button = event.target;
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Sending...';
    
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
            alert(data.message);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while sending alerts. Please try again.');
    })
    .finally(() => {
        button.disabled = false;
        button.innerHTML = originalText;
    });
}

// Auto-refresh every 5 minutes
setInterval(function() {
    if (document.hidden) return; // Don't refresh if tab is not visible
    
    const currentTime = new Date();
    const lastUpdate = localStorage.getItem('lastExpiryRefresh');
    
    if (!lastUpdate || (currentTime.getTime() - parseInt(lastUpdate)) > 300000) { // 5 minutes
        localStorage.setItem('lastExpiryRefresh', currentTime.getTime());
        window.location.reload();
    }
}, 60000); // Check every minute
</script>
@endsection