@extends('Inventory.layout.app')

@section('content')
<div class="flex flex-col lg:flex-row gap-6 h-[calc(100vh-7rem)] pb-4 font-sans text-gray-600">

    {{-- LEFT COLUMN: REPLENISHMENT NEEDED --}}
    <div class="flex-1 flex flex-col bg-white border border-border-soft rounded-2xl shadow-sm overflow-hidden h-full">
        
        {{-- Header --}}
        <div class="px-6 py-4 border-b border-border-soft bg-gradient-to-r from-red-50 to-amber-50">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-red-100 border border-red-200 rounded-xl flex items-center justify-center">
                        <i class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div>
                        <h1 class="font-display text-xl font-bold text-gray-900">Replenishment Needed</h1>
                        <p class="text-sm text-gray-600">Items below reorder point</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    <div class="px-3 py-1.5 bg-red-100 text-red-700 rounded-lg border border-red-200 text-sm font-medium">
                        {{ $lowStockItems->count() }} items need attention
                    </div>
                    <div class="px-3 py-1.5 bg-amber-100 text-amber-700 rounded-lg border border-amber-200 text-sm font-medium">
                        {{ $stats['pending'] }} pending requests
                    </div>
                </div>
            </div>
        </div>

        {{-- Low Stock Items Table --}}
        <div class="flex-1 overflow-y-auto p-6 bg-gray-50/50 custom-scrollbar">
            @if($lowStockItems->count() > 0)
                <div class="bg-white rounded-xl border border-border-soft overflow-hidden shadow-sm">
                    <table class="min-w-full">
                        <thead class="bg-gray-50 border-b border-border-soft">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Item</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Current Stock</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Reorder Point</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Suggested Qty</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($lowStockItems as $item)
                                <tr class="hover:bg-amber-25 transition-colors">
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center text-amber-600 border border-amber-200">
                                                <i class="fas fa-box text-sm"></i>
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900 text-sm">{{ $item->name }}</div>
                                                <div class="text-xs text-gray-500 font-mono">{{ $item->item_code }} • {{ $item->category }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="text-sm font-bold text-red-600">{{ number_format($item->current_stock, 1) }} {{ $item->unit }}</div>
                                        @if($item->current_stock <= 0)
                                            <div class="text-xs text-red-500 font-medium">OUT OF STOCK</div>
                                        @else
                                            <div class="text-xs text-gray-500">Below reorder point</div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="text-sm text-gray-700">{{ number_format($item->reorder_point, 1) }} {{ $item->unit }}</div>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="text-sm font-bold text-green-600">{{ $item->suggested_qty }} {{ $item->unit }}</div>
                                        <div class="text-xs text-gray-500">Max: {{ number_format($item->max_stock_level, 1) }} {{ $item->unit }}</div>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <button onclick="addToDraft({{ $item->id }})" 
                                                class="px-4 py-2 bg-chocolate text-white rounded-lg hover:bg-chocolate-dark transition-colors text-sm font-medium shadow-sm">
                                            <i class="fas fa-plus mr-1"></i> Add
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="flex flex-col items-center justify-center h-full text-center py-16">
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-check-circle text-3xl text-green-500"></i>
                    </div>
                    <h3 class="font-display text-xl font-bold text-green-700">All Items Stocked!</h3>
                    <p class="text-sm text-gray-500 mt-1">No items currently need replenishment.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- RIGHT COLUMN: DRAFT REQUEST --}}
    <div class="w-full lg:w-96 flex flex-col bg-white border border-border-soft rounded-2xl shadow-xl overflow-hidden h-full shrink-0">
        
        {{-- Header --}}
        <div class="p-6 border-b border-border-soft bg-chocolate text-white relative overflow-hidden">
            <div class="relative z-10 flex items-center justify-between">
                <div>
                    <h2 class="font-display text-xl font-bold">Draft Request</h2>
                    <p class="text-xs text-white/70 mt-0.5">{{ date('F d, Y') }}</p>
                </div>
                <div class="text-right">
                    <span class="text-xs text-white/70 block mb-0.5">Items</span>
                    <div class="text-2xl font-bold text-caramel" id="draftCount">0</div>
                </div>
            </div>
            {{-- Decorative element --}}
            <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full"></div>
        </div>

        {{-- Draft Items --}}
        <div class="flex-1 overflow-y-auto p-4 custom-scrollbar bg-cream-bg" id="draftContainer">
            <div id="emptyDraftMessage" class="flex flex-col items-center justify-center h-full text-center opacity-50 py-12">
                <div class="w-20 h-20 border-2 border-dashed border-chocolate/30 rounded-2xl flex items-center justify-center mb-4">
                    <i class="fas fa-clipboard-list text-3xl text-chocolate/40"></i>
                </div>
                <p class="text-sm font-bold text-chocolate">Draft is empty</p>
                <p class="text-xs text-gray-500 mt-1">Add items from replenishment list</p>
            </div>
        </div>

        {{-- Form and Actions --}}
        <div class="p-5 border-t border-border-soft bg-white shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] relative z-20">
            <div class="space-y-4 mb-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1">Priority</label>
                    <select name="priority" id="priorityInput" class="w-full border border-gray-300 rounded-xl px-3 py-2.5 focus:ring-2 focus:ring-caramel/20 focus:border-caramel text-sm bg-white cursor-pointer">
                        <option value="normal">Normal</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
                <textarea name="notes" id="notesInput" rows="2" 
                          placeholder="Add notes or justification (optional)..." 
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-caramel/20 focus:border-caramel resize-none text-sm transition-all placeholder-gray-400"></textarea>
            </div>

            {{-- Total and Submit --}}
            <div class="space-y-4 pt-2 border-t border-gray-100">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-500 uppercase tracking-wide">Total Estimate</span>
                    <span class="text-xl font-bold text-chocolate" id="draftTotal">₱ 0.00</span>
                </div>
                
                <div class="grid grid-cols-2 gap-3">
                    <button type="button" onclick="clearDraft()" id="clearBtn" disabled
                            class="py-2.5 bg-red-50 text-red-600 font-bold rounded-xl cursor-not-allowed transition-all shadow-sm flex items-center justify-center gap-2 hover:bg-red-100 border border-red-200">
                        <i class="fas fa-trash-alt"></i> Clear
                    </button>
                    <button type="button" onclick="submitDraft()" id="submitBtn" disabled 
                            class="py-2.5 bg-gray-100 text-gray-400 font-bold rounded-xl cursor-not-allowed transition-all shadow-sm flex items-center justify-center gap-2">
                        <i class="fas fa-paper-plane"></i> Submit
                    </button>
                </div>
                <p class="text-center text-[10px] text-gray-400 font-medium" id="totalItems">0 items selected</p>
            </div>
        </div>
    </div>
