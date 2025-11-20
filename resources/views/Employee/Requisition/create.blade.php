@extends('Admin.layout.app')

@section('content')
<div class="flex flex-col lg:flex-row h-[calc(100vh-8rem)] gap-6">

    {{-- 1. CATALOG SECTION (Left / Main) --}}
    <div class="flex-1 flex flex-col min-w-0 bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        
        <!-- Search & Filter Header -->
        <div class="p-4 border-b border-gray-100 bg-white">
            <div class="flex flex-col sm:flex-row gap-4 justify-between items-center">
                <div class="relative w-full sm:w-72">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    <input type="text" placeholder="Search ingredients..." 
                           class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-chocolate focus:bg-white transition">
                </div>
                
                <!-- Category Tabs -->
                <div class="flex gap-2 overflow-x-auto w-full sm:w-auto pb-1 sm:pb-0 no-scrollbar">
                    <button class="px-4 py-2 rounded-lg bg-chocolate text-white text-sm font-bold whitespace-nowrap shadow-sm">
                        All Items
                    </button>
                    <button class="px-4 py-2 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 text-sm font-medium whitespace-nowrap transition">
                        Dry Goods
                    </button>
                    <button class="px-4 py-2 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 text-sm font-medium whitespace-nowrap transition">
                        Dairy & Cold
                    </button>
                    <button class="px-4 py-2 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 text-sm font-medium whitespace-nowrap transition">
                        Packaging
                    </button>
                </div>
            </div>
        </div>

        <!-- Items Grid (Scrollable) -->
        <div class="flex-1 overflow-y-auto p-4 bg-gray-50">
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                
                {{-- Item 1 --}}
                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition group">
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center text-amber-700">
                                <i class="fas fa-wheat text-lg"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 leading-tight">Bread Flour</h3>
                                <p class="text-xs text-gray-500">Dry Goods • 25kg Sack</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between mt-4">
                        <div class="text-xs text-gray-400 font-medium">
                            Avail: <span class="text-green-600">High</span>
                        </div>
                        <button onclick="addToCart('Bread Flour', 'kg')" class="bg-gray-100 hover:bg-chocolate hover:text-white text-gray-700 px-3 py-1.5 rounded-lg text-sm font-bold transition flex items-center gap-2">
                            <i class="fas fa-plus"></i> Add
                        </button>
                    </div>
                </div>

                {{-- Item 2 --}}
                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition group">
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-700">
                                <i class="fas fa-cube text-lg"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 leading-tight">White Sugar</h3>
                                <p class="text-xs text-gray-500">Dry Goods • 50kg Sack</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between mt-4">
                         <div class="text-xs text-gray-400 font-medium">
                            Avail: <span class="text-amber-500">Low</span>
                        </div>
                        <button onclick="addToCart('White Sugar', 'kg')" class="bg-gray-100 hover:bg-chocolate hover:text-white text-gray-700 px-3 py-1.5 rounded-lg text-sm font-bold transition flex items-center gap-2">
                            <i class="fas fa-plus"></i> Add
                        </button>
                    </div>
                </div>

                {{-- Item 3 --}}
                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition group">
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center text-red-700">
                                <i class="fas fa-egg text-lg"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 leading-tight">Eggs (Large)</h3>
                                <p class="text-xs text-gray-500">Dairy • 30pc Tray</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between mt-4">
                         <div class="text-xs text-gray-400 font-medium">
                            Avail: <span class="text-green-600">OK</span>
                        </div>
                        <button onclick="addToCart('Eggs (Large)', 'Tray')" class="bg-gray-100 hover:bg-chocolate hover:text-white text-gray-700 px-3 py-1.5 rounded-lg text-sm font-bold transition flex items-center gap-2">
                            <i class="fas fa-plus"></i> Add
                        </button>
                    </div>
                </div>

                {{-- Item 4 --}}
                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition group">
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-700">
                                <i class="fas fa-tint text-lg"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 leading-tight">Vanilla Extract</h3>
                                <p class="text-xs text-gray-500">Liquids • 1L Bottle</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between mt-4">
                         <div class="text-xs text-gray-400 font-medium">
                            Avail: <span class="text-green-600">High</span>
                        </div>
                        <button onclick="addToCart('Vanilla Extract', 'L')" class="bg-gray-100 hover:bg-chocolate hover:text-white text-gray-700 px-3 py-1.5 rounded-lg text-sm font-bold transition flex items-center gap-2">
                            <i class="fas fa-plus"></i> Add
                        </button>
                    </div>
                </div>

                {{-- Item 5 --}}
                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition group">
                    <div class="flex justify-between items-start mb-2">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-600">
                                <i class="fas fa-box-open text-lg"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 leading-tight">Cake Boxes (10x10)</h3>
                                <p class="text-xs text-gray-500">Packaging • Piece</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between mt-4">
                         <div class="text-xs text-gray-400 font-medium">
                            Avail: <span class="text-green-600">High</span>
                        </div>
                        <button onclick="addToCart('Cake Boxes (10x10)', 'pc')" class="bg-gray-100 hover:bg-chocolate hover:text-white text-gray-700 px-3 py-1.5 rounded-lg text-sm font-bold transition flex items-center gap-2">
                            <i class="fas fa-plus"></i> Add
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- 2. CART SECTION (Right Sidebar) --}}
    <div class="w-full lg:w-96 bg-white border border-gray-200 rounded-xl shadow-lg flex flex-col h-full">
        <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-cream-bg rounded-t-xl">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-chocolate text-white rounded-full flex items-center justify-center font-bold text-sm">
                    2
                </div>
                <h2 class="font-bold text-gray-900">Current Request</h2>
            </div>
            <button class="text-xs text-red-500 hover:text-red-700 font-medium">Clear All</button>
        </div>

        <!-- Cart Items -->
        <div class="flex-1 overflow-y-auto p-4 space-y-4" id="cartContainer">
            
            <!-- Cart Item 1 -->
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-100 group">
                <div>
                    <h4 class="text-sm font-bold text-gray-900">Bread Flour</h4>
                    <p class="text-xs text-gray-500">Unit: kg</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex items-center bg-white border border-gray-300 rounded-md">
                        <button class="px-2 py-1 text-gray-500 hover:bg-gray-100 rounded-l-md">-</button>
                        <input type="text" value="25" class="w-10 text-center text-sm font-bold border-none focus:ring-0 p-0 text-gray-900">
                        <button class="px-2 py-1 text-gray-500 hover:bg-gray-100 rounded-r-md">+</button>
                    </div>
                    <button class="text-gray-400 hover:text-red-500"><i class="fas fa-times"></i></button>
                </div>
            </div>

            <!-- Cart Item 2 -->
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-100 group">
                <div>
                    <h4 class="text-sm font-bold text-gray-900">Fresh Milk</h4>
                    <p class="text-xs text-gray-500">Unit: L</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex items-center bg-white border border-gray-300 rounded-md">
                        <button class="px-2 py-1 text-gray-500 hover:bg-gray-100 rounded-l-md">-</button>
                        <input type="text" value="5" class="w-10 text-center text-sm font-bold border-none focus:ring-0 p-0 text-gray-900">
                        <button class="px-2 py-1 text-gray-500 hover:bg-gray-100 rounded-r-md">+</button>
                    </div>
                    <button class="text-gray-400 hover:text-red-500"><i class="fas fa-times"></i></button>
                </div>
            </div>

        </div>

        <!-- Cart Footer -->
        <div class="p-5 border-t border-gray-100 bg-gray-50 rounded-b-xl">
            <div class="mb-4">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Notes for Supervisor</label>
                <textarea rows="2" class="w-full border-gray-300 rounded-md text-sm focus:ring-chocolate focus:border-chocolate" placeholder="e.g. For Wedding Cake #882..."></textarea>
            </div>
            <button class="w-full py-3 bg-chocolate text-white font-bold rounded-lg hover:bg-chocolate-dark shadow-md transition flex items-center justify-center gap-2">
                <i class="fas fa-paper-plane"></i> Submit Request
            </button>
        </div>
    </div>

</div>

<script>
    function addToCart(name, unit) {
        // In a real app, this would add to a JS array or Livewire component
        // For prototype, we can just show a visual feedback
        const btn = event.currentTarget;
        const originalContent = btn.innerHTML;
        
        btn.innerHTML = '<i class="fas fa-check"></i> Added';
        btn.classList.remove('bg-gray-100', 'text-gray-700');
        btn.classList.add('bg-green-600', 'text-white');
        
        setTimeout(() => {
            btn.innerHTML = originalContent;
            btn.classList.add('bg-gray-100', 'text-gray-700');
            btn.classList.remove('bg-green-600', 'text-white');
        }, 1500);
    }
</script>

@endsection