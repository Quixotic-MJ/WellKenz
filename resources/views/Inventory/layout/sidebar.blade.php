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
                    <i class="fas fa-birthday-cake text-caramel text-lg animate-float"></i>
                </div>
                <div class="sidebar-text transition-opacity duration-300">
                    <h1 class="font-display text-xl font-bold tracking-wide">WellKenz</h1>
                    <p class="text-xs text-white/60 uppercase tracking-widest">Cakes & Pastries</p>
                </div>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto py-4 no-scrollbar">
            <ul class="space-y-1 px-3">
                <!-- Dashboard -->
                <li>
                    <a href="{{ route('inventory.dashboard') }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('inventory.dashboard') ? 'active-menu' : '' }}">
                        <i class="fas fa-tachometer-alt w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Dashboard</span>
                    </a>
                </li>

                <!-- Item Management Section -->
                <li class="pt-2">
                    <div class="px-4 py-2 text-xs font-semibold text-white/50 uppercase tracking-wider">Item Management</div>
                </li>
                <li>
                    <a href="{{ route('inventory.items.list') }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover-border-caramel {{ request()->routeIs('inventory.items.list') ? 'active-menu' : '' }}">
                        <i class="fas fa-boxes w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Item List</span>
                    </a>
                </li>

                <!-- Stock Operations Section -->
                <li class="pt-2">
                    <div class="px-4 py-2 text-xs font-semibold text-white/50 uppercase tracking-wider">Stock Operations</div>
                </li>
                
                <!-- Incoming Deliveries -->
                <li>
                    <a href="{{ route('inventory.deliveries.incoming') }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('inventory.deliveries.incoming') ? 'active-menu' : '' }}">
                        <i class="fas fa-truck-loading w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Incoming Deliveries</span>
                    </a>
                </li>

                <!-- Stock-In Processing -->
                <li>
                    <a href="{{ route('inventory.stock-in.index') }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('inventory.stock-in.index') ? 'active-menu' : '' }}">
                        <i class="fas fa-arrow-down w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Stock-In Processing</span>
                    </a>
                </li>

                <!-- Stock-Out Processing -->
                <li>
                    <a href="{{ route('inventory.stock-out.index') }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('inventory.stock-out.index') ? 'active-menu' : '' }}">
                        <i class="fas fa-arrow-up w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Stock-Out Processing</span>
                    </a>
                </li>

                <!-- Stock Adjustments -->
                <li>
                    <a href="{{ route('inventory.adjustments.index') }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('inventory.adjustments.index') ? 'active-menu' : '' }}">
                        <i class="fas fa-sliders-h w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Stock Adjustments</span>
                    </a>
                </li>

                <!-- Monitoring Section -->
                <li class="pt-2">
                    <div class="px-4 py-2 text-xs font-semibold text-white/50 uppercase tracking-wider">Monitoring</div>
                </li>

                <!-- Alerts -->
                <li>
                    <a href="{{ route('inventory.alerts.index') }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('inventory.alerts.index') ? 'active-menu' : '' }}">
                        <i class="fas fa-exclamation-triangle w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Inventory Alerts</span>
                    </a>
                </li>

                <!-- Transactions History -->
                <li>
                    <a href="{{ route('inventory.transactions.index') }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('inventory.transactions.index') ? 'active-menu' : '' }}">
                        <i class="fas fa-exchange-alt w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Transaction History</span>
                    </a>
                </li>

                <!-- Acknowledgement Receipts -->
                <li>
                    <a href="{{ route('inventory.acknowledge-receipts.index') }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('inventory.acknowledge-receipts.index') ? 'active-menu' : '' }}">
                        <i class="fas fa-file-alt w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Acknowledgement Receipts</span>
                    </a>
                </li>

                <!-- Reports Section -->
                <li class="pt-2">
                    <div class="px-4 py-2 text-xs font-semibold text-white/50 uppercase tracking-wider">Reports</div>
                </li>
                <li>
                    <a href="{{ route('inventory.reports') }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('inventory.reports') ? 'active-menu' : '' }}">
                        <i class="fas fa-chart-bar w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Reports & Analytics</span>
                    </a>
                </li>

                <!-- System Section -->
                <li class="pt-2">
                    <div class="px-4 py-2 text-xs font-semibold text-white/50 uppercase tracking-wider">System</div>
                </li>
                <li>
                    <a href="{{ route('inventory.notifications.index') }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('inventory.notifications.index') ? 'active-menu' : '' }}">
                        <i class="fas fa-bell w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Notifications</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>

<style>
    .active-menu {
        background-color: rgba(255, 255, 255, 0.15);
        color: white;
        border-left-color: #D2691E !important;
        /* caramel color */
    }
</style>