</div>

{{-- HISTORY SECTION (Simplified) --}}
<div class="mt-6 bg-white border border-border-soft rounded-2xl shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-border-soft bg-gray-50">
        <div class="flex items-center justify-between">
            <h3 class="font-display text-lg font-bold text-gray-900">Recent Requests</h3>
        </div>
    </div>
    
    <div class="overflow-x-auto">
        @if($recentRequests->count() > 0)
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">PR Number</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Items</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($recentRequests as $request)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-mono font-bold text-chocolate text-sm">{{ $request->pr_number }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ \Carbon\Carbon::parse($request->request_date)->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ $request->purchaseRequestItems->count() }} items
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-chocolate">
                                ₱ {{ number_format($request->total_estimated_cost, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @php
                                    $statusClass = match($request->status) {
                                        'approved' => 'bg-green-100 text-green-800 border-green-200',
                                        'rejected' => 'bg-red-100 text-red-800 border-red-200',
                                        'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
                                        default => 'bg-gray-100 text-gray-800 border-gray-200'
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wide border {{ $statusClass }}">
                                    {{ ucfirst($request->status) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="px-6 py-12 text-center">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-folder-open text-gray-400 text-2xl"></i>
                </div>
                <span class="text-gray-500 font-medium">No recent requests found.</span>
            </div>
        @endif
    </div>
</div>

<script>
// Replenishment-focused JavaScript
const ReplenishmentManager = {
    draft: [],
    
    init() {
        this.loadDraft();
        this.updateDraftUI();
    },

    addToDraft(itemId) {
        // Find item in low stock items
        const itemData = @json($lowStockItems->toJson());
        const item = itemData.find(i => i.id === itemId);
        
        if (!item) return;
        
        // Check if already in draft
        const existing = this.draft.find(d => d.id === itemId);
        if (existing) {
            existing.qty += item.suggested_qty;
        } else {
            this.draft.push({
                id: itemId,
                name: item.name,
                code: item.item_code,
                price: item.cost_price,
                qty: item.suggested_qty,
                unit: item.unit,
                suggested_qty: item.suggested_qty
            });
        }
        
        this.saveDraft();
        this.updateDraftUI();
        
        // Show toast notification
        showToast('Added to Draft', `${item.name} (${item.suggested_qty} ${item.unit}) added to draft request.`);
    },

    updateDraftUI() {
        const container = document.getElementById('draftContainer');
        const countEl = document.getElementById('draftCount');
        const totalEl = document.getElementById('draftTotal');
        const totalItemsEl = document.getElementById('totalItems');
        const submitBtn = document.getElementById('submitBtn');
        const clearBtn = document.getElementById('clearBtn');
        const emptyMsg = document.getElementById('emptyDraftMessage');
        
        container.innerHTML = '';
        
        if (this.draft.length === 0) {
            emptyMsg.classList.remove('hidden');
            submitBtn.disabled = true;
            clearBtn.disabled = true;
            submitBtn.className = 'py-2.5 bg-gray-100 text-gray-400 font-bold rounded-xl cursor-not-allowed transition-all shadow-sm flex items-center justify-center gap-2';
            clearBtn.className = 'py-2.5 bg-red-50 text-red-400 font-bold rounded-xl cursor-not-allowed transition-all shadow-sm flex items-center justify-center gap-2 border border-red-100';
            countEl.textContent = '0';
            totalEl.textContent = '₱ 0.00';
            totalItemsEl.textContent = '0 items selected';
            return;
        }
        
        emptyMsg.classList.add('hidden');
        submitBtn.disabled = false;
        clearBtn.disabled = false;
        submitBtn.className = 'py-2.5 bg-chocolate text-white font-bold rounded-xl hover:bg-chocolate-dark transition-all shadow-sm flex items-center justify-center gap-2';
        clearBtn.className = 'py-2.5 bg-red-50 text-red-600 font-bold rounded-xl hover:bg-red-100 transition-all shadow-sm flex items-center justify-center gap-2 border border-red-200 hover:border-red-300';
        
        let total = 0;
        let totalItems = 0;
        
        this.draft.forEach((item, idx) => {
            total += item.price * item.qty;
            totalItems += item.qty;
            
            const row = document.createElement('div');
            row.className = 'bg-white border border-border-soft rounded-xl p-4 mb-3 shadow-sm group hover:border-caramel/30 transition-colors';
            row.innerHTML = `
                <div class="flex justify-between items-start mb-2">
                    <div class="flex-1 pr-2">
                        <div class="flex items-center mb-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide bg-blue-100 text-blue-800 border border-blue-200 mr-2">
                                <i class="fas fa-lightbulb text-[8px] mr-1"></i>Suggested
                            </span>
                            <span class="text-sm font-bold text-gray-900 block leading-tight">${item.name}</span>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-gray-400 font-mono">
                            <span>${item.code}</span>
                            <span class="text-blue-600 font-medium">Suggested: ${item.suggested_qty} ${item.unit}</span>
                        </div>
                    </div>
                    <button onclick="ReplenishmentManager.removeFromDraft(${idx})" class="text-gray-300 hover:text-red-500 transition-colors p-1 rounded-full hover:bg-red-50">
                        <i class="fas fa-times text-xs"></i>
                    </button>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm font-bold text-chocolate">₱${(item.price * item.qty).toFixed(2)}</span>
                    <div class="flex items-center bg-cream-bg rounded-lg border border-border-soft">
                        <button onclick="ReplenishmentManager.changeQty(${idx}, -1)" class="w-7 h-7 flex items-center justify-center text-gray-500 hover:text-chocolate transition-colors"><i class="fas fa-minus text-[10px]"></i></button>
                        <input type="number" min="1" step="1" value="${item.qty}" onchange="ReplenishmentManager.updateQty(${idx}, this.value)" class="w-10 text-center text-xs font-bold bg-transparent border-none focus:ring-0 p-0 text-gray-800">
                        <button onclick="ReplenishmentManager.changeQty(${idx}, 1)" class="w-7 h-7 flex items-center justify-center text-gray-500 hover:text-chocolate transition-colors"><i class="fas fa-plus text-[10px]"></i></button>
                    </div>
                </div>
            `;
            container.appendChild(row);
        });
        
        countEl.textContent = this.draft.length;
        totalEl.textContent = '₱ ' + total.toFixed(2);
        totalItemsEl.textContent = `${totalItems} ${totalItems === 1 ? 'item' : 'items'} selected`;
    },

    changeQty(idx, delta) {
        this.draft[idx].qty += delta;
        if (this.draft[idx].qty <= 0) {
            this.draft.splice(idx, 1);
        }
        this.saveDraft();
        this.updateDraftUI();
    },

    updateQty(idx, qty) {
        const newQty = parseInt(qty);
        if (newQty > 0) {
            this.draft[idx].qty = newQty;
        } else {
            this.draft.splice(idx, 1);
        }
        this.saveDraft();
        this.updateDraftUI();
    },

    removeFromDraft(idx) {
        this.draft.splice(idx, 1);
        this.saveDraft();
        this.updateDraftUI();
    },

    clearDraft() {
        if (this.draft.length === 0) return;
        
        if (confirm('Clear all items from draft?')) {
            this.draft = [];
            this.saveDraft();
            this.updateDraftUI();
            showToast('Draft Cleared', 'All items removed from draft request.');
        }
    },

    submitDraft() {
        const priority = document.getElementById('priorityInput').value;
        const notes = document.getElementById('notesInput').value.trim();
        
        if (this.draft.length === 0) {
            showToast('No Items', 'Please add items to your draft request.', 'error');
            return;
        }
        
        const data = {
            department: 'Inventory',
            priority: priority,
            request_date: '{{ date("Y-m-d") }}',
            notes: notes,
            items: this.draft.map(item => ({
                item_id: item.id,
                quantity_requested: item.qty,
                unit_price_estimate: item.price
            }))
        };
        
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        submitBtn.disabled = true;
        
        fetch('{{ route("inventory.purchase-requests.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                this.draft = [];
                this.saveDraft();
                this.updateDraftUI();
                document.getElementById('notesInput').value = '';
                showToast('Success', 'Purchase request submitted successfully!');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast('Error', result.message || 'Failed to submit request.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error', 'System error occurred.', 'error');
        })
        .finally(() => {
            submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit';
            submitBtn.disabled = false;
        });
    },

    saveDraft() {
        localStorage.setItem('replenishment_draft', JSON.stringify(this.draft));
    },

    loadDraft() {
        const saved = localStorage.getItem('replenishment_draft');
        if (saved) {
            try {
                this.draft = JSON.parse(saved);
            } catch (e) {
                this.draft = [];
                localStorage.removeItem('replenishment_draft');
            }
        }
    }
};

// Global functions for onclick handlers
function addToDraft(itemId) {
    ReplenishmentManager.addToDraft(itemId);
}

function clearDraft() {
    ReplenishmentManager.clearDraft();
}

function submitDraft() {
    ReplenishmentManager.submitDraft();
}

// Toast notification system
function showToast(title, message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `fixed top-5 right-5 z-50 transform transition-all duration-300 translate-x-full`;
    
    const bgColor = type === 'error' ? 'border-red-400 bg-red-50' : 'border-green-400 bg-green-50';
    const iconColor = type === 'error' ? 'text-red-500' : 'text-green-500';
    const icon = type === 'error' ? 'fas fa-exclamation-circle' : 'fas fa-check-circle';
    
    toast.innerHTML = `
        <div class="bg-white border-l-4 ${bgColor} rounded-lg shadow-xl p-4 flex items-center gap-4 min-w-[320px] ring-1 ring-black/5">
            <div class="w-10 h-10 rounded-full flex items-center justify-center bg-white border ${iconColor}">
                <i class="${icon} text-lg"></i>
            </div>
            <div>
                <h4 class="text-sm font-bold text-gray-900">${title}</h4>
                <p class="text-xs text-gray-500 mt-0.5 font-medium">${message}</p>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-auto text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
    }, 100);
    
    setTimeout(() => {
        toast.classList.add('translate-x-full');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => ReplenishmentManager.init());
} else {
    ReplenishmentManager.init();
}
</script>

<style>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background-color: #e8dfd4; border-radius: 20px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background-color: #c48d3f; }
</style>
@endsection