@extends('Inventory.layout.app')

@section('title','Inventory Overview - WellKenz ERP')
@section('breadcrumb','Inventory Overview')

@section('content')
<div class="space-y-6">
    <!-- header -->
    <div class="bg-white border rounded p-6 flex justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Inventory Overview</h1>
            <p class="text-sm text-gray-500">Real-time stock levels and reorder alerts</p>
        </div>
        <div class="text-right">
            <p class="text-sm text-gray-900 font-medium">{{ now()->format('F j, Y') }}</p>
            <p class="text-xs text-gray-500">{{ now()->format('l') }}</p>
        </div>
    </div>

    <!-- stat cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        @php
            $cards = [
                ['label'=>'Total Items',  'value'=>$total,  'icon'=>'boxes', 'color'=>'gray'],
                ['label'=>'In Stock',     'value'=>$inStock, 'icon'=>'check-circle', 'color'=>'gray'],
                ['label'=>'Low Stock',    'value'=>$lowStock,'icon'=>'exclamation-triangle','color'=>'yellow'],
                ['label'=>'Out of Stock', 'value'=>$outStock,'icon'=>'times-circle','color'=>'red']
            ];
        @endphp
        @foreach($cards as $c)
        <div class="bg-white border rounded p-5 flex justify-between">
            <div>
                <p class="text-xs text-gray-500 uppercase tracking-wider">{{ $c['label'] }}</p>
                <p class="text-2xl font-semibold mt-2">{{ $c['value'] }}</p>
            </div>
            <div class="w-10 h-10 bg-{{ $c['color'] }}-100 flex items-center justify-center rounded">
                <i class="fas fa-{{ $c['icon'] }} text-{{ $c['color'] }}-600"></i>
            </div>
        </div>
        @endforeach
    </div>

    <!-- main table -->
    <div class="bg-white border rounded p-6">
        <div class="flex justify-between mb-4">
            <h3 class="text-lg font-semibold">All Inventory Items</h3>
            <button onclick="openAddModal()"
                class="px-4 py-2 bg-gray-900 text-white text-sm rounded hover:bg-gray-800">
                <i class="fas fa-plus-circle mr-2"></i>Add Item
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-2">Item Details</th>
                        <th class="text-left">Category</th>
                        <th class="text-left">Current Stock</th>
                        <th class="text-left">Reorder Level</th>
                        <th class="text-left">Unit</th>
                        <th class="text-left">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($items as $it)
                    @php
                        $status = 'NORMAL';
                        if ($it->item_stock == 0) $status='OUT';
                        elseif ($it->item_stock <= $it->min_stock_level) $status='CRITICAL';
                        elseif ($it->item_stock <= $it->reorder_level) $status='LOW';
                    @endphp
                    <tr>
                        <td class="py-3">
                            <div class="font-medium">{{ $it->item_name }}</div>
                            <div class="text-xs text-gray-500">{{ $it->item_code }}</div>
                        </td>
                        <td>{{ $it->cat_id }}</td>
                        <td>{{ number_format($it->item_stock,3) }}</td>
                        <td>{{ number_format($it->reorder_level,3) }}</td>
                        <td>{{ $it->item_unit }}</td>
                        <td>
                            <span class="px-2 py-1 text-xs rounded
                                @if($status=='OUT') bg-red-100 text-red-700
                                @elseif($status=='CRITICAL') bg-red-100 text-red-700
                                @elseif($status=='LOW') bg-yellow-100 text-yellow-700
                                @else bg-green-100 text-green-700 @endif">
                                {{ $status }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- quick reorder -->
    <div class="bg-white border rounded p-6">
        <h4 class="text-lg font-semibold mb-4">Quick Reorder</h4>
        <div class="space-y-2">
            @foreach($lows as $l)
            <div class="flex justify-between items-center p-2 hover:bg-gray-50 rounded">
                <span class="text-sm">{{ $l->item_name }}</span>
                <span class="text-xs font-medium text-yellow-600">{{ $l->current_stock }} {{ $l->item_unit }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- add item modal -->
<div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 hidden place-items-center">
    <div class="bg-white rounded p-6 w-full max-w-2xl">
        <form id="addForm" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <input name="item_code" required placeholder="Item Code *" class="border px-3 py-2 rounded">
                <input name="item_name"  required placeholder="Item Name *" class="border px-3 py-2 rounded">
            </div>
            <select name="cat_id" required class="w-full border px-3 py-2 rounded">
                <option value="">Select Category *</option>
                @foreach(\App\Models\Category::pluck('cat_name','cat_id') as $id=>$name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
            <input name="item_unit" required placeholder="Unit (kg, piece, etc.) *" class="w-full border px-3 py-2 rounded">
            <div class="grid grid-cols-3 gap-4">
                <input type="number" step="0.001" name="item_stock" placeholder="Current Stock" class="border px-3 py-2 rounded">
                <input type="number" step="0.001" name="reorder_level" placeholder="Reorder Level" class="border px-3 py-2 rounded">
                <input type="number" step="0.001" name="min_stock_level" placeholder="Min Stock" class="border px-3 py-2 rounded">
            </div>
            <textarea name="item_description" placeholder="Description" class="w-full border px-3 py-2 rounded"></textarea>
            <label class="flex items-center">
                <input type="checkbox" name="is_custom" value="1" class="mr-2">
                <span class="text-sm">Custom item</span>
            </label>
            <div class="flex space-x-3">
                <button type="button" onclick="closeAddModal()" class="flex-1 border rounded px-4 py-2">Cancel</button>
                <button class="flex-1 bg-gray-900 text-white rounded px-4 py-2">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal(){ document.getElementById('addModal').classList.remove('hidden'); }
function closeAddModal(){ document.getElementById('addModal').classList.add('hidden'); }

document.getElementById('addForm').addEventListener('submit',async(e)=>{
    e.preventDefault();
    const data = Object.fromEntries(new FormData(e.target));
    data.is_custom = data.is_custom ? true : false;
    await fetch("{{ route('inventory.store') }}",{
        method:'POST',
        headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json'},
        body:JSON.stringify(data)
    });
    location.reload();
});
</script>
@endsection