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
                    <p class="text-xs text-white/60 uppercase tracking-widest">Admin Config</p>
                </div>
            </div>
        </div>

        <nav class="flex-1 overflow-y-auto py-4 no-scrollbar">
            <ul class="space-y-1 px-3">
                
                <!-- 1. DASHBOARD -->
                <li>
                    <a href="{{ Route::has('admin.dashboard') ? route('admin.dashboard') : '#' }}"
                        id="menu-admin-dashboard"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('admin.dashboard') ? 'active-menu' : '' }}">
                        <i class="fas fa-tachometer-alt w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">System Overview</span>
                    </a>
                </li>

                <li class="pt-4">
                    <div class="px-4 py-2 text-xs font-semibold text-white/50 uppercase tracking-wider">User Management</div>
                </li>

                <li>
                    <a href="{{ Route::has('admin.users.index') ? route('admin.users.index') : '#' }}"
                        id="menu-admin-users"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('admin.users*') ? 'active-menu' : '' }}">
                        <i class="fas fa-users w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">User Management</span>
                    </a>
                </li>

                <li class="pt-4">
                    <div class="px-4 py-2 text-xs font-semibold text-white/50 uppercase tracking-wider">Master Files</div>
                </li>

                <li>
                    <a href="{{ Route::has('admin.items.index') ? route('admin.items.index') : '#' }}"
                        id="menu-admin-items"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('admin.items*') ? 'active-menu' : '' }}">
                        <i class="fas fa-database w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Item Masterlist</span>
                    </a>
                </li>

                <li>
                    <a href="{{ Route::has('admin.categories.index') ? route('admin.categories.index') : '#' }}"
                        id="menu-admin-categories"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('admin.categories*') ? 'active-menu' : '' }}">
                        <i class="fas fa-tags w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Categories</span>
                    </a>
                </li>

                <li>
                    <a href="{{ Route::has('admin.units.index') ? route('admin.units.index') : '#' }}"
                        id="menu-admin-units"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('admin.units*') ? 'active-menu' : '' }}">
                        <i class="fas fa-balance-scale w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Unit Config</span>
                    </a>
                </li>

                <li class="pt-4">
                    <div class="px-4 py-2 text-xs font-semibold text-white/50 uppercase tracking-wider">External Partners</div>
                </li>

                <li>
                    <a href="{{ Route::has('admin.suppliers.index') ? route('admin.suppliers.index') : '#' }}"
                        id="menu-admin-suppliers"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('admin.suppliers*') ? 'active-menu' : '' }}">
                        <i class="fas fa-truck w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Suppliers List</span>
                    </a>
                </li>

                <li class="pt-4">
                    <div class="px-4 py-2 text-xs font-semibold text-white/50 uppercase tracking-wider">System & Security</div>
                </li>

                <li>
                    <a href="{{ Route::has('admin.audit-logs') ? route('admin.audit-logs') : '#' }}"
                        id="menu-admin-audit"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('admin.audit-logs*') ? 'active-menu' : '' }}">
                        <i class="fas fa-file-contract w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Audit Logs</span>
                    </a>
                </li>

                <li>
                    <a href="{{ Route::has('admin.settings') ? route('admin.settings') : '#' }}"
                        id="menu-admin-settings"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('admin.settings*') ? 'active-menu' : '' }}">
                        <i class="fas fa-cogs w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">General Settings</span>
                    </a>
                </li>

                <li>
                    <a href="{{ Route::has('admin.backups') ? route('admin.backups') : '#' }}"
                        id="menu-admin-backups"
                        class="menu-item group flex items-center space-x-3 px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('admin.backups*') ? 'active-menu' : '' }}">
                        <i class="fas fa-cloud-download-alt w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                        <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Backup & Restore</span>
                    </a>
                </li>

                <!-- Notifications -->
                <li>
                    <a href="{{ Route::has('admin.notifications') ? route('admin.notifications') : '#' }}"
                        id="menu-admin-notifications"
                        class="menu-item group flex items-center justify-between px-4 py-3 text-white/70 hover:text-white hover:bg-white/10 transition-all duration-200 border-l-3 border-transparent hover:border-caramel {{ request()->routeIs('admin.notifications') ? 'active-menu' : '' }}">
                        <div class="flex items-center space-x-3">
                            <i class="fas fa-bell w-5 text-center text-sm group-hover:scale-110 transition-transform"></i>
                            <span class="sidebar-text font-medium text-sm transition-opacity duration-300">Notifications</span>
                        </div>
                        <!-- Badge for unread notifications -->
                        <span class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-sm sidebar-text">3</span>
                    </a>
                </li>

            </ul>
        </nav>
    </div>
</aside>

<style>
    .active-menu {
        background-color: rgba(255, 255, 255, 0.15) !important;
        color: white !important;
        border-left-color: #D2691E !important; /* caramel color */
    }
    
    .active-menu i {
        color: white !important;
    }
    
    .active-menu span {
        color: white !important;
    }
</style>