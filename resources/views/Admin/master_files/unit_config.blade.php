@extends('Admin.layout.app')

@section('content')
<div class="space-y-6 relative">

    {{-- 1. HEADER & ACTIONS --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Unit Configurations</h1>
            <p class="text-sm text-gray-500 mt-1">Define the standard measurements (Base Units) and container types (Packaging Units) used across the system.</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="openUnitModal()" 
                class="inline-flex items-center justify-center px-4 py-2 bg-chocolate text-white text-sm font-medium rounded-lg hover:bg-chocolate-dark transition shadow-sm">
                <i class="fas fa-plus mr-2"></i> Add New Unit
            </button>
        </div>
    </div>

    {{-- 2. INFORMATION CARD --}}
    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-500"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">System Logic</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <p>
                        <strong>Base Units (Weight/Volume/Count):</strong> Used for <em>Recipes</em> and <em>Inventory Counting</em> (e.g., Grams, Liters).
                        <br>
                        <strong>Packaging Units:</strong> Used for <em>Purchasing</em> and <em>Delivery</em> (e.g., Sacks, Boxes).
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. FILTERS --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <form method="GET" action="{{ route('admin.units.index') }}" class="flex flex-wrap items-center gap-4">
            <div class="flex-1 min-w-64">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search Units</label>
                <input type="text" 
                       id="search" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Search by name or symbol..." 
                       class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-chocolate focus:border-chocolate sm:text-sm">
            </div>
            
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <select id="type" name="type" class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-chocolate focus:border-chocolate sm:text-sm">
                    <option value="">All Types</option>
                    <option value="weight" {{ request('type') == 'weight' ? 'selected' : '' }}>Weight</option>
                    <option value="volume" {{ request('type') == 'volume' ? 'selected' : '' }}>Volume</option>
                    <option value="piece" {{ request('type') == 'piece' ? 'selected' : '' }}>Count/Piece</option>
                    <option value="length" {{ request('type') == 'length' ? 'selected' : '' }}>Length</option>
                </select>
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select id="status" name="status" class="block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-chocolate focus:border-chocolate sm:text-sm">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-chocolate hover:bg-chocolate-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-chocolate">
                    <i class="fas fa-search mr-2"></i> Filter
                </button>
                <a href="{{ route('admin.units.index') }}" class="ml-2 inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-chocolate">
                    <i class="fas fa-times mr-2"></i> Clear
                </a>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        {{-- 4. LEFT COL: STANDARD BASE UNITS --}}
        <div class="space-y-4">
            <h3 class="text-lg font-bold text-gray-900 flex items-center">
                <i class="fas fa-ruler-combined text-gray-400 mr-2"></i> Standard Base Units
            </h3>
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Abbr.</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Base Unit</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($baseUnits as $unit)
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $unit->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                <span class="bg-gray-100 px-2 py-1 rounded text-xs font-mono">{{ $unit->symbol }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ ucfirst($unit->type) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                @if($unit->baseUnit)
                                    {{ $unit->baseUnit->name }}
                                @else
                                    <span class="text-gray-400">Base</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right text-sm font-medium">
                                @if(in_array($unit->name, ['Kilogram', 'Gram', 'Liter', 'Milliliter', 'Piece']))
                                    <i class="fas fa-lock text-gray-400" title="System Default"></i>
                                @else
                                    <button onclick="editUnit({{ $unit->id }})" class="text-blue-600 hover:text-blue-900 mr-2">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="confirmToggleStatus({{ $unit->id }}, '{{ $unit->is_active ? 'active' : 'inactive' }}')" class="text-yellow-600 hover:text-yellow-900 mr-2">
                                        <i class="fas fa-{{ $unit->is_active ? 'eye-slash' : 'eye' }}"></i>
                                    </button>
                                    <button onclick="confirmDelete({{ $unit->id }})" class="text-red-600 hover:text-red-900">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                <i class="fas fa-ruler-combined text-4xl text-gray-300 mb-4"></i>
                                <p>No base units found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 5. RIGHT COL: PACKAGING UNITS --}}
        <div class="space-y-4">
            <h3 class="text-lg font-bold text-gray-900 flex items-center">
                <i class="fas fa-box-open text-chocolate mr-2"></i> Packaging / Purchase Units
            </h3>
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Symbol</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Base Unit</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($packagingUnits as $unit)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-gray-900">{{ $unit->name }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                <span class="bg-gray-100 px-2 py-1 rounded text-xs font-mono">{{ $unit->symbol }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">
                                @if($unit->baseUnit)
                                    {{ $unit->baseUnit->name }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $unit->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $unit->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right text-sm font-medium">
                                <button onclick="editUnit({{ $unit->id }})" class="text-blue-600 hover:text-blue-900 mr-2">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="confirmToggleStatus({{ $unit->id }}, '{{ $unit->is_active ? 'active' : 'inactive' }}')" class="text-yellow-600 hover:text-yellow-900 mr-2">
                                    <i class="fas fa-{{ $unit->is_active ? 'eye-slash' : 'eye' }}"></i>
                                </button>
                                <button onclick="confirmDelete({{ $unit->id }})" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                <i class="fas fa-box-open text-4xl text-gray-300 mb-4"></i>
                                <p>No packaging units found</p>
                                <button onclick="openUnitModal()" class="mt-2 text-chocolate hover:text-chocolate-dark text-sm">
                                    <i class="fas fa-plus mr-1"></i>Add first packaging unit
                                </button>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- 6. PAGINATION --}}
    @if($units instanceof \Illuminate\Pagination\LengthAwarePaginator)
    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6 rounded-lg">
        {{ $units->appends(request()->query())->links() }}
    </div>
    @endif

