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
                <div
                    class="w-10 h-10 bg-caramel/20 backdrop-blur-sm flex items-center justify-center border border-caramel/30 flex-shrink-0">
                    <i class="fas fa-user-tie text-caramel text-lg animate-float"></i>
                </div>
                <div class="sidebar-text transition-opacity duration-300">
                    <h1 class="font-display text-xl font-bold tracking-wide">WellKenz</h1>
                    <p class="text-xs text-white/60 uppercase tracking-widest">Supervisor Portal</p>
                </div>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto py-4 no-scrollbar">
            <ul class="space-y-1 px-3">
                
                <!-- 1. DASHBOARD -->
                <li>
                    <a href="{{ Route::has('supervisor.dashboard') ? route('supervisor.dashboard') : '#' }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('supervisor.dashboard') ? 'active-menu' : '' }}">
                        <i class="fas fa-tachometer-alt w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Manager Home</span>
                    </a>
                </li>

                <!-- 2. APPROVALS (The Inbox) -->
                <li class="pt-4">
                    <div class="px-4 py-2 text-xs font-semibold text-white/50 uppercase tracking-wider">Approvals (Inbox)</div>
                </li>

                <!-- Requisitions -->
                <li>
                    <a href="{{ Route::has('supervisor.approvals.requisitions') ? route('supervisor.approvals.requisitions') : '#' }}"
                        class="menu-item group flex items-center justify-between px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('supervisor.approvals.requisitions') ? 'active-menu' : '' }}">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-clipboard-check w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                            <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Requisitions</span>
                        </div>
                        <!-- Badge for 5 Pending -->
                        <span class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm sidebar-text">5</span>
                    </a>
                </li>

                <!-- Purchase Requests -->
                <li>
                    <a href="{{ Route::has('supervisor.approvals.purchase-requests') ? route('supervisor.approvals.purchase-requests') : '#' }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('supervisor.approvals.purchase-requests') ? 'active-menu' : '' }}">
                        <i class="fas fa-shopping-basket w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Purchase Requests</span>
                    </a>
                </li>

                <!-- 3. INVENTORY OVERSIGHT -->
                <li class="pt-4">
                    <div class="px-4 py-2 text-xs font-semibold text-white/50 uppercase tracking-wider">Inventory Oversight</div>
                </li>

                <!-- Stock Levels -->
                <li>
                    <a href="{{ Route::has('supervisor.inventory.index') ? route('supervisor.inventory.index') : '#' }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('supervisor.inventory.index') ? 'active-menu' : '' }}">
                        <i class="fas fa-warehouse w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Stock Levels (Live)</span>
                    </a>
                </li>

                <!-- Stock Card (History) -->
                <li>
                    <a href="{{ Route::has('supervisor.inventory.history') ? route('supervisor.inventory.history') : '#' }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('supervisor.inventory.history') ? 'active-menu' : '' }}">
                        <i class="fas fa-history w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Stock Card / History</span>
                    </a>
                </li>

                <!-- Adjustments / Write-offs -->
                <li>
                    <a href="{{ Route::has('supervisor.inventory.adjustments') ? route('supervisor.inventory.adjustments') : '#' }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('supervisor.inventory.adjustments') ? 'active-menu' : '' }}">
                        <i class="fas fa-file-invoice-dollar w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Write-offs & Adjustments</span>
                    </a>
                </li>

                <!-- 4. REPORTS & ANALYTICS -->
                <li class="pt-4">
                    <div class="px-4 py-2 text-xs font-semibold text-white/50 uppercase tracking-wider">Reports & Analytics</div>
                </li>

                <!-- Yield Variance -->
                <li>
                    <a href="{{ Route::has('supervisor.reports.yield') ? route('supervisor.reports.yield') : '#' }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('supervisor.reports.yield') ? 'active-menu' : '' }}">
                        <i class="fas fa-chart-line w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Yield Variance</span>
                    </a>
                </li>

                <!-- Expiry Report -->
                <li>
                    <a href="{{ Route::has('supervisor.reports.expiry') ? route('supervisor.reports.expiry') : '#' }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('supervisor.reports.expiry') ? 'active-menu' : '' }}">
                        <i class="fas fa-hourglass-end w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Expiry Report</span>
                    </a>
                </li>

                <!-- COGS -->
                <li>
                    <a href="{{ Route::has('supervisor.reports.cogs') ? route('supervisor.reports.cogs') : '#' }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('supervisor.reports.cogs') ? 'active-menu' : '' }}">
                        <i class="fas fa-coins w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Cost of Goods (COGS)</span>
                    </a>
                </li>

                <!-- 5. BRANCH SETTINGS -->
                <li class="pt-4">
                    <div class="px-4 py-2 text-xs font-semibold text-white/50 uppercase tracking-wider">Branch Settings</div>
                </li>

                <!-- Minimum Stock Levels -->
                <li>
                    <a href="{{ Route::has('supervisor.settings.stock-levels') ? route('supervisor.settings.stock-levels') : '#' }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('supervisor.settings.stock-levels') ? 'active-menu' : '' }}">
                        <i class="fas fa-sliders-h w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Min. Stock Levels</span>
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