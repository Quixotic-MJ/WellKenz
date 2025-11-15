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
                    <a href="{{ Route::has('admin.dashboard') ? route('admin.dashboard') : '#' }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('admin.dashboard') ? 'active-menu' : '' }}">
                        <i
                            class="fas fa-tachometer-alt w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Dashboard</span>
                    </a>
                </li>

                <!-- Requisition -->
                <li>
                    <a href="{{ Route::has('admin.requisitions') ? route('admin.requisitions') : '#' }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('admin.requisitions*') ? 'active-menu' : '' }}">
                        <i
                            class="fas fa-clipboard-list w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span
                            class="sidebar-text font-medium text-sm transition-opacity duration-300">Requisition</span>
                    </a>
                </li>

                <!-- Item Request -->
                <li>
                    <a href="{{ Route::has('admin.item-requests') ? route('admin.item-requests') : '#' }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('admin.item-requests*') ? 'active-menu' : '' }}">
                        <i
                            class="fas fa-clipboard-check w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span
                            class="sidebar-text font-medium text-sm transition-opacity duration-300">Item Request</span>
                    </a>
                </li>

                <!-- Purchasing -->
                <li>
                    <a href="{{ Route::has('admin.purchase-orders') ? route('admin.purchase-orders') : '#' }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('admin.purchase-orders*') ? 'active-menu' : '' }}">
                        <i
                            class="fas fa-shopping-cart w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Purchase Order</span>
                    </a>
                </li>

                <!-- Supplier -->
                <li>
                    <a href="{{ Route::has('admin.suppliers') ? route('admin.suppliers') : '#' }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('admin.suppliers*') ? 'active-menu' : '' }}">
                        <i
                            class="fas fa-truck w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Supplier</span>
                    </a>
                </li>

                <!-- Inventory -->
                <li>
                    <a href="{{ Route::has('admin.item-management') ? route('admin.item-management') : '#' }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('admin.item-management*') ? 'active-menu' : '' }}">
                        <i
                            class="fas fa-warehouse w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Inventory Management</span>
                    </a>
                </li>

                <!-- Inventory Transaction -->
                <li>
                    <a href="{{ Route::has('admin.inventory-transactions') ? route('admin.inventory-transactions') : '#' }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('admin.inventory-transactions*') ? 'active-menu' : '' }}">
                        <i
                            class="fas fa-exchange-alt w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Inventory Transaction</span>
                    </a>
                </li>

                <!-- Reports & Analytics -->
                <li>
                    <a href="{{ Route::has('admin.reports') ? route('admin.reports') : '#' }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('admin.reports*') ? 'active-menu' : '' }}">
                        <i
                            class="fas fa-chart-bar w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Reports &
                            Analytics</span>
                    </a>
                </li>

                <!-- User Management -->
                <li>
                    <a href="{{ Route::has('admin.user-management') ? route('admin.user-management') : '#' }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('admin.user-management*') ? 'active-menu' : '' }}">
                        <i
                            class="fas fa-users-cog w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">User
                            Management</span>
                    </a>
                </li>

                <!-- Notification -->
                <li>
                    <a href="{{ Route::has('admin.notifications') ? route('admin.notifications') : '#' }}"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('admin.notifications*') ? 'active-menu' : '' }}">
                        <i class="fas fa-bell w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span
                            class="sidebar-text font-medium text-sm transition-opacity duration-300">Notification</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</aside>

<style>
    /* This style block defines the 'active-menu' class used by Blade */
    .active-menu {
        background-color: rgba(255, 255, 255, 0.15);
        color: white !important;
        border-left-color: #D2691E !important; /* caramel color */
    }
</style>