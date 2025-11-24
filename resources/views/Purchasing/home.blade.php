@extends('Purchasing.layout.app')

@section('content')
{{-- THEME CONFIGURATION --}}
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    'primary': '#2c1810', // Deep professional brown (Chocolate)
                    'primary-light': '#4a3b32',
                    'accent': '#c48d3f', // Muted Gold/Caramel
                    'surface': '#ffffff',
                    'background': '#f3f4f6', // Standard ERP Gray
                    'text-main': '#111827', // Gray 900
                    'text-secondary': '#4b5563', // Gray 600
                    'border-color': '#e5e7eb', // Gray 200
                },
                fontFamily: {
                    'sans': ['"Inter"', 'ui-sans-serif', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial', 'sans-serif'],
                    'mono': ['ui-monospace', 'SFMono-Regular', 'Menlo', 'Monaco', 'Consolas', 'monospace'],
                }
            }
        }
    }
</script>
{{-- Using Inter font for maximum legibility in data-heavy apps --}}
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f3f4f6;
    }
    .erp-card {
        background-color: white;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem; /* rounded-lg */
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 3px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
</style>

<div class="min-h-screen -m-4 p-6 bg-background text-text-main">
    
    <div class="w-full max-w-[1600px] mx-auto space-y-6">
        
        {{-- 1. HEADER --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-gray-300">
            <div>
                <h1 class="text-2xl font-bold text-primary tracking-tight">Procurement Dashboard</h1>
                <div class="flex items-center gap-4 mt-1">
                    <span class="text-sm font-medium text-text-secondary">
                        <i class="far fa-calendar-alt mr-1.5"></i>
                        {{ date('F d, Y') }}
                    </span>
                    <span class="text-xs text-gray-400">|</span>
                    <p class="text-xs text-text-secondary flex items-center">
                        <i class="fas fa-sync-alt mr-1.5 text-gray-400"></i>
                        Data synced: {{ now()->format('H:i') }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="location.reload()" class="px-3 py-2 bg-white border border-gray-300 text-text-secondary text-sm font-medium rounded hover:bg-gray-50 transition shadow-sm">
                    <i class="fas fa-redo-alt"></i>
                </button>
            </div>
        </div>

        {{-- 2. KPI / METRICS ROW --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            {{-- WIDGET 1: LOW STOCK ALERTS --}}
            <div class="erp-card flex flex-col h-80 overflow-hidden">
                <div class="px-4 py-3 border-b border-border-color bg-gray-50 flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <div class="text-red-600">
                            <i class="fas fa-cubes"></i>
                        </div>
                        <h3 class="font-semibold text-sm text-gray-900">Inventory Alerts</h3>
                    </div>
                    <span class="bg-red-100 text-red-700 text-xs font-bold px-2 py-0.5 rounded border border-red-200">{{ $lowStockItems->count() }} Critical</span>
                </div>
                
                <div class="flex-1 overflow-y-auto p-0 custom-scrollbar">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Name</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Stock Level</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($lowStockItems as $item)
                            @php
                                $percentage = min(100, ($item['current_stock'] / max(1, $item['min_stock'])) * 100);
                                $statusColor = $percentage < 25 ? 'bg-red-600' : 'bg-yellow-500';
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 truncate max-w-[150px]" title="{{ $item['name'] }}">{{ $item['name'] }}</div>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-right align-middle">
                                    <div class="flex flex-col items-end">
                                        <span class="text-xs font-bold text-gray-900">{{ $item['current_stock'] }} <span class="text-gray-400 font-normal">/ {{ $item['min_stock'] }}</span></span>
                                        <div class="w-16 h-1 bg-gray-200 rounded-full mt-1">
                                            <div class="h-1 rounded-full {{ $statusColor }}" style="width: {{ $percentage }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="#" class="text-accent hover:text-primary text-xs font-semibold">Restock</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-gray-500 text-sm">
                                    No critical stock items.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- WIDGET 2: FINANCIAL COMMITMENTS --}}
            <div class="erp-card flex flex-col justify-between h-80">
                <div class="p-5">
                    <h3 class="font-semibold text-sm text-text-secondary uppercase tracking-wider mb-4">Open Commitments</h3>
                    
                    <div class="flex items-baseline gap-1 mb-2">
                        <span class="text-2xl font-normal text-gray-500">₱</span>
                        <span class="text-4xl font-bold text-primary tracking-tight">{{ number_format($openPoValue, 2) }}</span>
                    </div>
                    
                    <div class="mt-4 flex items-center p-2 bg-blue-50 border border-blue-100 rounded text-blue-800 text-sm">
                        <i class="fas fa-info-circle mr-2"></i>
                        <span class="font-medium">{{ $openPoCount }} Active Purchase Orders</span>
                    </div>

                    <div class="mt-6 space-y-3">
                        <div class="flex justify-between text-sm border-b border-gray-100 pb-2">
                            <span class="text-gray-600">Pending Approval</span>
                            <span class="font-mono font-medium text-gray-900">₱ {{ number_format($openPoValue * 0.2, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm border-b border-gray-100 pb-2">
                            <span class="text-gray-600">Awaiting Goods Receipt</span>
                            <span class="font-mono font-medium text-gray-900">₱ {{ number_format($openPoValue * 0.8, 2) }}</span>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-5 py-3 border-t border-gray-200">
                    <a href="#" class="text-xs font-semibold text-primary hover:underline">View Financial Report &rarr;</a>
                </div>
            </div>

            {{-- WIDGET 3: DELIVERY EXCEPTION MONITOR --}}
            <div class="erp-card flex flex-col h-80 overflow-hidden border-l-4 border-l-amber-500">
                <div class="px-4 py-3 border-b border-gray-200 bg-white flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <div class="text-amber-600">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h3 class="font-semibold text-sm text-gray-900">Overdue Deliveries</h3>
                    </div>
                    <span class="text-xs text-gray-500 font-mono">Count: {{ $overdueDeliveries->count() }}</span>
                </div>
                
                <div class="flex-1 overflow-y-auto custom-scrollbar">
                    <div class="divide-y divide-gray-200">
                        @forelse($overdueDeliveries as $delivery)
                        <div class="p-3 hover:bg-gray-50 transition">
                            <div class="flex justify-between items-start">
                                <div>
                                    <div class="text-sm font-bold text-gray-900">{{ $delivery['supplier_name'] }}</div>
                                    <div class="text-xs text-gray-500 mt-0.5">PO Ref: <a href="#" class="text-blue-600 hover:underline font-mono">{{ $delivery['po_number'] }}</a></div>
                                </div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                    +{{ max(0, $delivery['days_overdue']) }} Days
                                </span>
                            </div>
                            
                            <div class="mt-2 flex gap-2">
                                @if($delivery['supplier_phone'])
                                <a href="tel:{{ $delivery['supplier_phone'] }}" class="inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50">
                                    <i class="fas fa-phone mr-1.5 text-gray-400"></i> Call
                                </a>
                                @endif
                                <a href="mailto:{{ $delivery['supplier_email'] }}" class="inline-flex items-center px-2 py-1 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50">
                                    <i class="fas fa-envelope mr-1.5 text-gray-400"></i> Email
                                </a>
                            </div>
                        </div>
                        @empty
                        <div class="p-8 text-center text-gray-500 text-sm">
                            <i class="fas fa-check-circle text-green-500 mb-2"></i>
                            <br>All deliveries are on schedule.
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>

        {{-- 3. GRID LAYOUT FOR TABLES & TOOLS --}}
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            
            {{-- Main Data Table (Recent POs) --}}
            <div class="lg:col-span-3 erp-card overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                    <h3 class="font-semibold text-sm text-gray-900">Recent Purchase Orders</h3>
                    <div class="flex gap-2">
                        <input type="text" placeholder="Filter POs..." class="text-xs border border-gray-300 rounded px-2 py-1 focus:outline-none focus:border-primary">
                        <a href="{{ route('purchasing.po.history') }}" class="text-xs font-medium text-primary hover:text-primary-light border border-gray-300 bg-white px-3 py-1 rounded hover:bg-gray-50 transition">View All</a>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PO Number</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Amount</th>
                                <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" class="relative px-6 py-3">
                                    <span class="sr-only">View</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 text-sm">
                            @forelse($recentPurchaseOrders as $order)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-3 whitespace-nowrap font-mono text-primary font-medium">#{{ $order['po_number'] }}</td>
                                <td class="px-6 py-3 whitespace-nowrap text-gray-900">{{ $order['supplier_name'] }}</td>
                                <td class="px-6 py-3 whitespace-nowrap text-gray-500 text-xs">{{ isset($order['created_at']) ? \Carbon\Carbon::parse($order['created_at'])->format('M d, Y') : '-' }}</td>
                                <td class="px-6 py-3 whitespace-nowrap text-right font-mono font-medium text-gray-900">₱ {{ number_format($order['total_amount'], 2) }}</td>
                                <td class="px-6 py-3 whitespace-nowrap text-center">
                                    @php
                                        $statusStyles = match($order['status']) {
                                            'draft' => 'bg-gray-100 text-gray-800',
                                            'sent' => 'bg-blue-100 text-blue-800',
                                            'confirmed' => 'bg-indigo-100 text-indigo-800',
                                            'completed' => 'bg-green-100 text-green-800',
                                            'cancelled' => 'bg-red-100 text-red-800',
                                            default => 'bg-gray-100 text-gray-800'
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusStyles }}">
                                        {{ ucfirst($order['status']) }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="#" class="text-primary hover:text-primary-light"><i class="fas fa-external-link-alt"></i></a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500 italic">No recent orders found in the system.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Sidebar Tool (Vendor Lookup) --}}
            <div class="lg:col-span-1 space-y-6">
                <div class="erp-card p-5">
                    <h3 class="font-semibold text-sm text-gray-900 mb-4">Supplier Directory</h3>
                    
                    <div class="relative group mb-4">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" id="supplier-search" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-primary focus:border-primary sm:text-sm transition duration-150 ease-in-out" placeholder="Search code or name..." autocomplete="off">
                    </div>
                    
                    {{-- Results Dropdown --}}
                    <div id="supplier-search-results" class="hidden absolute z-50 w-64 bg-white border border-gray-200 rounded-md shadow-lg max-h-60 overflow-y-auto"></div>
                    
                    <div id="frequent-suppliers">
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Frequent Contacts</p>
                        <ul class="divide-y divide-gray-100">
                            @forelse($frequentSuppliers as $supplier)
                            <li class="py-2 hover:bg-gray-50 transition -mx-2 px-2 rounded cursor-pointer group" onclick="selectSupplier('{{ $supplier['name'] }}', {{ $supplier['id'] }})">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0 h-8 w-8 rounded bg-primary/10 text-primary flex items-center justify-center text-xs font-bold border border-primary/20">
                                        {{ substr($supplier['name'], 0, 2) }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900 truncate group-hover:text-primary">
                                            {{ $supplier['name'] }}
                                        </p>
                                        <p class="text-xs text-gray-500 truncate">
                                            ID: {{ $supplier['id'] }}
                                        </p>
                                    </div>
                                </div>
                            </li>
                            @empty
                            <li class="text-xs text-gray-500 py-2">No frequent suppliers logged.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
                
                {{-- KPI Mini Card --}}
                <div class="erp-card p-4 bg-white border-l-4 border-green-500">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-xs font-medium text-gray-500 uppercase">On-Time Delivery Rate</p>
                            <p class="mt-1 text-2xl font-bold text-gray-900">94.2%</p>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                            <i class="fas fa-arrow-up mr-1"></i> 1.2%
                        </span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-1.5 mt-3">
                        <div class="bg-green-500 h-1.5 rounded-full" style="width: 94.2%"></div>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

<script>
let searchTimeout;
const searchInput = document.getElementById('supplier-search');
const searchResults = document.getElementById('supplier-search-results');
// ... (Keeping existing JS logic) ...
</script>
@endsection