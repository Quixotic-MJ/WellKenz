<aside id="sidebar" 
    class="sidebar w-64 flex flex-col h-screen sticky top-0 bg-chocolate text-white border-r border-white/5 transition-all duration-300 z-40 font-sans shadow-2xl">
    
    {{-- 1. BRANDING SECTION --}}
    <div class="relative z-10 p-6 pb-8 border-b border-white/10">
        {{-- Decorative Glow --}}
        <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
        
        <div class="relative flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-caramel to-chocolate-dark border border-white/10 flex items-center justify-center shadow-lg">
                <i class="fas fa-shopping-cart text-white text-lg"></i>
            </div>
            <div>
                <h1 class="font-display text-2xl font-bold tracking-wide text-white leading-none">WellKenz</h1>
                <p class="text-[10px] text-caramel font-bold uppercase tracking-[0.2em] mt-1">Purchasing</p>
            </div>
        </div>
    </div>

    {{-- 2. NAVIGATION --}}
    <nav class="flex-1 overflow-y-auto custom-scrollbar px-4 py-6 space-y-1">

        {{-- DASHBOARD --}}
        <a href="{{ Route::has('purchasing.dashboard') ? route('purchasing.dashboard') : '#' }}"
           id="menu-purchasing-dashboard"
           class="group flex items-center px-3 py-3 rounded-lg text-sm font-medium transition-all duration-200 mb-6
           {{ request()->routeIs('purchasing.dashboard') 
              ? 'bg-white/10 text-white border-l-2 border-caramel' 
              : 'text-white/70 hover:bg-white/5 hover:text-white border-l-2 border-transparent' }}">
            <i class="fas fa-tachometer-alt w-6 text-center text-sm {{ request()->routeIs('purchasing.dashboard') ? 'text-caramel' : 'text-white/50 group-hover:text-white transition-colors' }}"></i>
            <span class="ml-2">Procurement Hub</span>
        </a>

        {{-- SECTION: PURCHASE ORDERS --}}
        <div class="px-3 pt-2 pb-2">
            <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest font-display">Purchase Orders</p>
        </div>

        {{-- Create PO --}}
        <a href="{{ Route::has('purchasing.po.create') ? route('purchasing.po.create') : '#' }}"
           id="menu-purchasing-po-create"
           class="group flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
           {{ request()->routeIs('purchasing.po.create') 
              ? 'bg-white/10 text-white border-l-2 border-caramel' 
              : 'text-white/70 hover:bg-white/5 hover:text-white border-l-2 border-transparent' }}">
            <i class="fas fa-plus-circle w-6 text-center text-sm {{ request()->routeIs('purchasing.po.create') ? 'text-caramel' : 'text-white/50 group-hover:text-white transition-colors' }}"></i>
            <span class="ml-2">Create PO</span>
        </a>

        {{-- Bulk Configure PO --}}
        <a href="{{ Route::has('purchasing.po.bulk-configure') ? route('purchasing.po.bulk-configure') : '#' }}"
           id="menu-purchasing-po-bulk-configure"
           class="group flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
           {{ request()->routeIs('purchasing.po.bulk-configure') 
              ? 'bg-white/10 text-white border-l-2 border-caramel' 
              : 'text-white/70 hover:bg-white/5 hover:text-white border-l-2 border-transparent' }}">
            <i class="fas fa-cogs w-6 text-center text-sm {{ request()->routeIs('purchasing.po.bulk-configure') ? 'text-caramel' : 'text-white/50 group-hover:text-white transition-colors' }}"></i>
            <span class="ml-2">Bulk Configure PO</span>
        </a>


        {{-- Open Orders --}}
        <a href="{{ Route::has('purchasing.po.open') ? route('purchasing.po.open') : '#' }}"
           id="menu-purchasing-po-open"
           class="group flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
           {{ request()->routeIs('purchasing.po.open') 
              ? 'bg-white/10 text-white border-l-2 border-caramel' 
              : 'text-white/70 hover:bg-white/5 hover:text-white border-l-2 border-transparent' }}">
            <i class="fas fa-paper-plane w-6 text-center text-sm {{ request()->routeIs('purchasing.po.open') ? 'text-caramel' : 'text-white/50 group-hover:text-white transition-colors' }}"></i>
            <span class="ml-2">Open Orders</span>
        </a>

        {{-- Completed History --}}
        <a href="{{ Route::has('purchasing.po.history') ? route('purchasing.po.history') : '#' }}"
           id="menu-purchasing-po-history"
           class="group flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
           {{ request()->routeIs('purchasing.po.history') 
              ? 'bg-white/10 text-white border-l-2 border-caramel' 
              : 'text-white/70 hover:bg-white/5 hover:text-white border-l-2 border-transparent' }}">
            <i class="fas fa-history w-6 text-center text-sm {{ request()->routeIs('purchasing.po.history') ? 'text-caramel' : 'text-white/50 group-hover:text-white transition-colors' }}"></i>
            <span class="ml-2">Completed History</span>
        </a>

        {{-- SECTION: SUPPLIERS --}}
        <div class="px-3 pt-6 pb-2">
            <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest font-display">Supplier Management</p>
        </div>

        {{-- Supplier Masterlist --}}
        <a href="{{ Route::has('purchasing.suppliers.index') ? route('purchasing.suppliers.index') : '#' }}"
           id="menu-purchasing-suppliers-index"
           class="group flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
           {{ request()->routeIs('purchasing.suppliers*') 
              ? 'bg-white/10 text-white border-l-2 border-caramel' 
              : 'text-white/70 hover:bg-white/5 hover:text-white border-l-2 border-transparent' }}">
            <i class="fas fa-truck w-6 text-center text-sm {{ request()->routeIs('purchasing.suppliers*') ? 'text-caramel' : 'text-white/50 group-hover:text-white transition-colors' }}"></i>
            <span class="ml-2">Supplier Masterlist</span>
        </a>

        {{-- Price Lists --}}
        <a href="{{ Route::has('purchasing.suppliers.prices') ? route('purchasing.suppliers.prices') : '#' }}"
           id="menu-purchasing-suppliers-prices"
           class="group flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
           {{ request()->routeIs('purchasing.suppliers.prices') 
              ? 'bg-white/10 text-white border-l-2 border-caramel' 
              : 'text-white/70 hover:bg-white/5 hover:text-white border-l-2 border-transparent' }}">
            <i class="fas fa-tags w-6 text-center text-sm {{ request()->routeIs('purchasing.suppliers.prices') ? 'text-caramel' : 'text-white/50 group-hover:text-white transition-colors' }}"></i>
            <span class="ml-2">Price Lists</span>
        </a>

        {{-- SECTION: REPORTS --}}
        <div class="px-3 pt-6 pb-2">
            <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest font-display">Analysis</p>
        </div>

        {{-- Purchase History --}}
        <a href="{{ Route::has('purchasing.reports.history') ? route('purchasing.reports.history') : '#' }}"
           id="menu-purchasing-reports-history"
           class="group flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
           {{ request()->routeIs('purchasing.reports.history') 
              ? 'bg-white/10 text-white border-l-2 border-caramel' 
              : 'text-white/70 hover:bg-white/5 hover:text-white border-l-2 border-transparent' }}">
            <i class="fas fa-chart-bar w-6 text-center text-sm {{ request()->routeIs('purchasing.reports.history') ? 'text-caramel' : 'text-white/50 group-hover:text-white transition-colors' }}"></i>
            <span class="ml-2">Purchase History</span>
        </a>

        {{-- RTV Reports --}}
        <a href="{{ Route::has('purchasing.reports.rtv') ? route('purchasing.reports.rtv') : '#' }}"
           id="menu-purchasing-reports-rtv"
           class="group flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
           {{ request()->routeIs('purchasing.reports.rtv') 
              ? 'bg-white/10 text-white border-l-2 border-caramel' 
              : 'text-white/70 hover:bg-white/5 hover:text-white border-l-2 border-transparent' }}">
            <i class="fas fa-undo-alt w-6 text-center text-sm {{ request()->routeIs('purchasing.reports.rtv') ? 'text-caramel' : 'text-white/50 group-hover:text-white transition-colors' }}"></i>
            <span class="ml-2">RTV Reports</span>
        </a>

        {{-- SECTION: SYSTEM --}}
        <div class="px-3 pt-6 pb-2">
            <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest font-display">System</p>
        </div>

        {{-- Notifications --}}
        <a href="{{ Route::has('purchasing.notifications') ? route('purchasing.notifications') : '#' }}"
           id="menu-purchasing-notifications"
           class="group flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
           {{ request()->routeIs('purchasing.notifications*') 
              ? 'bg-white/10 text-white border-l-2 border-caramel' 
              : 'text-white/70 hover:bg-white/5 hover:text-white border-l-2 border-transparent' }}">
            <div class="flex items-center">
                <i class="fas fa-bell w-6 text-center text-sm {{ request()->routeIs('purchasing.notifications*') ? 'text-caramel' : 'text-white/50 group-hover:text-white transition-colors' }}"></i>
                <span class="ml-2">Notifications</span>
            </div>
            @if(isset($unreadNotificationsCount) && $unreadNotificationsCount > 0)
            <span class="bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded min-w-[1.25rem] text-center shadow-sm">
                {{ $unreadNotificationsCount }}
            </span>
            @else
            <span class="bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded min-w-[1.25rem] text-center shadow-sm">
                2
            </span>
            @endif
        </a>

    </nav>

    {{-- DECORATIVE FOOTER ELEMENT --}}
    <div class="p-4 relative overflow-hidden mt-auto">
        <div class="absolute -bottom-10 -right-10 w-32 h-32 bg-caramel/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative z-10 flex items-center gap-2 text-[10px] text-white/30 uppercase tracking-widest">
            <div class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></div> System Online
        </div>
    </div>
</aside>

{{-- Custom Scrollbar Style --}}
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