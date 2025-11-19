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
                    <i class="fas fa-shopping-cart text-caramel text-lg animate-float"></i>
                </div>
                <div class="sidebar-text transition-opacity duration-300">
                    <h1 class="font-display text-xl font-bold tracking-wide">WellKenz</h1>
                    <p class="text-xs text-white/60 uppercase tracking-widest">Purchasing</p>
                </div>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto py-4 no-scrollbar">
            <ul class="space-y-1 px-3">
                
                <!-- 1. DASHBOARD -->
                <li>
                    <a href="{{ Route::has('purchasing.dashboard') ? route('purchasing.dashboard') : '#' }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('purchasing.dashboard') ? 'active-menu' : '' }}">
                        <i class="fas fa-tachometer-alt w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Procurement Home</span>
                    </a>
                </li>

                <!-- 2. PURCHASE ORDERS (The Workflow) -->
                <li class="pt-4">
                    <div class="px-4 py-2 text-xs font-semibold text-white/50 uppercase tracking-wider">Purchase Orders</div>
                </li>

                <!-- Create New PO -->
                <li>
                    <a href="{{ Route::has('purchasing.po.create') ? route('purchasing.po.create') : '#' }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('purchasing.po.create') ? 'active-menu' : '' }}">
                        <i class="fas fa-plus-circle w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Create New PO</span>
                    </a>
                </li>

                <!-- Drafts -->
                <li>
                    <a href="{{ Route::has('purchasing.po.drafts') ? route('purchasing.po.drafts') : '#' }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('purchasing.po.drafts') ? 'active-menu' : '' }}">
                        <i class="fas fa-pencil-ruler w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Drafts</span>
                    </a>
                </li>

                <!-- Open Orders -->
                <li>
                    <a href="{{ Route::has('purchasing.po.open') ? route('purchasing.po.open') : '#' }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('purchasing.po.open') ? 'active-menu' : '' }}">
                        <i class="fas fa-paper-plane w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Open Orders (Sent)</span>
                    </a>
                </li>

                <!-- Partial / Backorders -->
                <li>
                    <a href="{{ Route::has('purchasing.po.partial') ? route('purchasing.po.partial') : '#' }}"
                        class="menu-item group flex items-center justify-between px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('purchasing.po.partial') ? 'active-menu' : '' }}">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-exclamation-triangle w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                            <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Partial / Backorders</span>
                        </div>
                        <!-- Warning Badge -->
                        <span class="bg-amber-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm sidebar-text">!</span>
                    </a>
                </li>

                <!-- Completed History -->
                <li>
                    <a href="{{ Route::has('purchasing.po.history') ? route('purchasing.po.history') : '#' }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('purchasing.po.history') ? 'active-menu' : '' }}">
                        <i class="fas fa-archive w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Completed History</span>
                    </a>
                </li>

                <!-- 3. SUPPLIERS -->
                <li class="pt-4">
                    <div class="px-4 py-2 text-xs font-semibold text-white/50 uppercase tracking-wider">Suppliers</div>
                </li>

                <!-- Vendor Masterlist -->
                <li>
                    <a href="{{ Route::has('purchasing.suppliers.index') ? route('purchasing.suppliers.index') : '#' }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('purchasing.suppliers.index') ? 'active-menu' : '' }}">
                        <i class="fas fa-address-book w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Vendor Masterlist</span>
                    </a>
                </li>

                <!-- Price Lists -->
                <li>
                    <a href="{{ Route::has('purchasing.suppliers.prices') ? route('purchasing.suppliers.prices') : '#' }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('purchasing.suppliers.prices') ? 'active-menu' : '' }}">
                        <i class="fas fa-tags w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Price Lists</span>
                    </a>
                </li>

                <!-- 4. REPORTS -->
                <li class="pt-4">
                    <div class="px-4 py-2 text-xs font-semibold text-white/50 uppercase tracking-wider">Reports</div>
                </li>

                <!-- Purchase History -->
                <li>
                    <a href="{{ Route::has('purchasing.reports.history') ? route('purchasing.reports.history') : '#' }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('purchasing.reports.history') ? 'active-menu' : '' }}">
                        <i class="fas fa-chart-pie w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Spending Analysis</span>
                    </a>
                </li>

                <!-- Supplier Performance -->
                <li>
                    <a href="{{ Route::has('purchasing.reports.performance') ? route('purchasing.reports.performance') : '#' }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('purchasing.reports.performance') ? 'active-menu' : '' }}">
                        <i class="fas fa-stopwatch w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Supplier Performance</span>
                    </a>
                </li>

                <!-- RTV Logs -->
                <li>
                    <a href="{{ Route::has('purchasing.reports.rtv') ? route('purchasing.reports.rtv') : '#' }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('purchasing.reports.rtv') ? 'active-menu' : '' }}">
                        <i class="fas fa-undo-alt w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">RTV Logs (Returns)</span>
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