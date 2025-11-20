@extends('Inventory.layout.app')

@section('content')
<div class="space-y-6">

    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Generate Batch Labels</h1>
            <p class="text-sm text-gray-500 mt-1">Print QR codes for recently received items to enable tracking.</p>
        </div>
        <div class="flex items-center gap-3">
            <button class="inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition shadow-sm">
                <i class="fas fa-print mr-2"></i> Print Selected
            </button>
        </div>
    </div>

    {{-- 2. RECENT BATCHES GRID --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        <!-- Label Card 1 -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4 flex flex-col relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-2">
                <input type="checkbox" class="w-5 h-5 text-chocolate border-gray-300 rounded focus:ring-chocolate" checked>
            </div>
            
            <!-- Label Preview -->
            <div class="border-2 border-dashed border-gray-300 bg-gray-50 p-4 rounded flex items-center space-x-4 mb-4 group-hover:border-chocolate transition-colors">
                <div class="w-20 h-20 bg-white border border-gray-200 flex items-center justify-center">
                    <i class="fas fa-qrcode text-4xl text-gray-800"></i>
                </div>
                <div class="space-y-1">
                    <p class="text-xs font-bold text-gray-900 uppercase">Heavy Cream</p>
                    <p class="text-[10px] text-gray-500">SKU: D-CRM-001</p>
                    <p class="text-[10px] text-gray-500">Batch: <span class="font-mono font-bold text-gray-800">231024-A</span></p>
                    <p class="text-[10px] text-red-600 font-bold">Exp: Oct 24, 2023</p>
                </div>
            </div>

            <div class="flex justify-between items-center mt-auto">
                <span class="text-xs text-gray-500">Recv: Just now</span>
                <button class="text-xs bg-chocolate text-white px-3 py-1.5 rounded hover:bg-chocolate-dark transition">
                    Print (10 Copies)
                </button>
            </div>
        </div>

        <!-- Label Card 2 -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4 flex flex-col relative overflow-hidden group">
             <div class="absolute top-0 right-0 p-2">
                <input type="checkbox" class="w-5 h-5 text-chocolate border-gray-300 rounded focus:ring-chocolate" checked>
            </div>
            
            <div class="border-2 border-dashed border-gray-300 bg-gray-50 p-4 rounded flex items-center space-x-4 mb-4 group-hover:border-chocolate transition-colors">
                <div class="w-20 h-20 bg-white border border-gray-200 flex items-center justify-center">
                    <i class="fas fa-qrcode text-4xl text-gray-800"></i>
                </div>
                <div class="space-y-1">
                    <p class="text-xs font-bold text-gray-900 uppercase">Cake Boxes 10x10</p>
                    <p class="text-[10px] text-gray-500">SKU: PCK-BX-10</p>
                    <p class="text-[10px] text-gray-500">Batch: <span class="font-mono font-bold text-gray-800">231024-B</span></p>
                    <p class="text-[10px] text-gray-400">Non-perishable</p>
                </div>
            </div>

            <div class="flex justify-between items-center mt-auto">
                <span class="text-xs text-gray-500">Recv: Just now</span>
                <button class="text-xs bg-chocolate text-white px-3 py-1.5 rounded hover:bg-chocolate-dark transition">
                    Print (50 Copies)
                </button>
            </div>
        </div>

        <!-- Label Card 3 -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4 flex flex-col relative overflow-hidden group">
             <div class="absolute top-0 right-0 p-2">
                <input type="checkbox" class="w-5 h-5 text-chocolate border-gray-300 rounded focus:ring-chocolate">
            </div>
            
            <div class="border-2 border-dashed border-gray-300 bg-gray-50 p-4 rounded flex items-center space-x-4 mb-4 group-hover:border-chocolate transition-colors">
                <div class="w-20 h-20 bg-white border border-gray-200 flex items-center justify-center">
                    <i class="fas fa-qrcode text-4xl text-gray-800"></i>
                </div>
                <div class="space-y-1">
                    <p class="text-xs font-bold text-gray-900 uppercase">White Sugar 50kg</p>
                    <p class="text-[10px] text-gray-500">SKU: RM-SGR-002</p>
                    <p class="text-[10px] text-gray-500">Batch: <span class="font-mono font-bold text-gray-800">231020-X</span></p>
                    <p class="text-[10px] text-gray-400">Non-perishable</p>
                </div>
            </div>

            <div class="flex justify-between items-center mt-auto">
                <span class="text-xs text-gray-500">Recv: 4 days ago</span>
                <button class="text-xs bg-white border border-gray-300 text-gray-700 px-3 py-1.5 rounded hover:bg-gray-50 transition">
                    Reprint
                </button>
            </div>
        </div>

    </div>
</div>
@endsection