</div>

{{-- ------------------- UI COMPONENTS ------------------- --}}

{{-- A. CREATE/EDIT UNIT MODAL --}}
<div id="unitModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeUnitModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form id="unitForm">
                @csrf
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Add New Unit</h3>
                            <div class="mt-4 space-y-4">
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Unit Name</label>
                                    <input type="text" name="name" id="unitName" required
                                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-chocolate focus:border-chocolate sm:text-sm" 
                                           placeholder="e.g. Sack, Box, Kilogram">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Symbol/Abbreviation</label>
                                    <input type="text" name="symbol" id="unitSymbol" required
                                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-chocolate focus:border-chocolate sm:text-sm" 
                                           placeholder="e.g. kg, L, pc">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Unit Type</label>
                                    <select name="type" id="unitType" required
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-chocolate focus:border-chocolate sm:text-sm">
                                        <option value="">Select Type</option>
                                        <option value="weight">Weight</option>
                                        <option value="volume">Volume</option>
                                        <option value="piece">Count/Piece</option>
                                        <option value="length">Length</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Base Unit (Optional)</label>
                                    <select name="base_unit_id" id="baseUnitSelect"
                                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-chocolate focus:border-chocolate sm:text-sm">
                                        <option value="">No base unit</option>
                                    </select>
                                    <div class="mt-1 text-xs text-gray-500">Optional. Select a base unit for conversion.</div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Conversion Factor</label>
                                    <input type="number" name="conversion_factor" id="conversionFactor"
                                           step="0.000001" min="0.000001" value="1.000000"
                                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-chocolate focus:border-chocolate sm:text-sm" 
                                           placeholder="1.000000">
                                    <div class="mt-1 text-xs text-gray-500">Conversion factor to base unit (default: 1.000000).</div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" id="saveUnitBtn"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-chocolate text-base font-medium text-white hover:bg-chocolate-dark focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        <i class="fas fa-save mr-2"></i> Save Unit
                    </button>
                    <button type="button" onclick="closeUnitModal()" 
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- B. CONFIRMATION MODAL (GENERIC) --}}
<div id="confirmationModal" class="hidden fixed inset-0 z-[60] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeConfirmation()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div id="confIconContainer" class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <i id="confIcon" class="fas fa-exclamation-triangle text-red-600"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="confTitle">Confirmation</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500" id="confMessage">Are you sure you want to proceed?</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" id="confConfirmBtn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                    Confirm
                </button>
                <button type="button" onclick="closeConfirmation()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

{{-- C. TOAST NOTIFICATION --}}
<div id="toast" class="hidden fixed top-5 right-5 z-[70] max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden transform transition-all duration-300 ease-out translate-y-2 opacity-0">
    <div class="p-4">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <i id="toastIcon" class="fas fa-check-circle text-green-400"></i>
            </div>
            <div class="ml-3 w-0 flex-1 pt-0.5">
                <p id="toastTitle" class="text-sm font-medium text-gray-900">Successfully saved!</p>
                <p id="toastMessage" class="mt-1 text-sm text-gray-500"></p>
            </div>
            <div class="ml-4 flex-shrink-0 flex">
                <button onclick="hideToast()" class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none">
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
    toastIcon.className = 'fas';
    
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
        btn.className = "w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm";
        iconContainer.className = "mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10";
        icon.className = "fas fa-trash text-red-600";
        btn.textContent = "Delete";
    } else {
        // Warning/Toggle
        btn.className = "w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-yellow-500 text-base font-medium text-white hover:bg-yellow-600 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm";
        iconContainer.className = "mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10";
        icon.className = "fas fa-exclamation-triangle text-yellow-600";
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
        saveBtn.innerHTML = '<i class="fas fa-save mr-2"></i> Update Unit';
        loadUnitData(unitId);
    } else {
        title.textContent = 'Add New Unit';
        saveBtn.innerHTML = '<i class="fas fa-save mr-2"></i> Save Unit';
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