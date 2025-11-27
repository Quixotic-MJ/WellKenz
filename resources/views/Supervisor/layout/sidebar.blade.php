<aside id="sidebar" 
    class="sidebar w-64 flex flex-col h-screen sticky top-0 bg-chocolate text-white border-r border-white/5 transition-all duration-300 z-40 font-sans shadow-2xl">
    
    {{-- 1. BRANDING SECTION --}}
    <div class="relative z-10 p-6 pb-8 border-b border-white/10">
        {{-- Decorative Glow --}}
        <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
        
        <div class="relative flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-caramel to-chocolate-dark border border-white/10 flex items-center justify-center shadow-lg">
                <i class="fas fa-user-tie text-white text-lg"></i>
            </div>
            <div>
                <h1 class="font-display text-2xl font-bold tracking-wide text-white leading-none">WellKenz</h1>
                <p class="text-[10px] text-caramel font-bold uppercase tracking-[0.2em] mt-1">Supervisor</p>
            </div>
        </div>
    </div>

    {{-- 2. NAVIGATION --}}
    <nav class="flex-1 overflow-y-auto custom-scrollbar px-4 py-6 space-y-1">

        {{-- DASHBOARD --}}
        <a href="{{ Route::has('supervisor.dashboard') ? route('supervisor.dashboard') : '#' }}"
           id="menu-supervisor-dashboard"
           class="group flex items-center px-3 py-3 rounded-lg text-sm font-medium transition-all duration-200 mb-6
           {{ request()->routeIs('supervisor.dashboard') 
              ? 'bg-caramel text-white shadow-md shadow-caramel/20' 
              : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
            <i class="fas fa-tachometer-alt w-6 text-center text-sm {{ request()->routeIs('supervisor.dashboard') ? 'text-white' : 'text-white/50 group-hover:text-white transition-colors' }}"></i>
            <span class="ml-2">Manager Home</span>
        </a>

        {{-- SECTION: APPROVALS --}}
        <div class="px-3 pt-2 pb-2">
            <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest font-display">Approvals</p>
        </div>

        {{-- Requisitions --}}
        <a href="{{ Route::has('supervisor.approvals.requisitions') ? route('supervisor.approvals.requisitions') : '#' }}"
           id="menu-supervisor-approvals-requisitions"
           class="group flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
           {{ request()->routeIs('supervisor.approvals.requisitions') 
              ? 'bg-white/10 text-white border-l-2 border-caramel' 
              : 'text-white/70 hover:bg-white/5 hover:text-white border-l-2 border-transparent' }}">
            <div class="flex items-center">
                <i class="fas fa-clipboard-check w-6 text-center text-sm {{ request()->routeIs('supervisor.approvals.requisitions') ? 'text-caramel' : 'text-white/50 group-hover:text-white transition-colors' }}"></i>
                <span class="ml-2">Requisitions</span>
            </div>
            @if(isset($badgeCounts['pending_requisitions']) && $badgeCounts['pending_requisitions'] > 0)
            <span class="bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded min-w-[1.25rem] text-center shadow-sm">
                {{ $badgeCounts['pending_requisitions'] }}
            </span>
            @endif
        </a>

        {{-- Purchase Requests --}}
        <a href="{{ Route::has('supervisor.approvals.purchase-requests') ? route('supervisor.approvals.purchase-requests') : '#' }}"
           id="menu-supervisor-approvals-purchase-requests"
           class="group flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
           {{ request()->routeIs('supervisor.approvals.purchase-requests') 
              ? 'bg-white/10 text-white border-l-2 border-caramel' 
              : 'text-white/70 hover:bg-white/5 hover:text-white border-l-2 border-transparent' }}">
            <i class="fas fa-shopping-basket w-6 text-center text-sm {{ request()->routeIs('supervisor.approvals.purchase-requests') ? 'text-caramel' : 'text-white/50 group-hover:text-white transition-colors' }}"></i>
            <span class="ml-2">Purchase Requests</span>
        </a>

        {{-- SECTION: INVENTORY OVERSIGHT --}}
        <div class="px-3 pt-6 pb-2">
            <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest font-display">Inventory Oversight</p>
        </div>

        {{-- Stock Levels --}}
        <a href="{{ Route::has('supervisor.inventory.stock-level') ? route('supervisor.inventory.stock-level') : '#' }}"
           id="menu-supervisor-inventory-stock-level"
           class="group flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
           {{ request()->routeIs('supervisor.inventory.stock-level') 
              ? 'bg-white/10 text-white border-l-2 border-caramel' 
              : 'text-white/70 hover:bg-white/5 hover:text-white border-l-2 border-transparent' }}">
            <i class="fas fa-warehouse w-6 text-center text-sm {{ request()->routeIs('supervisor.inventory.stock-level') ? 'text-caramel' : 'text-white/50 group-hover:text-white transition-colors' }}"></i>
            <span class="ml-2">Stock Levels</span>
        </a>

        {{-- Stock Card/History --}}
        <a href="{{ Route::has('supervisor.inventory.stock-history') ? route('supervisor.inventory.stock-history') : '#' }}"
           id="menu-supervisor-inventory-stock-history"
           class="group flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
           {{ request()->routeIs('supervisor.inventory.stock-history') 
              ? 'bg-white/10 text-white border-l-2 border-caramel' 
              : 'text-white/70 hover:bg-white/5 hover:text-white border-l-2 border-transparent' }}">
            <i class="fas fa-history w-6 text-center text-sm {{ request()->routeIs('supervisor.inventory.stock-history') ? 'text-caramel' : 'text-white/50 group-hover:text-white transition-colors' }}"></i>
            <span class="ml-2">Stock History</span>
        </a>

        {{-- Adjustments --}}
        <a href="{{ Route::has('supervisor.inventory.adjustments') ? route('supervisor.inventory.adjustments') : '#' }}"
           id="menu-supervisor-inventory-adjustments"
           class="group flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
           {{ request()->routeIs('supervisor.inventory.adjustments') 
              ? 'bg-white/10 text-white border-l-2 border-caramel' 
              : 'text-white/70 hover:bg-white/5 hover:text-white border-l-2 border-transparent' }}">
            <i class="fas fa-file-invoice-dollar w-6 text-center text-sm {{ request()->routeIs('supervisor.inventory.adjustments') ? 'text-caramel' : 'text-white/50 group-hover:text-white transition-colors' }}"></i>
            <span class="ml-2">Adjustments</span>
        </a>

        {{-- SECTION: REPORTS & SETTINGS --}}
        <div class="px-3 pt-6 pb-2">
            <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest font-display">Reports & Settings</p>
        </div>

        {{-- Expiry Report --}}
        <a href="{{ Route::has('supervisor.reports.expiry') ? route('supervisor.reports.expiry') : '#' }}"
           id="menu-supervisor-reports-expiry"
           class="group flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
           {{ request()->routeIs('supervisor.reports.expiry') 
              ? 'bg-white/10 text-white border-l-2 border-caramel' 
              : 'text-white/70 hover:bg-white/5 hover:text-white border-l-2 border-transparent' }}">
            <i class="fas fa-hourglass-end w-6 text-center text-sm {{ request()->routeIs('supervisor.reports.expiry') ? 'text-caramel' : 'text-white/50 group-hover:text-white transition-colors' }}"></i>
            <span class="ml-2">Expiry Report</span>
        </a>

        {{-- Min Stock Settings --}}
        <a href="{{ Route::has('supervisor.settings.stock-levels') ? route('supervisor.settings.stock-levels') : '#' }}"
           id="menu-supervisor-settings-stock-levels"
           class="group flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
           {{ request()->routeIs('supervisor.settings.stock-levels') 
              ? 'bg-white/10 text-white border-l-2 border-caramel' 
              : 'text-white/70 hover:bg-white/5 hover:text-white border-l-2 border-transparent' }}">
            <i class="fas fa-sliders-h w-6 text-center text-sm {{ request()->routeIs('supervisor.settings.stock-levels') ? 'text-caramel' : 'text-white/50 group-hover:text-white transition-colors' }}"></i>
            <span class="ml-2">Min. Stock Levels</span>
        </a>

        {{-- SECTION: SYSTEM --}}
        <div class="px-3 pt-6 pb-2">
            <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest font-display">System</p>
        </div>

        {{-- Notifications --}}
        <a href="{{ Route::has('supervisor.notifications') ? route('supervisor.notifications') : '#' }}"
           id="menu-supervisor-notifications"
           class="group flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
           {{ request()->routeIs('supervisor.notifications') 
              ? 'bg-white/10 text-white border-l-2 border-caramel' 
              : 'text-white/70 hover:bg-white/5 hover:text-white border-l-2 border-transparent' }}">
            <div class="flex items-center">
                <i class="fas fa-bell w-6 text-center text-sm {{ request()->routeIs('supervisor.notifications') ? 'text-caramel' : 'text-white/50 group-hover:text-white transition-colors' }}"></i>
                <span class="ml-2">Notifications</span>
            </div>
            @if(isset($badgeCounts['unread_notifications']) && $badgeCounts['unread_notifications'] > 0)
            <span class="bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded min-w-[1.25rem] text-center shadow-sm">
                {{ $badgeCounts['unread_notifications'] }}
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