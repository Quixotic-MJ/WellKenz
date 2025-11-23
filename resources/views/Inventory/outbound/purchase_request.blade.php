@extends('Inventory.layout.app')

@section('content')
<style>
    /* --- Theming & Variables --- */
    :root {
        --color-chocolate: #d2691e; /* Fallback if tailwind config missing */
        --color-chocolate-dark: #8b4513;
        --color-chocolate-light: #f4a460;
    }
    
    /* --- Custom Scrollbar --- */
    .modal-scroll::-webkit-scrollbar { width: 6px; height: 6px; }
    .modal-scroll::-webkit-scrollbar-track { background: transparent; }
    .modal-scroll::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 10px; }
    .modal-scroll::-webkit-scrollbar-thumb:hover { background: #d1d5db; }
    
    /* --- Animations --- */
    .animate-slide-up { animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    
    .animate-pulse-subtle { animation: pulseSubtle 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
    @keyframes pulseSubtle { 0%, 100% { opacity: 1; } 50% { opacity: .7; } }

    /* --- Modal Transitions --- */
    .modal-backdrop { transition: opacity 0.3s ease-out; opacity: 0; pointer-events: none; }
    .modal-backdrop.active { opacity: 1; pointer-events: auto; }
    .modal-panel { transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); transform: scale(0.95) translateY(10px); opacity: 0; }
    .modal-panel.active { transform: scale(1) translateY(0); opacity: 1; }

    /* --- Component Styles --- */
    .glass-panel {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .item-card { 
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid #f3f4f6;
    }
    .item-card:hover { 
        transform: translateY(-4px);
        box-shadow: 0 12px 24px -10px rgba(0, 0, 0, 0.1);
        border-color: var(--color-chocolate-light);
    }

    /* --- Form Elements --- */
    .enhanced-input, .enhanced-select {
        @apply w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm transition-all duration-200;
    }
    .enhanced-input:focus, .enhanced-select:focus {
        @apply bg-white ring-2 ring-offset-0 outline-none border-transparent;
        --tw-ring-color: var(--color-chocolate);
        box-shadow: 0 0 0 2px var(--color-chocolate-light);
    }

    /* --- Utilities --- */
    .text-chocolate { color: var(--color-chocolate); }
    .bg-chocolate { background-color: var(--color-chocolate); }
    .hover\:bg-chocolate:hover { background-color: var(--color-chocolate); }
    .hover\:text-chocolate:hover { color: var(--color-chocolate); }
    .border-chocolate { border-color: var(--color-chocolate); }
    .ring-chocolate { --tw-ring-color: var(--color-chocolate); }
    
    .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>

<div class="flex flex-col lg:flex-row h-[calc(100vh-6rem)] gap-6 pb-2 relative font-sans text-gray-700">
    
    {{-- 1. CATALOG SECTION (Left) --}}
    <div class="flex-1 flex flex-col min-w-0 bg-white border border-gray-200 rounded-3xl shadow-sm overflow-hidden h-full relative">
        
        <div class="px-6 py-5 border-b border-gray-100 bg-white/80 backdrop-blur-md z-10 sticky top-0">
            <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4">
                <div>
                    <h1 class="text-2xl font-extrabold text-gray-800 tracking-tight">Purchase Catalog</h1>
                    <p class="text-sm text-gray-500 mt-1">Browse items and create your requisition slip.</p>
                </div>
                
                <div class="flex gap-3">
                    <div class="px-4 py-2 bg-amber-50 rounded-2xl border border-amber-100 flex flex-col items-center min-w-[80px]">
                        <span class="text-[10px] uppercase font-bold text-amber-600 tracking-wider">Pending</span>
                        <span class="text-xl font-bold text-amber-800 leading-none mt-1">{{ $stats['pending'] ?? 0 }}</span>
                    </div>
                    <div class="px-4 py-2 bg-green-50 rounded-2xl border border-green-100 flex flex-col items-center min-w-[80px]">
                        <span class="text-[10px] uppercase font-bold text-green-600 tracking-wider">Approved</span>
                        <span class="text-xl font-bold text-green-800 leading-none mt-1">{{ $stats['approved'] ?? 0 }}</span>
                    </div>
                    
                    <div class="flex gap-2 ml-2">
                         <button onclick="PRManager.openHistory()" class="w-10 h-full rounded-xl bg-gray-50 hover:bg-gray-100 border border-gray-200 text-gray-600 flex items-center justify-center transition-colors" title="View History">
                            <i class="fas fa-history text-lg"></i>
                        </button>
                        <button onclick="PRManager.refreshData()" class="w-10 h-full rounded-xl bg-blue-50 hover:bg-blue-100 border border-blue-200 text-blue-600 flex items-center justify-center transition-colors" title="Refresh">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="mt-6 p-1 bg-gray-100/50 rounded-2xl border border-gray-100 grid grid-cols-1 md:grid-cols-4 gap-2">
                <div class="relative group col-span-1">
                    <i class="fas fa-search absolute left-4 top-3 text-gray-400 group-focus-within:text-chocolate transition-colors"></i>
                    <input type="text" id="searchInput" placeholder="Search items..." 
                           class="w-full pl-10 pr-4 py-2.5 bg-white border-0 rounded-xl focus:ring-2 focus:ring-chocolate/20 text-sm shadow-sm transition-all placeholder-gray-400">
                </div>
                
                <select id="categoryFilter" class="bg-white border-0 py-2.5 px-4 rounded-xl text-sm text-gray-600 shadow-sm focus:ring-2 focus:ring-chocolate/20 cursor-pointer hover:bg-gray-50 transition-colors">
                    <option value="all">All Categories</option>
                    @if(isset($categories) && count($categories) > 0)
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    @endif
                </select>

                <select id="stockFilter" class="bg-white border-0 py-2.5 px-4 rounded-xl text-sm text-gray-600 shadow-sm focus:ring-2 focus:ring-chocolate/20 cursor-pointer hover:bg-gray-50 transition-colors">
                    <option value="all">All Stock Status</option>
                    <option value="normal_stock">Normal Stock</option>
                    <option value="low_stock">Low Stock</option>
                    <option value="out_of_stock">Out of Stock</option>
                    <option value="high_stock">High Stock</option>
                </select>

                <select id="priceFilter" class="bg-white border-0 py-2.5 px-4 rounded-xl text-sm text-gray-600 shadow-sm focus:ring-2 focus:ring-chocolate/20 cursor-pointer hover:bg-gray-50 transition-colors">
                    <option value="all">Any Price</option>
                    <option value="0-50">₱0 - ₱50</option>
                    <option value="51-100">₱51 - ₱100</option>
                    <option value="101-500">₱101 - ₱500</option>
                    <option value="500+">₱500+</option>
                </select>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-6 bg-slate-50/50 modal-scroll relative">
            <div id="loadingState" class="hidden animate-pulse">
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
                    @for($i = 1; $i <= 6; $i++)
                        <div class="h-40 bg-gray-200 rounded-2xl"></div>
                    @endfor
                </div>
            </div>

            <div id="itemsGrid" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5 pb-10">
                @if(isset($items) && count($items) > 0)
                    @foreach($items as $item)
                        <div class="item-card bg-white p-5 rounded-2xl flex flex-col h-full animate-slide-up relative group overflow-hidden"
                             data-item-id="{{ $item->id }}"
                             data-category-id="{{ $item->category->id ?? 0 }}"
                             data-stock-status="{{ $item->stock_status ?? 'normal_stock' }}"
                             data-price="{{ $item->cost_price ?? 0 }}"
                             data-name="{{ strtolower($item->name) }}"
                             data-code="{{ strtolower($item->item_code ?? '') }}"
                             data-description="{{ strtolower($item->description ?? '') }}">
                            <div class="flex justify-between items-start mb-3 relative z-10">
                                <div class="w-12 h-12 rounded-xl bg-orange-50 text-chocolate flex items-center justify-center text-lg shadow-inner">
                                    <i class="fas fa-box-open"></i>
                                </div>
                                <div class="text-right">
                                     @php
                                        $stockStatus = $item->stock_status ?? 'normal_stock';
                                        $badgeClass = match($stockStatus) {
                                            'out_of_stock' => 'bg-red-50 text-red-600 border border-red-100',
                                            'low_stock' => 'bg-amber-50 text-amber-600 border border-amber-100',
                                            'high_stock' => 'bg-blue-50 text-blue-600 border border-blue-100',
                                            default => 'bg-emerald-50 text-emerald-600 border border-emerald-100'
                                        };
                                        $badgeText = match($stockStatus) {
                                            'out_of_stock' => 'Out of Stock',
                                            'low_stock' => 'Low Stock',
                                            'high_stock' => 'High Stock',
                                            default => 'In Stock'
                                        };
                                    @endphp
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wide {{ $badgeClass }}">
                                        {{ $badgeText }}
                                    </span>
                                </div>
                            </div>

                            <div class="mb-4 relative z-10">
                                <h4 class="font-bold text-gray-800 leading-tight mb-1 group-hover:text-chocolate transition-colors line-clamp-1" title="{{ $item->name }}">{{ $item->name }}</h4>
                                <div class="flex items-center gap-2 text-xs text-gray-400 mb-2">
                                    <span class="bg-gray-100 px-1.5 py-0.5 rounded text-gray-500 font-mono">{{ $item->item_code ?? 'N/A' }}</span>
                                    <span>•</span>
                                    <span>{{ $item->category->name ?? 'General' }}</span>
                                </div>
                                <p class="text-xs text-gray-500 line-clamp-2 h-8 leading-relaxed">{{ $item->description ?? 'No description available for this item.' }}</p>
                            </div>
                            
                            <div class="mt-auto pt-4 border-t border-gray-50 flex items-center justify-between relative z-10">
                                <div class="flex flex-col">
                                    <span class="text-[10px] text-gray-400 font-medium uppercase tracking-wider">Est. Cost</span>
                                    <span class="font-bold text-lg text-gray-800">₱{{ number_format($item->cost_price ?? 0, 2) }}</span>
                                </div>
                                
                                <button onclick="PRManager.addToCart({{ $item->id }})" 
                                    class="w-10 h-10 rounded-xl bg-gray-900 text-white shadow-lg shadow-gray-200 hover:bg-chocolate hover:shadow-orange-200 hover:scale-105 active:scale-95 transition-all flex items-center justify-center">
                                    <i class="fas fa-plus text-sm"></i>
                                </button>
                            </div>
                            
                            <div class="absolute inset-0 bg-gradient-to-br from-transparent via-transparent to-orange-50/50 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
                        </div>
                    @endforeach
                @endif
            </div>
            
            <div id="noItemsMessage" class="hidden flex-col items-center justify-center h-full text-center pb-20">
                <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mb-4 animate-pulse-subtle">
                    <i class="fas fa-search text-3xl text-gray-300"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900">No items found</h3>
                <p class="text-sm text-gray-500 max-w-xs mx-auto mt-2">We couldn't find any items matching your filters. Try adjusting your search criteria.</p>
                <button onclick="document.getElementById('searchInput').value=''; document.getElementById('searchInput').dispatchEvent(new Event('input'));" class="mt-4 text-chocolate font-medium text-sm hover:underline">Clear Search</button>
            </div>
        </div>
    </div>

    {{-- 2. CART SECTION (Right) --}}
    <div class="w-full lg:w-[400px] flex flex-col h-full shrink-0">
        <form id="requisitionForm" onsubmit="return false;" class="bg-white border border-gray-200 rounded-3xl shadow-xl shadow-gray-200/50 flex flex-col h-full overflow-hidden relative">
            
            <div class="px-6 py-5 bg-gray-900 text-white relative overflow-hidden shrink-0">
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full blur-2xl -mr-10 -mt-10"></div>
                <div class="flex justify-between items-center relative z-10">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center backdrop-blur-sm border border-white/10">
                            <i class="fas fa-file-invoice text-orange-300"></i>
                        </div>
                        <div>
                            <h2 class="font-bold text-lg leading-tight">Requisition Slip</h2>
                            <p class="text-xs text-gray-400 font-medium tracking-wide">{{ date('F d, Y') }}</p>
                        </div>
                    </div>
                    <div class="flex flex-col items-end">
                        <span class="text-[10px] text-gray-400 uppercase font-bold tracking-wider">Items</span>
                        <span class="font-mono text-xl font-bold text-orange-300" id="cartCount">0</span>
                    </div>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-4 bg-gray-50 modal-scroll relative" id="cartContainer">
                <div id="emptyCartMessage" class="flex flex-col items-center justify-center h-full text-center opacity-60 pb-10">
                    <div class="w-20 h-20 border-2 border-dashed border-gray-300 rounded-2xl flex items-center justify-center mb-4">
                        <i class="fas fa-basket-shopping text-2xl text-gray-300"></i>
                    </div>
                    <p class="text-sm font-bold text-gray-600">Your slip is empty</p>
                    <p class="text-xs text-gray-400 mt-1">Add items from the catalog</p>
                </div>
            </div>

            <div class="p-6 bg-white border-t border-gray-100 shadow-[0_-10px_40px_-10px_rgba(0,0,0,0.05)] relative z-20">
                 <button type="button" onclick="PRManager.clearCart()" class="absolute top-[-1.2rem] right-6 px-3 py-1 bg-red-50 hover:bg-red-100 text-red-500 text-xs font-bold rounded-full shadow-sm border border-red-100 transition-all opacity-0 pointer-events-none translate-y-2" id="clearCartBtn" style="display: none;">
                    Clear All
                </button>

                <div class="space-y-4 mb-6">
                    <div>
                        <input type="text" name="department" id="deptInput" required 
                               placeholder="Department (e.g. Production)" 
                               value="{{ $defaultDepartment ?? '' }}"
                               class="enhanced-input focus:ring-chocolate placeholder-gray-400">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <select name="priority" id="priorityInput" class="enhanced-select">
                            <option value="normal">Normal Priority</option>
                            <option value="high">High Priority</option>
                            <option value="urgent">Urgent</option>
                            <option value="low">Low Priority</option>
                        </select>
                        <input type="date" name="request_date" id="dateInput" 
                               value="{{ date('Y-m-d') }}" class="enhanced-input text-gray-600">
                    </div>
                    <textarea name="notes" id="notesInput" rows="2" 
                              placeholder="Add notes or justification..." 
                              class="enhanced-input resize-none"></textarea>
                </div>

                <div class="flex flex-col gap-4">
                    <div class="flex justify-between items-end px-2">
                        <span class="text-sm font-medium text-gray-500">Total Estimate</span>
                        <span class="text-2xl font-bold text-gray-800 tracking-tight" id="cartTotal">₱ 0.00</span>
                    </div>
                    
                    <button type="button" onclick="PRManager.submitPR()" id="submitBtn" disabled 
                            class="w-full py-4 bg-gray-100 text-gray-400 font-bold rounded-xl cursor-not-allowed transition-all duration-300 flex items-center justify-center gap-2 group relative overflow-hidden">
                        <span class="relative z-10">Submit Request</span>
                        <i class="fas fa-arrow-right relative z-10 transform group-hover:translate-x-1 transition-transform"></i>
                        <div class="absolute inset-0 bg-chocolate opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    </button>
                    <p class="text-center text-[10px] text-gray-400" id="totalItems">0 items selected</p>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- HISTORY MODAL (Improved) --}}
<div id="historyModalBackdrop" class="hidden fixed inset-0 z-[60] modal-backdrop">
    <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm transition-opacity" onclick="PRManager.closeHistory()"></div>
    <div class="flex items-center justify-center min-h-screen p-4 sm:p-6">
        <div id="historyModalPanel" class="bg-white rounded-3xl shadow-2xl w-full max-w-6xl max-h-[85vh] flex flex-col modal-panel border border-white/20">
            
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50 rounded-t-3xl">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center shadow-sm text-chocolate border border-gray-100">
                        <i class="fas fa-history text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">Request History</h2>
                        <p class="text-sm text-gray-500">Track status of your previous requests.</p>
                    </div>
                </div>
                <button onclick="PRManager.closeHistory()" class="w-10 h-10 rounded-full bg-white hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors flex items-center justify-center shadow-sm border border-gray-100">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="p-4 bg-white border-b border-gray-100 flex flex-wrap gap-4 items-center justify-between">
                <div class="flex flex-wrap gap-3 flex-1">
                    <select id="historyStatusFilter" class="enhanced-select w-auto min-w-[140px] text-xs">
                        <option value="all">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    <select id="historyDepartmentFilter" class="enhanced-select w-auto min-w-[140px] text-xs">
                        <option value="all">All Departments</option>
                        @if(isset($departments) && count($departments) > 0)
                            @foreach($departments as $dept)
                                <option value="{{ $dept }}">{{ $dept }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="w-full sm:w-64 relative">
                    <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-xs"></i>
                    <input type="text" id="historySearchInput" placeholder="Search by PR # or Dept..." 
                           class="enhanced-input pl-9 py-2 text-xs">
                </div>
            </div>

            <div class="flex-1 overflow-y-auto p-0 modal-scroll bg-gray-50 rounded-b-3xl">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50 sticky top-0 z-10 shadow-sm">
                        <tr>
                            <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">Reference</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">Department</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">Priority</th>
                            <th class="px-6 py-4 text-left text-[11px] font-bold text-gray-400 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-4 text-center text-[11px] font-bold text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-right text-[11px] font-bold text-gray-400 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-50" id="historyTableBody">
                        @if(isset($purchaseRequests) && count($purchaseRequests) > 0)
                            @foreach($purchaseRequests as $pr)
                                <tr class="hover:bg-blue-50/30 transition-colors group">
                                    <td class="px-6 py-4">
                                        <span class="font-bold text-gray-800 text-sm font-mono">{{ $pr->pr_number }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        {{ \Carbon\Carbon::parse($pr->request_date)->format('M d, Y') }}
                                        <span class="text-xs text-gray-300 block">{{ \Carbon\Carbon::parse($pr->created_at)->diffForHumans() }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 font-medium">{{ $pr->department }}</td>
                                    <td class="px-6 py-4">
                                        @php
                                            $pClass = match($pr->priority) {
                                                'urgent' => 'text-red-600 bg-red-50 border-red-100',
                                                'high' => 'text-orange-600 bg-orange-50 border-orange-100',
                                                default => 'text-blue-600 bg-blue-50 border-blue-100'
                                            };
                                        @endphp
                                        <span class="px-2 py-1 rounded-lg text-xs font-bold border {{ $pClass }}">
                                            {{ ucfirst($pr->priority) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-bold text-gray-700">₱ {{ number_format($pr->total_estimated_cost, 2) }}</td>
                                    <td class="px-6 py-4 text-center">
                                        @php
                                            $sClass = match($pr->status) {
                                                'approved' => 'bg-green-100 text-green-700',
                                                'rejected' => 'bg-red-100 text-red-700',
                                                'pending' => 'bg-amber-100 text-amber-700',
                                                default => 'bg-gray-100 text-gray-600'
                                            };
                                            $sIcon = match($pr->status) {
                                                'approved' => 'fa-check',
                                                'rejected' => 'fa-times',
                                                'pending' => 'fa-clock',
                                                default => 'fa-circle'
                                            };
                                        @endphp
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $sClass }}">
                                            <i class="fas {{ $sIcon }} text-[10px]"></i> {{ ucfirst($pr->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex justify-end gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                                            <button onclick="PRManager.viewDetails({{ $pr->id }})" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-colors flex items-center justify-center"><i class="fas fa-eye text-xs"></i></button>
                                            @if($pr->status === 'pending' || $pr->status === 'draft')
                                                <button onclick="PRManager.cancelPR({{ $pr->id }})" class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-600 hover:text-white transition-colors flex items-center justify-center"><i class="fas fa-trash text-xs"></i></button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="7" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-3">
                                            <i class="fas fa-folder-open text-gray-300 text-2xl"></i>
                                        </div>
                                        <span class="text-gray-500 font-medium">No purchase requests found.</span>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
                @if(isset($purchaseRequests) && $purchaseRequests->hasPages())
                    <div class="p-4 border-t border-gray-200">
                        {{ $purchaseRequests->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- DETAILS MODAL --}}
<div id="detailsModalBackdrop" class="hidden fixed inset-0 z-[70] modal-backdrop">
    <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm" onclick="PRManager.closeDetails()"></div>
    <div class="flex items-center justify-center min-h-screen p-4">
        <div id="detailsModalPanel" class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl modal-panel overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="text-lg font-bold text-gray-800">Request Details</h3>
                <button onclick="PRManager.closeDetails()" class="text-gray-400 hover:text-gray-600 transition-colors"><i class="fas fa-times text-lg"></i></button>
            </div>
            <div id="detailsContent" class="p-0 modal-scroll overflow-y-auto max-h-[70vh]"></div>
        </div>
    </div>
</div>

{{-- TOAST & CONFIRM (Modernized) --}}
<div id="confirmModal" class="fixed inset-0 z-[80] hidden modal-backdrop">
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm"></div>
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-sm w-full p-8 text-center transform transition-all scale-100 border border-white/20">
            <div class="w-16 h-16 bg-orange-50 rounded-full flex items-center justify-center mx-auto mb-5 text-chocolate shadow-inner">
                <i class="fas fa-question text-2xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2" id="confirmTitle">Confirm</h3>
            <p class="text-gray-500 mb-8 leading-relaxed" id="confirmMessage">Are you sure you want to proceed?</p>
            <div class="grid grid-cols-2 gap-4">
                <button onclick="closeConfirmModal()" class="px-4 py-3 border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50 font-bold text-sm transition-colors">Cancel</button>
                <button id="confirmBtn" class="px-4 py-3 bg-gray-900 text-white rounded-xl hover:bg-chocolate hover:shadow-lg font-bold text-sm transition-all shadow-md">Yes, Confirm</button>
            </div>
        </div>
    </div>
</div>

<div id="toast" class="fixed top-5 right-5 z-[90] hidden transform transition-all duration-300 translate-y-[-20px] opacity-0">
    <div class="bg-white/90 backdrop-blur-md border border-gray-100 rounded-2xl shadow-2xl p-4 flex items-center gap-4 min-w-[320px]">
        <div id="toastIconContainer" class="w-10 h-10 rounded-full flex items-center justify-center shrink-0">
            <i id="toastIcon" class="fas fa-check"></i>
        </div>
        <div>
            <h4 class="text-sm font-bold text-gray-900" id="toastTitle">Notification</h4>
            <p class="text-xs text-gray-500 mt-0.5" id="toastMessage">Message details...</p>
        </div>
    </div>
</div>

<script>
// Keep existing JS Logic exactly intact, just updating UI references in methods if needed
// The backend interaction relies on IDs and Names which are preserved.

const PRManager = {
    cart: [],
    els: {
        cartContainer: document.getElementById('cartContainer'),
        cartCount: document.getElementById('cartCount'),
        cartTotal: document.getElementById('cartTotal'),
        totalItems: document.getElementById('totalItems'),
        submitBtn: document.getElementById('submitBtn'),
        emptyCart: document.getElementById('emptyCartMessage'),
        clearBtn: document.getElementById('clearCartBtn'), // Added ref
        
        history: {
            backdrop: document.getElementById('historyModalBackdrop'),
            panel: document.getElementById('historyModalPanel'),
            tableBody: document.getElementById('historyTableBody'),
            statusFilter: document.getElementById('historyStatusFilter'),
            departmentFilter: document.getElementById('historyDepartmentFilter'),
            searchInput: document.getElementById('historySearchInput')
        },
        details: {
            backdrop: document.getElementById('detailsModalBackdrop'),
            panel: document.getElementById('detailsModalPanel'),
            content: document.getElementById('detailsContent')
        }
    },

    initHistoryFilters() {
        // History filters are set up in setupListeners()
    },

    init() {
        this.setupListeners();
        this.loadCart();
        this.initHistoryFilters();
    },

    setupListeners() {
        if (this.els.history.statusFilter) this.els.history.statusFilter.addEventListener('change', () => this.filterHistory());
        if (this.els.history.departmentFilter) this.els.history.departmentFilter.addEventListener('change', () => this.filterHistory());
        if (this.els.history.searchInput) this.els.history.searchInput.addEventListener('input', this.debounce(() => this.filterHistory(), 300));
        
        // Filter logic for main catalog (Front-end filtering for demo, ideally backend)
        ['categoryFilter', 'stockFilter', 'priceFilter', 'searchInput'].forEach(id => {
            const el = document.getElementById(id);
            if(el) {
                el.addEventListener(id === 'searchInput' ? 'input' : 'change', () => this.filterCatalog());
            }
        });
    },

    // --- CATALOG FRONTEND FILTER (Improved with data attributes) ---
    filterCatalog() {
        const cat = document.getElementById('categoryFilter').value;
        const stock = document.getElementById('stockFilter').value;
        const price = document.getElementById('priceFilter').value;
        const search = document.getElementById('searchInput').value.toLowerCase().trim();
        
        const items = document.querySelectorAll('.item-card');
        let visibleCount = 0;

        items.forEach(card => {
            const itemCat = card.dataset.categoryId;
            const itemStock = card.dataset.stockStatus;
            const itemPrice = parseFloat(card.dataset.price);
            const itemName = card.dataset.name;
            const itemCode = card.dataset.code;
            const itemDesc = card.dataset.description;
            
            let show = true;
            
            // Category filter
            if (cat !== 'all' && itemCat !== cat) {
                show = false;
            }
            
            // Stock status filter
            if (stock !== 'all' && itemStock !== stock) {
                show = false;
            }
            
            // Price filter
            if (price !== 'all') {
                const priceMatch = this.matchesPriceFilter(itemPrice, price);
                if (!priceMatch) show = false;
            }
            
            // Search filter
            if (search && !itemName.includes(search) && !itemCode.includes(search) && !itemDesc.includes(search)) {
                show = false;
            }
            
            if (show) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        const noMsg = document.getElementById('noItemsMessage');
        const grid = document.getElementById('itemsGrid');
        
        if (visibleCount === 0) {
            noMsg.classList.remove('hidden');
            noMsg.classList.add('flex');
            grid.classList.add('hidden');
        } else {
            noMsg.classList.add('hidden');
            noMsg.classList.remove('flex');
            grid.classList.remove('hidden');
        }
    },
    
    matchesPriceFilter(price, filter) {
        switch(filter) {
            case '0-50': return price >= 0 && price <= 50;
            case '51-100': return price >= 51 && price <= 100;
            case '101-500': return price >= 101 && price <= 500;
            case '500+': return price > 500;
            default: return true;
        }
    },

    // --- CART LOGIC ---
    addToCart(id) {
        const itemElement = document.querySelector(`[data-item-id="${id}"]`);
        if (!itemElement) {
            console.error('Item not found:', id);
            return;
        }
        
        const itemName = itemElement.dataset.name;
        const itemPrice = parseFloat(itemElement.dataset.price);
        const itemCode = itemElement.dataset.code;
        
        const existing = this.cart.find(c => c.id === id);
        if (existing) {
            existing.qty++;
        } else {
            this.cart.push({ 
                id: id, 
                name: itemName.charAt(0).toUpperCase() + itemName.slice(1), // Capitalize first letter
                code: itemCode.toUpperCase(), 
                price: itemPrice, 
                qty: 1 
            });
        }
        
        this.updateCartUI();
        this.saveCart();
        showToast('Added to Slip', `${itemName} has been added.`);
        
        // Visual feedback on card
        itemElement.classList.add('ring-2', 'ring-chocolate', 'ring-offset-2');
        setTimeout(() => itemElement.classList.remove('ring-2', 'ring-chocolate', 'ring-offset-2'), 400);
    },

    updateCartUI() {
        this.els.cartContainer.innerHTML = '';
        
        if (this.cart.length === 0) {
            this.els.emptyCart.classList.remove('hidden');
            this.els.emptyCart.classList.add('flex');
            this.els.submitBtn.disabled = true;
            this.els.submitBtn.className = 'w-full py-4 bg-gray-100 text-gray-400 font-bold rounded-xl cursor-not-allowed transition-all flex items-center justify-center gap-2';
            this.els.submitBtn.innerHTML = '<span>Submit Request</span>';
            this.els.cartCount.textContent = 0;
            this.els.cartTotal.textContent = '₱ 0.00';
            this.els.totalItems.textContent = '0 items selected';
            if(this.els.clearBtn) this.els.clearBtn.style.display = 'none';
            return;
        }

        this.els.emptyCart.classList.add('hidden');
        this.els.emptyCart.classList.remove('flex');
        if(this.els.clearBtn) {
            this.els.clearBtn.style.display = 'inline-flex';
            this.els.clearBtn.style.opacity = '1';
            this.els.clearBtn.style.pointerEvents = 'auto';
        }
        
        this.els.submitBtn.disabled = false;
        this.els.submitBtn.className = 'w-full py-4 bg-gray-900 text-white font-bold rounded-xl hover:bg-chocolate hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 flex items-center justify-center gap-2 group relative overflow-hidden shadow-lg';
        this.els.submitBtn.innerHTML = '<span class="relative z-10">Submit Request</span><i class="fas fa-arrow-right relative z-10 transform group-hover:translate-x-1 transition-transform"></i><div class="absolute inset-0 bg-chocolate opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>';

        let total = 0;
        let totalItems = 0;

        this.cart.forEach((c, idx) => {
            total += c.price * c.qty;
            totalItems += c.qty;

            const row = document.createElement('div');
            row.className = 'bg-white p-3 rounded-2xl border border-gray-100 shadow-sm mb-3 animate-slide-up group';
            row.innerHTML = `
                <div class="flex justify-between items-start mb-2">
                    <div class="w-10/12">
                        <span class="text-sm font-bold text-gray-800 line-clamp-1 group-hover:text-chocolate transition-colors">${c.name}</span>
                        <span class="text-[10px] text-gray-400 font-mono block">${c.code}</span>
                    </div>
                    <button onclick="PRManager.removeCartItem(${idx})" class="text-gray-300 hover:text-red-500 transition-colors bg-gray-50 hover:bg-red-50 w-6 h-6 rounded-full flex items-center justify-center"><i class="fas fa-times text-xs"></i></button>
                </div>
                <div class="flex justify-between items-center mt-2">
                    <div class="text-xs font-bold text-gray-700">₱${(c.price * c.qty).toLocaleString('en-US', {minimumFractionDigits: 2})}</div>
                    <div class="flex items-center bg-gray-100 rounded-lg p-0.5">
                        <button onclick="PRManager.changeQty(${idx}, -1)" class="w-6 h-6 flex items-center justify-center text-gray-500 hover:text-chocolate hover:bg-white rounded-md transition-all text-xs font-bold shadow-sm hover:shadow">-</button>
                        <input type="number" min="1" step="1" value="${c.qty}" 
                               onchange="PRManager.updateCartItemQty(${idx}, this.value)" 
                               class="w-8 text-center text-xs font-bold bg-transparent border-none p-0 focus:ring-0 text-gray-800">
                        <button onclick="PRManager.changeQty(${idx}, 1)" class="w-6 h-6 flex items-center justify-center text-gray-500 hover:text-chocolate hover:bg-white rounded-md transition-all text-xs font-bold shadow-sm hover:shadow">+</button>
                    </div>
                </div>
            `;
            this.els.cartContainer.appendChild(row);
        });

        this.els.cartCount.textContent = this.cart.length;
        this.els.cartTotal.textContent = '₱ ' + total.toLocaleString('en-US', {minimumFractionDigits: 2});
        this.els.totalItems.textContent = `${totalItems} ${totalItems === 1 ? 'item' : 'items'} selected`;
    },

    updateCartItemQty(idx, qty) {
        const newQty = parseInt(qty);
        if (newQty > 0) {
            this.cart[idx].qty = newQty;
        } else {
            this.cart.splice(idx, 1);
        }
        this.updateCartUI();
        this.saveCart();
    },

    changeQty(idx, delta) {
        this.cart[idx].qty += delta;
        if (this.cart[idx].qty <= 0) {
            this.cart.splice(idx, 1);
        }
        this.updateCartUI();
        this.saveCart();
    },

    removeCartItem(idx) {
        this.cart.splice(idx, 1);
        this.updateCartUI();
        this.saveCart();
    },

    clearCart() {
        if (this.cart.length === 0) return;
        
        // For immediate clearing without confirmation (you can add confirmation back later)
        this.cart = [];
        this.updateCartUI();
        this.saveCart();
        showToast('Cleared', 'Requisition slip has been cleared.');
        
        // Uncomment below to use confirmation modal
        /*
        openConfirmModal('Clear Requisition Slip?', 'This will remove all items from your current slip.', () => {
            this.cart = [];
            this.updateCartUI();
            this.saveCart();
            showToast('Cleared', 'Requisition slip has been cleared.');
        });
        */
    },

    saveCart() { localStorage.setItem('emp_pr_cart', JSON.stringify(this.cart)); },
    loadCart() {
        const s = localStorage.getItem('emp_pr_cart');
        if (s) { 
            try { 
                const parsed = JSON.parse(s);
                // Validate cart data - filter out items with NaN prices
                this.cart = parsed.filter(item => {
                    return item && 
                           typeof item.id !== 'undefined' && 
                           typeof item.price === 'number' && 
                           !isNaN(item.price) &&
                           typeof item.qty === 'number' &&
                           !isNaN(item.qty);
                });
                this.updateCartUI(); 
            } catch(e) { 
                this.cart = []; 
                localStorage.removeItem('emp_pr_cart');
            } 
        }
    },

    // --- ACTIONS ---
    submitPR() {
        const dept = document.getElementById('deptInput').value.trim();
        const prio = document.getElementById('priorityInput').value;
        const date = document.getElementById('dateInput').value;
        const notes = document.getElementById('notesInput').value.trim();

        if (!dept) { 
            showToast('Missing Information', 'Please enter your department.', 'error'); 
            document.getElementById('deptInput').focus();
            return; 
        }
        if (this.cart.length === 0) {
            showToast('No Items', 'Please add items to your requisition slip.', 'error');
            return;
        }

        // For immediate submission without confirmation (you can add confirmation back later)
        const data = {
            department: dept,
            priority: prio,
            request_date: date,
            notes: notes,
            items: this.cart.map(c => ({ item_id: c.id, quantity_requested: c.qty, unit_price_estimate: c.price }))
        };

        // Loading UI
        this.els.submitBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Processing...';
        this.els.submitBtn.disabled = true;

        fetch('{{ route("inventory.purchase-requests.store") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(r => r.json())
        .then(res => {
            if(res.success) {
                this.cart = [];
                this.saveCart();
                this.updateCartUI();
                showToast('Success', 'Purchase Request submitted successfully!');
                document.getElementById('notesInput').value = '';
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast('Error', res.message || 'Failed to submit.', 'error');
                this.updateCartUI(); // Reset btn
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Error', 'System error occurred.', 'error');
            this.updateCartUI();
        });

        // Uncomment below to use confirmation modal
        /*
        openConfirmModal('Submit Request?', `Submit ${this.cart.length} items for approval?`, () => {
            // Same fetch code as above
        });
        */
    },

    cancelPR(id) {
        openConfirmModal('Cancel Request?', 'This action cannot be undone.', () => {
            fetch(`/inventory/purchase-requests/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(res => {
                if(res.success) {
                    showToast('Cancelled', 'Request removed.');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showToast('Error', 'Could not cancel.', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                showToast('Error', 'Failed to cancel request.', 'error');
            });
        });
    },

    viewDetails(id) {
        fetch(`/inventory/purchase-requests/${id}`)
            .then(r => r.json())
            .then(res => {
                if(res.success) {
                    const d = res.data;
                    let html = `
                        <div class="space-y-6">
                            <div class="grid grid-cols-2 gap-4 bg-gray-50 p-5 rounded-2xl border border-gray-100">
                                <div class="space-y-1">
                                    <span class="text-xs text-gray-500 uppercase tracking-wider font-bold">Reference</span>
                                    <div class="font-mono font-bold text-gray-900 text-lg">${d.pr_number}</div>
                                </div>
                                <div class="space-y-1 text-right">
                                    <span class="text-xs text-gray-500 uppercase tracking-wider font-bold">Total Cost</span>
                                    <div class="font-bold text-chocolate text-xl">${d.formatted_total}</div>
                                </div>
                                <div class="col-span-2 border-t border-gray-200 my-1"></div>
                                <div><span class="text-gray-400 text-xs block">Department</span><span class="font-medium text-gray-800">${d.department}</span></div>
                                <div><span class="text-gray-400 text-xs block">Date</span><span class="font-medium text-gray-800">${new Date(d.request_date).toLocaleDateString()}</span></div>
                                <div><span class="text-gray-400 text-xs block">Priority</span><span class="font-medium uppercase text-gray-800">${d.priority}</span></div>
                                <div><span class="text-gray-400 text-xs block">Status</span><span class="font-bold uppercase text-gray-800">${d.status}</span></div>
                            </div>
                            
                            <div>
                                <h4 class="font-bold text-gray-800 mb-3 text-sm">Items Requested</h4>
                                <div class="border border-gray-100 rounded-xl overflow-hidden">
                                    <table class="w-full text-sm text-left">
                                        <thead class="bg-gray-100 text-gray-500 font-bold text-xs uppercase">
                                            <tr><th class="p-3">Item</th><th class="p-3 text-right">Qty</th><th class="p-3 text-right">Unit Price</th><th class="p-3 text-right">Total</th></tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-50">
                                            ${d.items.map(i => `
                                                <tr class="bg-white">
                                                    <td class="p-3 font-medium text-gray-700">${i.item_name}</td>
                                                    <td class="p-3 text-right text-gray-500">${i.quantity_requested}</td>
                                                    <td class="p-3 text-right text-gray-500">₱${parseFloat(i.unit_price_estimate).toFixed(2)}</td>
                                                    <td class="p-3 text-right font-bold text-gray-700">₱${parseFloat(i.total_estimated_cost).toFixed(2)}</td>
                                                </tr>
                                            `).join('')}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            ${d.notes ? `<div class="bg-yellow-50 p-4 rounded-xl border border-yellow-100"><span class="text-yellow-700 text-xs font-bold uppercase block mb-1">Notes</span><p class="text-yellow-900 text-sm">${d.notes}</p></div>` : ''}
                        </div>
                    `;
                    this.els.details.content.innerHTML = html;
                    this.openModal('details');
                }
            })
            .catch(err => { console.error(err); showToast('Error', 'Failed to load details.', 'error'); });
    },

    // --- HELPERS ---
    filterHistory() {
        const status = this.els.history.statusFilter?.value || 'all';
        const department = this.els.history.departmentFilter?.value || 'all';
        const search = this.els.history.searchInput?.value.toLowerCase() || '';

        const rows = this.els.history.tableBody.querySelectorAll('tr');
        rows.forEach(row => {
            if (row.children.length < 7) return; 
            const prNumber = row.children[0].textContent.toLowerCase();
            const dept = row.children[2].textContent.toLowerCase();
            const rowStatus = row.children[5].textContent.toLowerCase();

            let showRow = true;
            if (status !== 'all' && !rowStatus.includes(status)) showRow = false;
            if (department !== 'all' && !dept.includes(department.toLowerCase())) showRow = false;
            if (search && !prNumber.includes(search) && !dept.includes(search)) showRow = false;
            row.style.display = showRow ? '' : 'none';
        });
    },

    refreshData() { location.reload(); },
    openHistory() { this.openModal('history'); },
    closeHistory() { this.closeModal('history'); },
    closeDetails() { this.closeModal('details'); },
    openModal(name) {
        const m = this.els[name];
        if (!m) return;
        m.backdrop.classList.remove('hidden');
        requestAnimationFrame(() => { m.backdrop.classList.add('active'); m.panel.classList.add('active'); });
    },
    closeModal(name) {
        const m = this.els[name];
        if (!m) return;
        m.backdrop.classList.remove('active');
        m.panel.classList.remove('active');
        setTimeout(() => m.backdrop.classList.add('hidden'), 300);
    },
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => { clearTimeout(timeout); func(...args); };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
};

let confirmCb = null;
function openConfirmModal(t, m, cb) {
    document.getElementById('confirmTitle').textContent = t;
    document.getElementById('confirmMessage').textContent = m;
    confirmCb = cb;
    document.getElementById('confirmModal').classList.remove('hidden');
}
function closeConfirmModal() { 
    document.getElementById('confirmModal').classList.add('hidden'); 
    confirmCb = null; 
}
document.getElementById('confirmBtn').onclick = () => { if(confirmCb) confirmCb(); closeConfirmModal(); };

function showToast(t, m, type='success') {
    const toast = document.getElementById('toast');
    const container = document.getElementById('toastIconContainer');
    const icon = document.getElementById('toastIcon');
    
    document.getElementById('toastTitle').textContent = t;
    document.getElementById('toastMessage').textContent = m;

    if(type === 'error') {
        container.className = 'w-10 h-10 rounded-full flex items-center justify-center shrink-0 bg-red-100 text-red-500';
        icon.className = 'fas fa-times';
    } else {
        container.className = 'w-10 h-10 rounded-full flex items-center justify-center shrink-0 bg-green-100 text-green-500';
        icon.className = 'fas fa-check';
    }

    toast.classList.remove('hidden');
    setTimeout(() => toast.classList.remove('translate-y-[-20px]', 'opacity-0'), 10);
    setTimeout(() => {
        toast.classList.add('translate-y-[-20px]', 'opacity-0');
        setTimeout(() => toast.classList.add('hidden'), 300);
    }, 3000);
}

document.addEventListener('DOMContentLoaded', () => { PRManager.init(); });
</script>
@endsection