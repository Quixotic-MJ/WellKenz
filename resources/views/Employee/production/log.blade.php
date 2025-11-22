@extends('Employee.layout.app')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 space-y-6 pb-24">

    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Daily Production Log</h1>
            <p class="text-sm text-gray-500 mt-1">Record finished goods for <span class="font-semibold text-gray-700">{{ date('F d, Y') }}</span>.</p>
        </div>
        <div class="flex items-center gap-4">
            <div class="bg-amber-50 px-5 py-3 rounded-xl border border-amber-100 flex flex-col items-end">
                <p class="text-[10px] text-amber-600 uppercase font-bold tracking-wider">Shift Total</p>
                <p class="text-2xl font-display font-bold text-chocolate leading-none mt-1">{{ number_format($shiftTotal, 0) }} <span class="text-sm font-medium text-amber-700">Units</span></p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- 2. ENTRY FORM (Left) --}}
        <div class="lg:col-span-1">
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden sticky top-24">
                <div class="p-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-plus-circle text-chocolate"></i> New Entry
                    </h3>
                </div>
                
                <div class="p-6">
                    <form id="productionForm" action="{{ route('employee.production.store') }}" method="POST">
                        @csrf
                        <div class="space-y-5">
                            
                            <!-- Product Select -->
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Select Product <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <select name="item_id" id="productSelect" required 
                                            class="block w-full appearance-none bg-white border border-gray-300 text-gray-900 py-3 px-4 pr-8 rounded-xl leading-tight focus:outline-none focus:border-chocolate focus:ring-1 focus:ring-chocolate transition-colors cursor-pointer text-sm">
                                        <option value="" disabled selected>Choose finished good...</option>
                                        @foreach($finishedGoods as $good)
                                            @if($good['has_recipe'])
                                                <option value="{{ $good['id'] }}" data-recipe-id="{{ $good['recipe_id'] }}" data-unit="{{ $good['unit'] }}">{{ $good['name'] }} ✓</option>
                                            @else
                                                <option value="{{ $good['id'] }}" data-recipe-id="" data-unit="{{ $good['unit'] }}" title="No recipe defined">{{ $good['name'] }} ⚠</option>
                                            @endif
                                        @endforeach
                                    </select>
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                                        <i class="fas fa-chevron-down text-xs"></i>
                                    </div>
                                </div>
                                <div class="mt-1 text-xs text-amber-600">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Items marked with ⚠ don't have production recipes defined
                                </div>
                                @error('item_id')
                                    <p class="mt-1 text-xs text-red-600"><i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Batch Number (Auto/Manual) -->
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Batch / Lot Number</label>
                                <div class="relative">
                                    <input type="text" name="batch_number" id="batchInput"
                                           class="block w-full pl-4 pr-10 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-600 font-mono text-sm focus:bg-white focus:border-chocolate focus:ring-1 focus:ring-chocolate transition-colors" 
                                           value="BATCH-{{ date('ymd') }}-{{ str_pad($todayProductions->count() + 1, 2, '0', STR_PAD_LEFT) }}" readonly>
                                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                        <i class="fas fa-barcode text-gray-400"></i>
                                    </div>
                                </div>
                                @error('batch_number')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <!-- Good Output -->
                                <div>
                                    <label class="block text-xs font-bold text-green-700 uppercase tracking-wide mb-1.5">Good Output <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <input type="number" name="good_output" id="goodOutput" step="0.01" min="0" required 
                                               class="block w-full pl-4 pr-8 py-3 border border-green-200 rounded-xl text-green-800 font-bold text-lg focus:ring-green-500 focus:border-green-500 placeholder-green-800/30" 
                                               placeholder="0">
                                        <span class="absolute inset-y-0 right-3 flex items-center text-xs font-bold text-green-600 unit-display"></span>
                                    </div>
                                    @error('good_output')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <!-- Rejects (Conditional) -->
                                <div class="rejects-field" style="display: none;">
                                    <label class="block text-xs font-bold text-red-600 uppercase tracking-wide mb-1.5">Waste / Rejects</label>
                                    <div class="relative">
                                        <input type="number" name="rejects" id="rejectsOutput" step="0.01" min="0" value="0" 
                                               class="block w-full pl-4 pr-8 py-3 border border-red-200 rounded-xl text-red-600 font-bold text-lg focus:ring-red-500 focus:border-red-500 placeholder-red-300">
                                        <span class="absolute inset-y-0 right-3 flex items-center text-xs font-bold text-red-400 unit-display"></span>
                                    </div>
                                    @error('rejects')
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Remarks -->
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wide mb-1.5">Notes / Issues</label>
                                <textarea name="notes" rows="2" 
                                          class="block w-full border-gray-300 rounded-xl shadow-sm focus:ring-chocolate focus:border-chocolate sm:text-sm resize-none p-3" 
                                          placeholder="Any issues encountered during baking?"></textarea>
                            </div>

                            <button type="submit" class="w-full py-3.5 bg-gradient-to-r from-chocolate to-[#8B4513] text-white font-bold rounded-xl hover:shadow-lg hover:-translate-y-0.5 active:translate-y-0 transition-all flex items-center justify-center gap-2">
                                <i class="fas fa-save"></i> Record Production
                            </button>

                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- 3. TODAY'S LOG (Right) --}}
        <div class="lg:col-span-2">
            <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden h-full flex flex-col">
                <div class="px-6 py-5 border-b border-gray-100 bg-white flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center text-blue-600">
                            <i class="fas fa-clipboard-list text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">Today's Records</h3>
                            <p class="text-xs text-gray-500">Live log of finished goods</p>
                        </div>
                    </div>
                    <button class="text-xs font-bold text-gray-400 hover:text-chocolate transition-colors flex items-center gap-1">
                        <i class="fas fa-download"></i> Report
                    </button>
                </div>
                
                <div class="flex-1 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Time</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Product Details</th>
                                <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Good</th>
                                @if($todayProductions->first() && isset($todayProductions->first()->reject_quantity))
                                    <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Reject</th>
                                @endif
                                <th class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($todayProductions as $production)
                                <tr class="hover:bg-gray-50/80 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-gray-900">{{ $production->created_at->format('h:i A') }}</div>
                                        <div class="text-xs text-gray-400">{{ $production->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-lg bg-orange-50 flex items-center justify-center text-chocolate flex-shrink-0">
                                                <i class="fas fa-box-open text-xs"></i>
                                            </div>
                                            <div>
                                                <p class="text-sm font-bold text-gray-900 leading-tight">
                                                    @if($production->recipe && $production->recipe->finishedItem)
                                                        {{ $production->recipe->finishedItem->name }}
                                                    @else
                                                        <span class="text-gray-500 italic">Product unavailable</span>
                                                    @endif
                                                </p>
                                                <p class="text-[10px] font-mono text-gray-500 mt-0.5 bg-gray-100 inline-block px-1.5 py-0.5 rounded">
                                                    {{ $production->batch_number ?? 'N/A' }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <span class="text-sm font-bold text-green-700 bg-green-50 px-2 py-1 rounded-md border border-green-100">
                                            {{ number_format($production->actual_quantity ?? 0, 0) }} 
                                            <span class="text-[10px] font-normal text-green-600 uppercase">
                                                @if($production->recipe && $production->recipe->finishedItem && $production->recipe->finishedItem->unit)
                                                    {{ $production->recipe->finishedItem->unit->symbol }}
                                                @elseif($production->unit)
                                                    {{ $production->unit->symbol ?? 'pcs' }}
                                                @else
                                                    pcs
                                                @endif
                                            </span>
                                        </span>
                                    </td>
                                    @if($todayProductions->first() && isset($todayProductions->first()->reject_quantity))
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            @if(isset($production->reject_quantity) && $production->reject_quantity > 0)
                                                <span class="text-sm font-bold text-red-600 bg-red-50 px-2 py-1 rounded-md border border-red-100">
                                                    {{ number_format($production->reject_quantity, 0) }}
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-300">-</span>
                                            @endif
                                        </td>
                                    @endif
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold capitalize
                                            @if($production->status === 'completed') bg-green-100 text-green-800
                                            @elseif($production->status === 'in_progress') bg-blue-100 text-blue-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            <span class="w-1.5 h-1.5 rounded-full bg-current mr-1.5"></span>
                                            {{ ucfirst(str_replace('_', ' ', $production->status ?? 'unknown')) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $todayProductions->first() && isset($todayProductions->first()->reject_quantity) ? '5' : '4' }}" class="px-6 py-12 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-3">
                                                <i class="fas fa-clipboard text-3xl text-gray-300"></i>
                                            </div>
                                            <p class="text-gray-900 font-medium">No production entries yet</p>
                                            <p class="text-gray-400 text-sm mt-1">Start by filling out the form on the left.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

{{-- UI COMPONENTS --}}

<!-- Confirmation Modal -->
<div id="confirmModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity backdrop-blur-sm" onclick="closeConfirmModal()"></div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full">
            <div class="bg-white px-6 pt-6 pb-6">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-chocolate/10 sm:mx-0 sm:h-12 sm:w-12">
                        <i class="fas fa-save text-chocolate text-xl"></i>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-bold text-gray-900" id="confirmTitle">Confirm Production Entry</h3>
                        
                        <div class="mt-4 bg-gray-50 rounded-xl p-4 border border-gray-100 text-sm text-left space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Product:</span>
                                <span class="font-bold text-gray-900" id="confProduct"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Batch #:</span>
                                <span class="font-mono text-gray-700" id="confBatch"></span>
                            </div>
                            <div class="flex justify-between border-t border-gray-200 pt-2 mt-2">
                                <span class="text-gray-500">Good Output:</span>
                                <span class="font-bold text-green-600" id="confGood"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Rejects:</span>
                                <span class="font-bold text-red-600" id="confReject"></span>
                            </div>
                        </div>

                        <p class="text-sm text-gray-500 mt-4">Please verify the counts are accurate before saving.</p>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse gap-3">
                <button type="button" id="confirmBtn" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2.5 bg-chocolate text-base font-bold text-white hover:bg-chocolate-dark focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                    Confirm & Save
                </button>
                <button type="button" onclick="closeConfirmModal()" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2.5 bg-white text-base font-bold text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                    Edit
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Check if reject_quantity column exists and show/hide field accordingly
    function checkRejectQuantitySupport() {
        const rejectField = document.querySelector('.rejects-field');
        const rejectInput = document.getElementById('rejectsOutput');
        const confirmReject = document.getElementById('confReject');
        
        // Check if any production has reject_quantity data
        fetch('/employee/production/check-reject-support', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.supports_reject_quantity) {
                // Show reject quantity field if supported
                if (rejectField) rejectField.style.display = 'block';
            } else {
                // Hide reject quantity field if not supported
                if (rejectField) rejectField.style.display = 'none';
                if (rejectInput) {
                    rejectInput.value = '0';
                    rejectInput.disabled = true;
                }
                if (confirmReject) {
                    confirmReject.parentElement.style.display = 'none';
                }
            }
        })
        .catch(error => {
            console.log('Error checking reject quantity support, defaulting to visible');
            // If there's an error, default to showing the field
            if (rejectField) rejectField.style.display = 'block';
        });
    }

    // 2. Update Unit Display and Recipe Status based on product selection
    const productSelect = document.getElementById('productSelect');
    const unitDisplays = document.querySelectorAll('.unit-display');
    
    function updateUnits() {
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        let unit = '';
        let hasRecipe = false;
        
        if (selectedOption && selectedOption.dataset.unit) {
            unit = selectedOption.dataset.unit;
            hasRecipe = selectedOption.dataset.recipeId && selectedOption.dataset.recipeId !== '';
        }
        
        unitDisplays.forEach(span => span.textContent = unit);
        
        // Update form validation message if no recipe
        const goodOutput = document.getElementById('goodOutput');
        const notes = document.querySelector('textarea[name="notes"]');
        if (hasRecipe) {
            goodOutput.placeholder = "0";
            if (notes) notes.placeholder = "Production notes...";
        } else {
            goodOutput.placeholder = "0 (No recipe - manual entry)";
            if (notes) notes.placeholder = "Detailed production notes required (no recipe available)";
        }
    }
    
    productSelect.addEventListener('change', updateUnits);
    // Initial call if page reloads with value
    if(productSelect.value) updateUnits();

    // 3. Modal Logic
    const form = document.getElementById('productionForm');
    const confirmModal = document.getElementById('confirmModal');
    const confirmBtn = document.getElementById('confirmBtn');

    window.closeConfirmModal = function() {
        confirmModal.classList.add('hidden');
    }

    form.addEventListener('submit', function(e) {
        e.preventDefault(); // Stop submission

        // Get Values
        const productText = productSelect.options[productSelect.selectedIndex].text;
        const batch = document.getElementById('batchInput').value;
        const good = document.getElementById('goodOutput').value;
        const reject = document.getElementById('rejectsOutput').value;
        
        // Populate Modal
        document.getElementById('confProduct').textContent = productText;
        document.getElementById('confBatch').textContent = batch;
        document.getElementById('confGood').textContent = good;
        document.getElementById('confReject').textContent = reject;

        // Show Modal
        confirmModal.classList.remove('hidden');
    });

    confirmBtn.addEventListener('click', function() {
        // Visual feedback
        confirmBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-2"></i> Saving...';
        confirmBtn.disabled = true;
        
        // Submit actual form
        form.submit();
    });

    // 4. Initialize - check reject quantity support
    checkRejectQuantitySupport();
});
</script>
@endsection