<aside id="sidebar"
    class="sidebar w-64 flex flex-col text-white relative overflow-hidden bg-chocolate transition-all duration-300 ease-in-out">
    <!-- Overlay gradient -->
    <div class="absolute inset-0 bg-gradient-to-br from-chocolate/95 via-chocolate/90 to-chocolate-dark/95"></div>

    <!-- Decorative Elements -->
    <div class="absolute top-20 -right-20 w-64 h-64 bg-caramel/10 rounded-full blur-3xl"></div>
    <div class="absolute bottom-20 -left-20 w-64 h-64 bg-caramel/10 rounded-full blur-3xl"></div>

    <div class="relative z-10 flex flex-col h-full">
        <!-- Logo Section -->
        <div class="p-6 border-b border-white/10">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-caramel/20 backdrop-blur-sm flex items-center justify-center border border-caramel/30 flex-shrink-0">
                    <i class="fas fa-warehouse text-caramel text-lg animate-float"></i>
                </div>
                <div class="sidebar-text transition-opacity duration-300">
                    <h1 class="font-display text-xl font-bold tracking-wide">WellKenz</h1>
                    <p class="text-xs text-white/60 uppercase tracking-widest">Inventory</p>
                </div>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto py-4 no-scrollbar">
            <ul class="space-y-1 px-3">
                
                <!-- 1. DASHBOARD -->
                <li>
                    <a href="{{ Route::has('inventory.dashboard') ? route('inventory.dashboard') : '#' }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('inventory.dashboard') ? 'active-menu' : '' }}">
                        <i class="fas fa-tachometer-alt w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Warehouse Home</span>
                    </a>
                </li>

                <!-- 2. INBOUND (Receiving) -->
                <li class="pt-4">
                    <div class="px-4 py-2 text-xs font-semibold text-white/50 uppercase tracking-wider">Inbound Operations</div>
                </li>

                <!-- Receive Delivery -->
                <li>
                    <a href="{{ Route::has('inventory.inbound.receive') ? route('inventory.inbound.receive') : '#' }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('inventory.inbound.receive') ? 'active-menu' : '' }}">
                        <i class="fas fa-truck-loading w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Receive Delivery</span>
                    </a>
                </li>

                <!-- Print Batch Labels -->
                <li>
                    <a href="{{ Route::has('inventory.inbound.labels') ? route('inventory.inbound.labels') : '#' }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('inventory.inbound.labels') ? 'active-menu' : '' }}">
                        <i class="fas fa-qrcode w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Print Batch Labels</span>
                    </a>
                </li>

                <!-- Return to Vendor (RTV) -->
                <li>
                    <a href="{{ Route::has('inventory.inbound.rtv') ? route('inventory.inbound.rtv') : '#' }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('inventory.inbound.rtv') ? 'active-menu' : '' }}">
                        <i class="fas fa-undo-alt w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Log Returns (RTV)</span>
                    </a>
                </li>

                <!-- 3. OUTBOUND (Issuance) -->
                <li class="pt-4">
                    <div class="px-4 py-2 text-xs font-semibold text-white/50 uppercase tracking-wider">Outbound Operations</div>
                </li>

                <!-- Fulfill Requests -->
                <li>
                    <a href="{{ Route::has('inventory.outbound.fulfill') ? route('inventory.outbound.fulfill') : '#' }}"
                        class="menu-item group flex items-center justify-between px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('inventory.outbound.fulfill') ? 'active-menu' : '' }}">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-dolly w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                            <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Fulfill Requests</span>
                        </div>
                        <span class="bg-green-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm sidebar-text">3</span>
                    </a>
                </li>

                <!-- Direct Issuance (Restricted) -->
                <li>
                    <a href="{{ Route::has('inventory.outbound.direct') ? route('inventory.outbound.direct') : '#' }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('inventory.outbound.direct') ? 'active-menu' : '' }}">
                        <i class="fas fa-hand-holding-box w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Direct Issuance <i class="fas fa-lock text-[10px] ml-1 opacity-50"></i></span>
                    </a>
                </li>

                <!-- 4. STOCK MANAGEMENT -->
                <li class="pt-4">
                    <div class="px-4 py-2 text-xs font-semibold text-white/50 uppercase tracking-wider">Stock Management</div>
                </li>

                <!-- Physical Count -->
                <li>
                    <a href="{{ Route::has('inventory.stock.count') ? route('inventory.stock.count') : '#' }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('inventory.stock.count') ? 'active-menu' : '' }}">
                        <i class="fas fa-clipboard-list w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Physical Count</span>
                    </a>
                </li>

                <!-- Batch Lookup -->
                <li>
                    <a href="{{ Route::has('inventory.stock.lookup') ? route('inventory.stock.lookup') : '#' }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('inventory.stock.lookup') ? 'active-menu' : '' }}">
                        <i class="fas fa-search-location w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Batch Lookup</span>
                    </a>
                </li>

                <!-- Stock Transfer -->
                <li>
                    <a href="{{ Route::has('inventory.stock.transfer') ? route('inventory.stock.transfer') : '#' }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('inventory.stock.transfer') ? 'active-menu' : '' }}">
                        <i class="fas fa-exchange-alt w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Stock Transfer</span>
                    </a>
                </li>

            </ul>
        </nav>
    </div>
</aside>

<style>
    .active-menu {
        background-color: rgba(255, 255, 255, 0.15);
        color: white !important;
        border-left-color: #D2691E !important; /* caramel color */
    }
</style>