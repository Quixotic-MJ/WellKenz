@extends('Admin.layout.app')

@section('content')
<div class="space-y-8 font-sans text-gray-600">

    {{-- 1. HEADER & ACTIONS --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <h1 class="font-display text-3xl font-bold text-chocolate mb-2">Unit Configurations</h1>
            <p class="text-sm text-gray-500">Define the standard measurements (Base) and container types (Packaging) used across the system.</p>
        </div>
        <div>
            <button onclick="openUnitModal()" 
                class="inline-flex items-center justify-center px-5 py-2.5 bg-chocolate text-white text-sm font-bold rounded-lg hover:bg-chocolate-dark transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                <i class="fas fa-plus mr-2"></i> Add New Unit
            </button>
        </div>
    </div>

    {{-- 2. INFORMATION CARD --}}
    <div class="bg-cream-bg border border-border-soft rounded-xl p-5 shadow-sm relative overflow-hidden">
        {{-- Decorative Icon --}}
        <div class="absolute top-0 right-0 -mt-2 -mr-2 opacity-10">
            <i class="fas fa-balance-scale text-6xl text-chocolate"></i>
        </div>
        
        <div class="flex items-start gap-4 relative z-10">
            <div class="flex-shrink-0 mt-1">
                <div class="w-8 h-8 rounded-full bg-white border border-border-soft flex items-center justify-center text-caramel">
                    <i class="fas fa-info text-sm"></i>
                </div>
            </div>
            <div>
                <h3 class="font-display text-lg font-bold text-chocolate">System Logic</h3>
                <div class="mt-2 text-sm text-gray-600 space-y-1">
                    <p><strong class="text-chocolate">Base Units (Weight/Volume/Count):</strong> Used for <span class="italic font-medium">Recipes</span> and <span class="italic font-medium">Inventory Counting</span> (e.g., Grams, Liters).</p>
                    <p><strong class="text-chocolate">Packaging Units:</strong> Used for <span class="italic font-medium">Purchasing</span> and <span class="italic font-medium">Delivery</span> (e.g., Sacks, Boxes).</p>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. FILTERS --}}
    <div class="bg-white border border-border-soft rounded-xl p-6 shadow-sm">
        <form method="GET" action="{{ route('admin.units.index') }}" class="flex flex-col md:flex-row gap-4 items-end">
            <div class="flex-1 w-full">
                <label for="search" class="block text-sm font-bold text-chocolate mb-1">Search Units</label>
                <div class="relative group">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400 group-focus-within:text-caramel transition-colors"></i>
                    </div>
                    <input type="text" 
                        id="search" 
                        name="search" 
                        value="{{ request('search') }}"
                        placeholder="Search by name or symbol..." 
                        class="block w-full pl-10 pr-3 py-2.5 border border-gray-200 bg-cream-bg rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel transition-all">
                </div>
            </div>
            
            <div class="w-full md:w-48">
                <label for="type" class="block text-sm font-bold text-chocolate mb-1">Type</label>
                <div class="relative">
                    <select id="type" name="type" class="block w-full py-2.5 px-3 border border-gray-200 bg-cream-bg rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel appearance-none cursor-pointer">
                        <option value="">All Types</option>
                        <option value="weight" {{ request('type') == 'weight' ? 'selected' : '' }}>Weight</option>
                        <option value="volume" {{ request('type') == 'volume' ? 'selected' : '' }}>Volume</option>
                        <option value="piece" {{ request('type') == 'piece' ? 'selected' : '' }}>Count/Piece</option>
                        <option value="length" {{ request('type') == 'length' ? 'selected' : '' }}>Length</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>

            <div class="w-full md:w-48">
                <label for="status" class="block text-sm font-bold text-chocolate mb-1">Status</label>
                <div class="relative">
                    <select id="status" name="status" class="block w-full py-2.5 px-3 border border-gray-200 bg-cream-bg rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-caramel/20 focus:border-caramel appearance-none cursor-pointer">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                        <i class="fas fa-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="px-5 py-2.5 bg-chocolate text-white rounded-lg hover:bg-chocolate-dark transition-all shadow-md font-medium text-sm">
                    Filter
                </button>
                <a href="{{ route('admin.units.index') }}" class="px-5 py-2.5 bg-white border border-border-soft text-chocolate rounded-lg hover:bg-cream-bg hover:text-caramel transition-all shadow-sm font-medium text-sm">
                    Clear
                </a>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        
        {{-- 4. LEFT COL: STANDARD BASE UNITS --}}
        <div class="space-y-4">
            <h3 class="font-display text-xl font-bold text-chocolate flex items-center border-b border-border-soft pb-2">
                <span class="w-8 h-8 rounded-lg bg-chocolate text-white flex items-center justify-center mr-3 text-sm shadow-sm">
                    <i class="fas fa-ruler-combined"></i>
                </span>
                Standard Base Units
            </h3>
            <div class="bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-border-soft">
                        <thead class="bg-cream-bg">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Unit</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Symbol</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Type</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-caramel uppercase tracking-widest font-display">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-soft bg-white">
                            @forelse($baseUnits as $unit)
                            <tr class="hover:bg-cream-bg/50 transition-colors group">
                                <td class="px-4 py-3 text-sm font-bold text-chocolate">{{ $unit->name }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="bg-cream-bg border border-border-soft text-chocolate px-2 py-1 rounded text-xs font-mono font-medium">{{ $unit->symbol }}</span>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-500 capitalize">{{ $unit->type }}</td>
                                <td class="px-4 py-3 text-right text-sm font-medium">
                                    @if(in_array($unit->name, ['Kilogram', 'Gram', 'Liter', 'Milliliter', 'Piece']))
                                        <span class="text-xs text-gray-400 italic flex items-center justify-end">
                                            <i class="fas fa-lock mr-1"></i> System
                                        </span>
                                    @else
                                        <div class="flex justify-end gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                                            <button onclick="editUnit({{ $unit->id }})" class="text-chocolate hover:text-caramel tooltip" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button onclick="confirmToggleStatus({{ $unit->id }}, '{{ $unit->is_active ? 'active' : 'inactive' }}')" class="text-amber-600 hover:text-amber-700 tooltip" title="Toggle Status">
                                                <i class="fas fa-{{ $unit->is_active ? 'eye-slash' : 'eye' }}"></i>
                                            </button>
                                            <button onclick="confirmDelete({{ $unit->id }})" class="text-red-600 hover:text-red-800 tooltip" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                    <p>No base units found</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- 5. RIGHT COL: PACKAGING UNITS --}}
        <div class="space-y-4">
            <h3 class="font-display text-xl font-bold text-chocolate flex items-center border-b border-border-soft pb-2">
                <span class="w-8 h-8 rounded-lg bg-caramel text-white flex items-center justify-center mr-3 text-sm shadow-sm">
                    <i class="fas fa-box-open"></i>
                </span>
                Packaging / Purchase Units
            </h3>
            <div class="bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-border-soft">
                        <thead class="bg-cream-bg">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Unit</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-caramel uppercase tracking-widest font-display">Base</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-caramel uppercase tracking-widest font-display">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-caramel uppercase tracking-widest font-display">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border-soft bg-white">
                            @forelse($packagingUnits as $unit)
                            <tr class="hover:bg-cream-bg/50 transition-colors group">
                                <td class="px-4 py-3">
                                    <div class="text-sm font-bold text-chocolate">{{ $unit->name }}</div>
                                    <div class="text-xs text-gray-400 font-mono mt-0.5">{{ $unit->symbol }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600">
                                    @if($unit->baseUnit)
                                        <span class="flex items-center gap-1">
                                            <i class="fas fa-arrow-right text-[10px] text-caramel"></i>
                                            {{ $unit->baseUnit->name }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide {{ $unit->is_active ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' }}">
                                        {{ $unit->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-medium">
                                    <div class="flex justify-end gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
                                        <button onclick="editUnit({{ $unit->id }})" class="text-chocolate hover:text-caramel tooltip" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button onclick="confirmToggleStatus({{ $unit->id }}, '{{ $unit->is_active ? 'active' : 'inactive' }}')" class="text-amber-600 hover:text-amber-700 tooltip" title="Toggle Status">
                                            <i class="fas fa-{{ $unit->is_active ? 'eye-slash' : 'eye' }}"></i>
                                        </button>
                                        <button onclick="confirmDelete({{ $unit->id }})" class="text-red-600 hover:text-red-800 tooltip" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-500">
                                    <p class="mb-2">No packaging units found</p>
                                    <button onclick="openUnitModal()" class="text-caramel hover:text-chocolate text-sm font-bold underline decoration-caramel/30 underline-offset-2">Add First Unit</button>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    {{-- 6. PAGINATION --}}
    @if($units instanceof \Illuminate\Pagination\LengthAwarePaginator)
    <div class="bg-white px-6 py-4 border-t border-border-soft rounded-xl border">
        {{ $units->appends(request()->query())->links() }}
    </div>
    @endif

</div>

{{-- ------------------- UI COMPONENTS ------------------- --}}

{{-- A. CREATE/EDIT UNIT MODAL --}}
<div id="unitModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity backdrop-blur-sm" onclick="closeUnitModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-border-soft">
            <div class="bg-chocolate px-6 py-4">
                <h3 class="font-display text-lg font-bold text-white" id="modal-title">Add New Unit</h3>
            </div>
            
            <form id="unitForm">
                @csrf
                <div class="bg-white px-6 pt-6 pb-6">
                    <div class="space-y-5">
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-bold text-chocolate mb-1">Unit Name</label>
                                <input type="text" name="name" id="unitName" required
                                       class="block w-full border-gray-200 bg-cream-bg rounded-lg shadow-sm py-2 px-3 focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm transition-all" 
                                       placeholder="e.g. Box">
                            </div>
                            <div>
                                <label class="block text-sm font-bold text-chocolate mb-1">Symbol</label>
                                <input type="text" name="symbol" id="unitSymbol" required
                                       class="block w-full border-gray-200 bg-cream-bg rounded-lg shadow-sm py-2 px-3 focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm transition-all" 
                                       placeholder="e.g. bx">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-chocolate mb-1">Unit Type</label>
                            <select name="type" id="unitType" required
                                    class="block w-full border-gray-200 bg-cream-bg rounded-lg shadow-sm py-2 px-3 focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm transition-all">
                                <option value="">Select Type</option>
                                <option value="weight">Weight</option>
                                <option value="volume">Volume</option>
                                <option value="piece">Count/Piece</option>
                                <option value="length">Length</option>
                            </select>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-lg border border-border-soft">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Conversion Logic</h4>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-bold text-chocolate mb-1">Base Unit (Optional)</label>
                                    <select name="base_unit_id" id="baseUnitSelect"
                                            class="block w-full border-gray-200 bg-white rounded-lg shadow-sm py-2 px-3 focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm transition-all">
                                        <option value="">No base unit</option>
                                    </select>
                                    <p class="mt-1 text-xs text-gray-500">If this is a packaging unit (e.g. Sack), what is it made of?</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-chocolate mb-1">Conversion Factor</label>
                                    <div class="relative">
                                        <input type="number" name="conversion_factor" id="conversionFactor"
                                               step="0.000001" min="0.000001" value="1.000000"
                                               class="block w-full border-gray-200 bg-white rounded-lg shadow-sm py-2 px-3 focus:ring-2 focus:ring-caramel/20 focus:border-caramel sm:text-sm transition-all">
                                    </div>
                                    <p class="mt-1 text-xs text-gray-500">How many base units are in this unit?</p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse border-t border-border-soft">
                    <button type="submit" id="saveUnitBtn"
                            class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-md px-4 py-2 bg-chocolate text-base font-bold text-white hover:bg-chocolate-dark focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-all">
                        Save Unit
                    </button>
                    <button type="button" onclick="closeUnitModal()" 
                            class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-bold text-gray-700 hover:bg-cream-bg hover:text-chocolate focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-all">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- B. CONFIRMATION MODAL --}}
<div id="confirmationModal" class="hidden fixed inset-0 z-[60] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-50 transition-opacity backdrop-blur-sm" onclick="closeConfirmation()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-border-soft">
            <div class="bg-white px-6 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div id="confIconContainer" class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i id="confIcon" class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-bold text-chocolate font-display" id="confTitle">Confirmation</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500" id="confMessage">Are you sure you want to proceed?</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-3 sm:flex sm:flex-row-reverse border-t border-border-soft">
                <button type="button" id="confConfirmBtn" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-md px-4 py-2 bg-red-600 text-base font-bold text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-all">
                    Confirm
                </button>
                <button type="button" onclick="closeConfirmation()" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-bold text-gray-700 hover:bg-cream-bg hover:text-chocolate focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-all">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

{{-- C. TOAST NOTIFICATION --}}
<div id="toast" class="hidden fixed top-5 right-5 z-[70] max-w-sm w-full bg-white shadow-xl rounded-xl pointer-events-auto border border-border-soft overflow-hidden transform transition-all duration-300 ease-out translate-y-2 opacity-0">
    <div class="p-4 bg-cream-bg">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <i id="toastIcon" class="fas fa-check-circle text-green-500 text-xl"></i>
            </div>
            <div class="ml-3 w-0 flex-1 pt-0.5">
                <p id="toastTitle" class="text-sm font-bold text-chocolate">Successfully saved!</p>
                <p id="toastMessage" class="mt-1 text-sm text-gray-500"></p>
            </div>
            <div class="ml-4 flex-shrink-0 flex">
                <button onclick="hideToast()" class="inline-flex text-gray-400 hover:text-chocolate focus:outline-none transition-colors">
                    <span class="sr-only">Close</span>
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
let currentEditingUnitId = null;
let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
let confirmActionCallback = null;

/* ===========================
   UI HELPERS (TOAST & MODALS)
   =========================== */

function showToast(title, message, type = 'success') {
    const toast = document.getElementById('toast');
    const toastTitle = document.getElementById('toastTitle');
    const toastMsg = document.getElementById('toastMessage');
    const toastIcon = document.getElementById('toastIcon');

    toastTitle.textContent = title;
    toastMsg.textContent = message || '';

    // Reset classes
    toastIcon.className = 'fas text-xl';
    
    if(type === 'success') {
        toastIcon.classList.add('fa-check-circle', 'text-green-500');
    } else if(type === 'error') {
        toastIcon.classList.add('fa-times-circle', 'text-red-500');
    } else {
        toastIcon.classList.add('fa-info-circle', 'text-blue-500');
    }

    // Show animation
    toast.classList.remove('hidden');
    // Trigger reflow
    void toast.offsetWidth;
    
    // Fade in
    toast.classList.remove('translate-y-2', 'opacity-0');
    
    // Auto hide
    setTimeout(() => {
        hideToast();
    }, 3000);
}

function hideToast() {
    const toast = document.getElementById('toast');
    toast.classList.add('translate-y-2', 'opacity-0');
    setTimeout(() => {
        toast.classList.add('hidden');
    }, 300);
}

function openConfirmation(title, message, type, callback) {
    const modal = document.getElementById('confirmationModal');
    const titleEl = document.getElementById('confTitle');
    const msgEl = document.getElementById('confMessage');
    const btn = document.getElementById('confConfirmBtn');
    const iconContainer = document.getElementById('confIconContainer');
    const icon = document.getElementById('confIcon');

    titleEl.textContent = title;
    msgEl.textContent = message;
    confirmActionCallback = callback;

    // Style based on type (danger vs warning)
    if (type === 'danger') {
        btn.className = "w-full inline-flex justify-center rounded-lg border border-transparent shadow-md px-4 py-2 bg-red-600 text-base font-bold text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-all";
        iconContainer.className = "mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10";
        icon.className = "fas fa-trash text-red-600";
        btn.textContent = "Delete";
    } else {
        // Warning/Toggle - Use Caramel theme
        btn.className = "w-full inline-flex justify-center rounded-lg border border-transparent shadow-md px-4 py-2 bg-caramel text-base font-bold text-white hover:bg-chocolate focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-all";
        iconContainer.className = "mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-amber-100 sm:mx-0 sm:h-10 sm:w-10";
        icon.className = "fas fa-exclamation-triangle text-amber-600";
        btn.textContent = "Update Status";
    }

    modal.classList.remove('hidden');
}

function closeConfirmation() {
    document.getElementById('confirmationModal').classList.add('hidden');
    confirmActionCallback = null;
}

// Attach click handler to confirm button
document.getElementById('confConfirmBtn').addEventListener('click', function() {
    if (confirmActionCallback) {
        confirmActionCallback();
    }
    closeConfirmation();
});

/* ===========================
   UNIT LOGIC
   =========================== */

function openUnitModal(unitId = null) {
    currentEditingUnitId = unitId;
    const modal = document.getElementById('unitModal');
    const form = document.getElementById('unitForm');
    const title = document.getElementById('modal-title');
    const saveBtn = document.getElementById('saveUnitBtn');
    
    form.reset();
    document.getElementById('conversionFactor').value = '1.000000';
    document.getElementById('baseUnitSelect').innerHTML = '<option value="">No base unit</option>';
    
    if (unitId) {
        title.textContent = 'Edit Unit';
        saveBtn.innerHTML = 'Update Unit';
        loadUnitData(unitId);
    } else {
        title.textContent = 'Add New Unit';
        saveBtn.innerHTML = 'Save Unit';
    }
    
    modal.classList.remove('hidden');
    document.getElementById('unitType').addEventListener('change', updateBaseUnitOptions);
}

function closeUnitModal() {
    document.getElementById('unitModal').classList.add('hidden');
    currentEditingUnitId = null;
}

async function loadUnitData(unitId) {
    try {
        const response = await fetch(`/admin/units/${unitId}/edit`, {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            const unit = await response.json();
            
            document.getElementById('unitName').value = unit.name;
            document.getElementById('unitSymbol').value = unit.symbol;
            document.getElementById('unitType').value = unit.type;
            document.getElementById('conversionFactor').value = unit.conversion_factor || '1.000000';
            
            if (unit.base_unit_id) {
                await updateBaseUnitOptions();
                document.getElementById('baseUnitSelect').value = unit.base_unit_id;
            }
        } else {
            showToast('Error', 'Failed to load unit data', 'error');
        }
    } catch (error) {
        console.error('Error loading unit data:', error);
        showToast('Error', 'Network error occurred', 'error');
    }
}

async function updateBaseUnitOptions() {
    const typeSelect = document.getElementById('unitType');
    const baseUnitSelect = document.getElementById('baseUnitSelect');
    const selectedType = typeSelect.value;
    
    baseUnitSelect.innerHTML = '<option value="">No base unit</option>';
    
    if (!selectedType) return;
    
    try {
        const response = await fetch(`/admin/units/base?type=${selectedType}&exclude_id=${currentEditingUnitId || ''}`, {
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            const units = await response.json();
            units.forEach(unit => {
                const option = document.createElement('option');
                option.value = unit.id;
                option.textContent = `${unit.name} (${unit.symbol})`;
                baseUnitSelect.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Error loading base units:', error);
    }
}

document.getElementById('unitForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const saveBtn = document.getElementById('saveUnitBtn');
    const originalBtnText = saveBtn.innerHTML;
    
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';
    
    try {
        const url = currentEditingUnitId ? `/admin/units/${currentEditingUnitId}` : '/admin/units';
        const method = currentEditingUnitId ? 'PUT' : 'POST';
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(Object.fromEntries(formData))
        });
        
        const result = await response.json();
        
        if (response.ok && result.success) {
            closeUnitModal();
            showToast('Success', result.message, 'success');
            // Delay reload slightly to show the toast
            setTimeout(() => location.reload(), 700);
        } else {
            throw new Error(result.message || 'Operation failed');
        }
    } catch (error) {
        console.error('Error saving unit:', error);
        showToast('Error', error.message, 'error');
    } finally {
        saveBtn.disabled = false;
        saveBtn.innerHTML = originalBtnText;
    }
});

function editUnit(unitId) {
    openUnitModal(unitId);
}

function confirmToggleStatus(unitId, currentStatus) {
    const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
    const message = `This action will make the unit ${newStatus}. Users may not be able to select it for new records.`;
    
    openConfirmation(
        'Update Unit Status?', 
        message, 
        'warning', 
        () => toggleUnitStatus(unitId)
    );
}

async function toggleUnitStatus(unitId) {
    try {
        const response = await fetch(`/admin/units/${unitId}/toggle-status`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });
        
        const result = await response.json();
        
        if (response.ok && result.success) {
            showToast('Updated', result.message, 'success');
            setTimeout(() => location.reload(), 700);
        } else {
            throw new Error(result.message || 'Operation failed');
        }
    } catch (error) {
        console.error('Error toggling unit status:', error);
        showToast('Error', error.message, 'error');
    }
}

function confirmDelete(unitId) {
    openConfirmation(
        'Delete Unit?', 
        'Are you sure you want to delete this unit? This action cannot be undone.', 
        'danger', 
        () => deleteUnit(unitId)
    );
}

async function deleteUnit(unitId) {
    try {
        const response = await fetch(`/admin/units/${unitId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });
        
        const result = await response.json();
        
        if (response.ok && result.success) {
            showToast('Deleted', result.message, 'success');
            setTimeout(() => location.reload(), 700);
        } else {
            throw new Error(result.message || 'Operation failed');
        }
    } catch (error) {
        console.error('Error deleting unit:', error);
        showToast('Error', error.message, 'error');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('unitType');
    if (typeSelect) {
        typeSelect.addEventListener('change', updateBaseUnitOptions);
    }
});
</script>
@endsection