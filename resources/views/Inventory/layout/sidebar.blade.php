<aside id="sidebar" 
    class="sidebar w-64 flex flex-col h-screen sticky top-0 bg-chocolate text-white border-r border-white/5 transition-all duration-300 z-40 font-sans shadow-2xl">
    
    {{-- 1. BRANDING SECTION --}}
    <div class="relative z-10 p-6 pb-8 border-b border-white/10">
        {{-- Decorative Glow --}}
        <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
        
        <div class="relative flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-caramel to-chocolate-dark border border-white/10 flex items-center justify-center shadow-lg">
                <i class="fas fa-warehouse text-white text-lg"></i>
            </div>
            <div>
                <h1 class="font-display text-2xl font-bold tracking-wide text-white leading-none">WellKenz</h1>
                <p class="text-[10px] text-caramel font-bold uppercase tracking-[0.2em] mt-1">Inventory</p>
            </div>
        </div>
    </div>

    {{-- 2. NAVIGATION --}}
    <nav class="flex-1 overflow-y-auto custom-scrollbar px-4 py-6 space-y-1">

        {{-- DASHBOARD --}}
        <a href="{{ Route::has('inventory.dashboard') ? route('inventory.dashboard') : '#' }}"
           id="menu-inventory-dashboard"
           class="group flex items-center px-3 py-3 rounded-lg text-sm font-medium transition-all duration-200 mb-6
           {{ request()->routeIs('inventory.dashboard') 
              ? 'bg-white/10 text-white border-l-2 border-caramel' 
              : 'text-white/70 hover:bg-white/5 hover:text-white border-l-2 border-transparent' }}">
            <i class="fas fa-tachometer-alt w-6 text-center text-sm {{ request()->routeIs('inventory.dashboard') ? 'text-caramel' : 'text-white/50 group-hover:text-white transition-colors' }}"></i>
            <span class="ml-2">Warehouse Home</span>
        </a>

        {{-- SECTION: INBOUND --}}
        <div class="px-3 pt-2 pb-2">
            <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest font-display">Inbound Operations</p>
        </div>

        {{-- Receive Delivery --}}
        <a href="{{ Route::has('inventory.inbound.receive') ? route('inventory.inbound.receive') : '#' }}"
           id="menu-inventory-inbound-receive"
           class="group flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
           {{ request()->routeIs('inventory.inbound.*') 
              ? 'bg-white/10 text-white border-l-2 border-caramel' 
              : 'text-white/70 hover:bg-white/5 hover:text-white border-l-2 border-transparent' }}">
            <i class="fas fa-truck-loading w-6 text-center text-sm {{ request()->routeIs('inventory.inbound.*') ? 'text-caramel' : 'text-white/50 group-hover:text-white transition-colors' }}"></i>
            <span class="ml-2">Receive Delivery</span>
        </a>

        {{-- Batch Records --}}
        <a href="{{ Route::has('inventory.inbound.labels') ? route('inventory.inbound.labels') : '#' }}"
           id="menu-inventory-inbound-labels"
           class="group flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
           {{ request()->routeIs('inventory.inbound.*') 
              ? 'bg-white/10 text-white border-l-2 border-caramel' 
              : 'text-white/70 hover:bg-white/5 hover:text-white border-l-2 border-transparent' }}">
            <i class="fas fa-qrcode w-6 text-center text-sm {{ request()->routeIs('inventory.inbound.*') ? 'text-caramel' : 'text-white/50 group-hover:text-white transition-colors' }}"></i>
            <span class="ml-2">Batch Records</span>
        </a>

        {{-- RTV --}}
        <a href="{{ Route::has('inventory.inbound.rtv') ? route('inventory.inbound.rtv') : '#' }}"
           id="menu-inventory-inbound-rtv"
           class="group flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
           {{ request()->routeIs('inventory.inbound.*') 
              ? 'bg-white/10 text-white border-l-2 border-caramel' 
              : 'text-white/70 hover:bg-white/5 hover:text-white border-l-2 border-transparent' }}">
            <i class="fas fa-undo-alt w-6 text-center text-sm {{ request()->routeIs('inventory.inbound.*') ? 'text-caramel' : 'text-white/50 group-hover:text-white transition-colors' }}"></i>
            <span class="ml-2">Log Returns (RTV)</span>
        </a>

        {{-- SECTION: OUTBOUND --}}
        <div class="px-3 pt-6 pb-2">
            <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest font-display">Outbound & Requests</p>
        </div>

        {{-- Fulfill Requests --}}
        <a href="{{ Route::has('inventory.outbound.fulfill') ? route('inventory.outbound.fulfill') : '#' }}"
           id="menu-inventory-outbound-fulfill"
           class="group flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
           {{ request()->routeIs('inventory.outbound.*') 
              ? 'bg-white/10 text-white border-l-2 border-caramel' 
              : 'text-white/70 hover:bg-white/5 hover:text-white border-l-2 border-transparent' }}">
            <div class="flex items-center">
                <i class="fas fa-dolly w-6 text-center text-sm {{ request()->routeIs('inventory.outbound.*') ? 'text-caramel' : 'text-white/50 group-hover:text-white transition-colors' }}"></i>
                <span class="ml-2">Fulfill Requests</span>
            </div>
            {{-- Dynamic count badge --}}
            @if(isset($pendingRequisitionsCount) && $pendingRequisitionsCount > 0)
            <span class="bg-green-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded min-w-[1.25rem] text-center shadow-sm">
                {{ $pendingRequisitionsCount }}
            </span>
            @endif
        </a>

        {{-- Create Purchase Request --}}
        <a href="{{ Route::has('inventory.purchase-requests.create') ? route('inventory.purchase-requests.create') : '#' }}"
           id="menu-inventory-purchase-requests-create"
           class="group flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
           {{ request()->routeIs('inventory.purchase-requests.*') 
              ? 'bg-white/10 text-white border-l-2 border-caramel' 
              : 'text-white/70 hover:bg-white/5 hover:text-white border-l-2 border-transparent' }}">
            <i class="fas fa-cart-plus w-6 text-center text-sm {{ request()->routeIs('inventory.purchase-requests.*') ? 'text-caramel' : 'text-white/50 group-hover:text-white transition-colors' }}"></i>
            <span class="ml-2">Create Purchase Request</span>
        </a>

        {{-- SECTION: STOCK MANAGEMENT --}}
        <div class="px-3 pt-6 pb-2">
            <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest font-display">Stock Management</p>
        </div>

        {{-- Batch Lookup --}}
        <a href="{{ Route::has('inventory.stock.lookup') ? route('inventory.stock.lookup') : '#' }}"
           id="menu-inventory-stock-lookup"
           class="group flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
           {{ request()->routeIs('inventory.stock.*') 
              ? 'bg-white/10 text-white border-l-2 border-caramel' 
              : 'text-white/70 hover:bg-white/5 hover:text-white border-l-2 border-transparent' }}">
            <i class="fas fa-search-location w-6 text-center text-sm {{ request()->routeIs('inventory.stock.*') ? 'text-caramel' : 'text-white/50 group-hover:text-white transition-colors' }}"></i>
            <span class="ml-2">Batch Lookup</span>
        </a>

        {{-- SECTION: SYSTEM --}}
        <div class="px-3 pt-6 pb-2">
            <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest font-display">System</p>
        </div>

        {{-- Notifications --}}
        <a href="{{ Route::has('inventory.notifications') ? route('inventory.notifications') : '#' }}"
           id="menu-inventory-notifications"
           class="group flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
           {{ request()->routeIs('inventory.notifications*') 
              ? 'bg-white/10 text-white border-l-2 border-caramel' 
              : 'text-white/70 hover:bg-white/5 hover:text-white border-l-2 border-transparent' }}">
            <div class="flex items-center">
                <i class="fas fa-bell w-6 text-center text-sm {{ request()->routeIs('inventory.notifications*') ? 'text-caramel' : 'text-white/50 group-hover:text-white transition-colors' }}"></i>
                <span class="ml-2">Notifications</span>
            </div>
            @if(isset($unreadNotificationsCount) && $unreadNotificationsCount > 0)
            <span class="bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded min-w-[1.25rem] text-center shadow-sm">
                {{ $unreadNotificationsCount }}
            </span>
            @endif
        </a>

    </nav>

    {{-- DECORATIVE FOOTER ELEMENT --}}
    <div class="p-4 relative overflow-hidden mt-auto">
        <div class="absolute -bottom-10 -right-10 w-32 h-32 bg-caramel/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative z-10 flex items-center gap-2 text-[10px] text-white/30 uppercase tracking-widest">
            <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div> System Online
        </div>
    </div>
</aside>

{{-- Custom Scrollbar Style (Inline for isolation or move to CSS) --}}
<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.05);
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.4);
    }
</style>