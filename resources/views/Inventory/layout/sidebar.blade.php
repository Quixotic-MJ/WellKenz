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
                        <i
                            class="fas fa-tachometer-alt w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Dashboard</span>
                    </a>
                </li>

                <!-- Transactions (Inventory List) -->
                <li>
                    <a href="{{ route('inventory.transactions.index') }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('inventory.transactions.index') ? 'active-menu' : '' }}">
                        <i
                            class="fas fa-clipboard-list w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span
                            class="sidebar-text font-medium text-sm transition-opacity duration-300">Transactions</span>
                    </a>
                </li>

                <!-- Stock In -->
                <li>
                    <a href="{{ route('inventory.stock-in.index') }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('inventory.stock-in.index') ? 'active-menu' : '' }}">
                        <i
                            class="fas fa-arrow-down w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Stock In</span>
                    </a>
                </li>

                <!-- Reports -->
                <li>
                    <a href="{{ route('inventory.reports') }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('inventory.reports') ? 'active-menu' : '' }}">
                        <i
                            class="fas fa-chart-bar w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Reports</span>
                    </a>
                </li>

                <!-- Stock Out -->
                <li>
                    <a href="{{ route('inventory.stock-out.index') }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('inventory.stock-out.index') ? 'active-menu' : '' }}">
                        <i
                            class="fas fa-arrow-up w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Stock Out</span>
                    </a>
                </li>

                <!-- Low Stock Alert -->
                <li>
                    <a href="{{ route('inventory.alerts.index') }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('inventory.alerts.index') ? 'active-menu' : '' }}">
                        <i
                            class="fas fa-exclamation-triangle w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Low Stock
                            Alert</span>
                    </a>
                </li>

                <!-- Reports & Analytics (duplicate kept for backward compatibility - points to reports) -->
                <li>
                    <a href="{{ route('inventory.reports') }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('inventory.reports') ? 'active-menu' : '' }}">
                        <i
                            class="fas fa-chart-bar w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Reports &
                            Analytics</span>
                    </a>
                </li>

                <!-- Notifications -->
                <li>
                    <a href="{{ route('inventory.notifications.index') }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('inventory.notifications.index') ? 'active-menu' : '' }}">
                        <i class="fas fa-bell w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span
                            class="sidebar-text font-medium text-sm transition-opacity duration-300">Notifications</span>
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
