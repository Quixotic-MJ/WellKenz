@extends('Employee.layout.app')

@section('title', 'Edit Requisition - WellKenz ERP')
@section('breadcrumb', 'Edit Requisition')

@section('content')
<div class="space-y-6">

    <!-- toast -->
    <div id="successMessage" class="hidden bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded"></div>
    <div id="errorMessage"  class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded"></div>

    <!-- header card -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Edit Requisition</h1>
                <p class="text-sm text-gray-500 mt-1">Modify your pending requisition</p>
            </div>
            <a href="{{ route('staff.requisitions.index') }}"
                class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 transition text-sm font-medium rounded">
                <i class="fas fa-arrow-left mr-2"></i>Back to List
            </a>
        </div>
    </div>

    <!-- form -->
    <form method="POST" action="{{ route('staff.requisitions.update', $requisition->req_id) }}" id="editReqForm">
        @csrf
        @method('PUT')

        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="space-y-4 text-sm">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Purpose <span class="text-rose-500">*</span></label>
                        <input type="text" name="req_purpose" value="{{ old('req_purpose', $requisition->req_purpose) }}" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                        @error('req_purpose') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Priority <span class="text-rose-500">*</span></label>
                        <select name="req_priority" required class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-gray-400">
                            <option value="low" {{ old('req_priority', $requisition->req_priority) == 'low' ? 'selected' : '' }}>Low</option>
                            <option value="medium" {{ old('req_priority', $requisition->req_priority) == 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="high" {{ old('req_priority', $requisition->req_priority) == 'high' ? 'selected' : '' }}>High</option>
                        </select>
                        @error('req_priority') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Items <span class="text-rose-500">*</span></label>
                    <div class="border border-gray-200 rounded">
                        <table class="w-full text-sm" id="itemsTable">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-3 py-2 text-left">Item</th>
                                    <th class="px-3 py-2 text-left">Unit</th>
                                    <th class="px-3 py-2 text-left">Stock</th>
                                    <th class="px-3 py-2 text-left">Qty</th>
                                    <th class="px-3 py-2"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsTbody">
                                @foreach($requisition->items as $index => $reqItem)
                                <tr>
                                    <td class="px-3 py-2">
                                        <select name="items[{{ $index }}][item_id]" required class="item-select w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400">
                                            <option value="">Choose item…</option>
                                            @foreach($items as $i)
                                            <option value="{{ $i->item_id }}" data-unit="{{ $i->item_unit }}" data-stock="{{ $i->item_stock }}" {{ $reqItem->item_id == $i->item_id ? 'selected' : '' }}>
                                                {{ $i->item_name }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-3 py-2 unit-cell text-gray-600">{{ $reqItem->item->item_unit ?? '-' }}</td>
                                    <td class="px-3 py-2 stock-cell text-gray-600">{{ $reqItem->item->item_stock ?? '-' }}</td>
                                    <td class="px-3 py-2"><input type="number" name="items[{{ $index }}][quantity]" value="{{ old('items.' . $index . '.quantity', $reqItem->req_item_quantity) }}" min="1" required class="qty-input w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400"></td>
                                    <td class="px-3 py-2"><button type="button" onclick="removeRow(this)" class="text-rose-600 hover:text-rose-800"><i class="fas fa-trash text-xs"></i></button></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="px-3 py-2 border-t border-gray-200 bg-gray-50">
                            <button type="button" onclick="addRow()" class="text-sm text-blue-600 hover:underline"><i class="fas fa-plus mr-1"></i>Add row</button>
                        </div>
                    </div>
                    @error('items') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    @error('items.*') <p class="text-red-500 text-xs mt-1">Please check item details</p> @enderror
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <a href="{{ route('staff.requisitions.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 hover:bg-gray-50 rounded">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-gray-900 text-white hover:bg-gray-800 rounded">Update Requisition</button>
            </div>
        </div>
    </form>

</div>

<script>
let rowIdx = {{ count($requisition->items) }};
function addRow(){
    const tbody = document.getElementById('itemsTbody');
    const tr = document.createElement('tr');
    tr.innerHTML = `
        <td class="px-3 py-2">
            <select name="items[${rowIdx}][item_id]" required class="item-select w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400">
                <option value="">Choose item…</option>
                @foreach($items as $i)<option value="{{ $i->item_id }}" data-unit="{{ $i->item_unit }}" data-stock="{{ $i->item_stock }}">{{ $i->item_name }}</option>@endforeach
            </select>
        </td>
        <td class="px-3 py-2 unit-cell text-gray-600">-</td>
        <td class="px-3 py-2 stock-cell text-gray-600">-</td>
        <td class="px-3 py-2"><input type="number" name="items[${rowIdx}][quantity]" min="1" required class="qty-input w-full border border-gray-300 rounded px-2 py-1 text-sm focus:outline-none focus:ring-1 focus:ring-gray-400"></td>
        <td class="px-3 py-2"><button type="button" onclick="removeRow(this)" class="text-rose-600 hover:text-rose-800"><i class="fas fa-trash text-xs"></i></button></td>
    `;
    tbody.appendChild(tr); rowIdx++;
    attachSelectListeners();
}
function removeRow(btn){ btn.closest('tr').remove(); }
function attachSelectListeners(){
    document.querySelectorAll('.item-select').forEach(s=>{
        s.onchange = function(){
            const opt = this.options[this.selectedIndex];
            const row = this.closest('tr');
            row.querySelector('.unit-cell').textContent = opt.dataset.unit || '-';
            row.querySelector('.stock-cell').textContent = opt.dataset.stock || '-';
        };
    });
}
attachSelectListeners();
</script>
@endsection