<aside id="sidebar" 
    class="sidebar w-64 flex flex-col h-screen sticky top-0 bg-chocolate text-white border-r border-white/5 transition-all duration-300 z-40 font-sans shadow-xl">
    
    {{-- 1. BRANDING SECTION --}}
    {{-- Design: Glassmorphism effect over Chocolate background --}}
    <div class="relative z-10 p-6 pb-8">
        {{-- Decorative Glow --}}
        <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
        
        <div class="relative flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-caramel to-chocolate-dark border border-white/10 flex items-center justify-center shadow-lg">
                <i class="fas fa-birthday-cake text-white text-lg"></i>
            </div>
            <div>
                <h1 class="font-display text-2xl font-bold tracking-wide text-white leading-none">WellKenz</h1>
                <p class="text-[10px] text-caramel font-bold uppercase tracking-[0.2em] mt-1">Admin Panel</p>
            </div>
        </div>
    </div>

    {{-- 2. NAVIGATION --}}
    <nav class="flex-1 overflow-y-auto no-scrollbar px-4 pb-6 space-y-1">

        {{-- DASHBOARD --}}
        <a href="{{ Route::has('admin.dashboard') ? route('admin.dashboard') : '#' }}"
           class="group flex items-center px-3 py-3 rounded-lg text-sm font-medium transition-all duration-200 mb-6
           {{ request()->routeIs('admin.dashboard') 
              ? 'bg-white/10 text-white border-l-2 border-caramel' 
              : 'text-white/70 hover:bg-white/5 hover:text-white border-l-2 border-transparent' }}">
            <i class="fas fa-tachometer-alt w-6 text-center text-sm {{ request()->routeIs('admin.dashboard') ? 'text-caramel' : 'text-white/50 group-hover:text-white transition-colors' }}"></i>
            <span class="ml-2">System Overview</span>
        </a>

        {{-- SECTION: USER MANAGEMENT --}}
        <div class="px-3 pt-2 pb-2">
            <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest font-display">User Management</p>
        </div>

        <a href="{{ Route::has('admin.users.index') ? route('admin.users.index') : '#' }}"
           class="group flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
           {{ request()->routeIs('admin.users*') 
              ? 'bg-white/10 text-white border-l-2 border-caramel' 
              : 'text-white/70 hover:bg-white/5 hover:text-white border-l-2 border-transparent' }}">
            <i class="fas fa-users w-6 text-center text-sm {{ request()->routeIs('admin.users*') ? 'text-caramel' : 'text-white/50 group-hover:text-white transition-colors' }}"></i>
            <span class="ml-2">Users</span>
        </a>

        {{-- SECTION: MASTER FILES --}}
        <div class="px-3 pt-6 pb-2">
            <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest font-display">Inventory Master</p>
        </div>

        <a href="{{ Route::has('admin.items.index') ? route('admin.items.index') : '#' }}"
           class="group flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
           {{ request()->routeIs('admin.items*') 
              ? 'bg-white/10 text-white border-l-2 border-caramel' 
              : 'text-white/70 hover:bg-white/5 hover:text-white border-l-2 border-transparent' }}">
            <i class="fas fa-database w-6 text-center text-sm {{ request()->routeIs('admin.items*') ? 'text-caramel' : 'text-white/50 group-hover:text-white transition-colors' }}"></i>
            <span class="ml-2">Item Masterlist</span>
        </a>

        <a href="{{ Route::has('admin.categories.index') ? route('admin.categories.index') : '#' }}"
           class="group flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
           {{ request()->routeIs('admin.categories*') 
              ? 'bg-white/10 text-white border-l-2 border-caramel' 
              : 'text-white/70 hover:bg-white/5 hover:text-white border-l-2 border-transparent' }}">
            <i class="fas fa-tags w-6 text-center text-sm {{ request()->routeIs('admin.categories*') ? 'text-caramel' : 'text-white/50 group-hover:text-white transition-colors' }}"></i>
            <span class="ml-2">Categories</span>
        </a>

        <a href="{{ Route::has('admin.units.index') ? route('admin.units.index') : '#' }}"
           class="group flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
           {{ request()->routeIs('admin.units*') 
              ? 'bg-white/10 text-white border-l-2 border-caramel' 
              : 'text-white/70 hover:bg-white/5 hover:text-white border-l-2 border-transparent' }}">
            <i class="fas fa-balance-scale w-6 text-center text-sm {{ request()->routeIs('admin.units*') ? 'text-caramel' : 'text-white/50 group-hover:text-white transition-colors' }}"></i>
            <span class="ml-2">Units</span>
        </a>

        {{-- SECTION: EXTERNAL --}}
        <div class="px-3 pt-6 pb-2">
            <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest font-display">External</p>
        </div>

        <a href="{{ Route::has('admin.suppliers.index') ? route('admin.suppliers.index') : '#' }}"
           class="group flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
           {{ request()->routeIs('admin.suppliers*') 
              ? 'bg-white/10 text-white border-l-2 border-caramel' 
              : 'text-white/70 hover:bg-white/5 hover:text-white border-l-2 border-transparent' }}">
            <i class="fas fa-truck w-6 text-center text-sm {{ request()->routeIs('admin.suppliers*') ? 'text-caramel' : 'text-white/50 group-hover:text-white transition-colors' }}"></i>
            <span class="ml-2">Suppliers</span>
        </a>

        {{-- SECTION: SYSTEM --}}
        <div class="px-3 pt-6 pb-2">
            <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest font-display">System</p>
        </div>

        <a href="{{ Route::has('admin.audit-logs.index') ? route('admin.audit-logs.index') : '#' }}"
           class="group flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
           {{ request()->routeIs('admin.audit-logs*') 
              ? 'bg-white/10 text-white border-l-2 border-caramel' 
              : 'text-white/70 hover:bg-white/5 hover:text-white border-l-2 border-transparent' }}">
            <i class="fas fa-file-contract w-6 text-center text-sm {{ request()->routeIs('admin.audit-logs*') ? 'text-caramel' : 'text-white/50 group-hover:text-white transition-colors' }}"></i>
            <span class="ml-2">Audit Logs</span>
        </a>

    

        <a href="{{ Route::has('admin.notifications.index') ? route('admin.notifications.index') : '#' }}"
           class="group flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
           {{ request()->routeIs('admin.notifications*') 
              ? 'bg-white/10 text-white border-l-2 border-caramel' 
              : 'text-white/70 hover:bg-white/5 hover:text-white border-l-2 border-transparent' }}">
            <div class="flex items-center">
                <i class="fas fa-bell w-6 text-center text-sm {{ request()->routeIs('admin.notifications*') ? 'text-caramel' : 'text-white/50 group-hover:text-white transition-colors' }}"></i>
                <span class="ml-2">Notifications</span>
            </div>
            @if(($unreadNotificationsCount ?? 0) > 0)
                <span class="bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded min-w-[1.25rem] text-center">{{ $unreadNotificationsCount }}</span>
            @endif
        </a>

    </nav>
    
  
</aside>