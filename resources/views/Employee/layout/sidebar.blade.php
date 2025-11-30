<aside id="sidebar" 
    class="sidebar w-64 flex flex-col h-screen sticky top-0 bg-chocolate text-white border-r border-white/5 transition-all duration-300 z-40 font-sans shadow-2xl">
    
    {{-- 1. BRANDING SECTION --}}
    <div class="relative z-10 p-6 pb-8 border-b border-white/10">
        {{-- Decorative Glow --}}
        <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-b from-white/5 to-transparent pointer-events-none"></div>
        
        <div class="relative flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-caramel to-chocolate-dark border border-white/10 flex items-center justify-center shadow-lg">
                <i class="fas fa-user-tag text-white text-lg"></i>
            </div>
            <div>
                <h1 class="font-display text-2xl font-bold tracking-wide text-white leading-none">WellKenz</h1>
                <p class="text-[10px] text-caramel font-bold uppercase tracking-[0.2em] mt-1">Employee Portal</p>
            </div>
        </div>
    </div>

    {{-- 2. NAVIGATION --}}
    <nav class="flex-1 overflow-y-auto custom-scrollbar px-4 py-6 space-y-1">

        {{-- DASHBOARD --}}
        <a href="{{ Route::has('employee.dashboard') ? route('employee.dashboard') : '#' }}"
           id="menu-employee-dashboard"
           class="group flex items-center px-3 py-3 rounded-lg text-sm font-medium transition-all duration-200 mb-6
           {{ request()->routeIs('employee.dashboard') 
              ? 'bg-white/10 text-white border-l-2 border-caramel' 
              : 'text-white/70 hover:bg-white/5 hover:text-white border-l-2 border-transparent' }}">
            <i class="fas fa-home w-6 text-center text-sm {{ request()->routeIs('employee.dashboard') ? 'text-caramel' : 'text-white/50 group-hover:text-white transition-colors' }}"></i>
            <span class="ml-2">My Hub</span>
        </a>

        {{-- SECTION: REQUISITIONS --}}
        <div class="px-3 pt-2 pb-2">
            <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest font-display">Requisitions</p>
        </div>

        {{-- New Request --}}
        <a href="{{ Route::has('employee.requisitions.create') ? route('employee.requisitions.create') : '#' }}"
           id="menu-employee-requisitions-create"
           class="group flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
           {{ request()->routeIs('employee.requisitions.create') || request()->routeIs('employee.requisitions.store')
              ? 'bg-white/10 text-white border-l-2 border-caramel' 
              : 'text-white/70 hover:bg-white/5 hover:text-white border-l-2 border-transparent' }}">
            <i class="fas fa-shopping-basket w-6 text-center text-sm {{ request()->routeIs('employee.requisitions.create') || request()->routeIs('employee.requisitions.store') ? 'text-caramel' : 'text-white/50 group-hover:text-white transition-colors' }}"></i>
            <span class="ml-2">New Request</span>
        </a>

        {{-- My History --}}
        <a href="{{ Route::has('employee.requisitions.history') ? route('employee.requisitions.history') : '#' }}"
           id="menu-employee-requisitions-history"
           class="group flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
           {{ request()->routeIs('employee.requisitions.history') || request()->routeIs('employee.requisitions.details')
              ? 'bg-white/10 text-white border-l-2 border-caramel' 
              : 'text-white/70 hover:bg-white/5 hover:text-white border-l-2 border-transparent' }}">
            <i class="fas fa-history w-6 text-center text-sm {{ request()->routeIs('employee.requisitions.history') || request()->routeIs('employee.requisitions.details') ? 'text-caramel' : 'text-white/50 group-hover:text-white transition-colors' }}"></i>
            <span class="ml-2">My History</span>
        </a>

        {{-- SECTION: PRODUCTION --}}
        <div class="px-3 pt-6 pb-2">
            <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest font-display">Production</p>
        </div>

        {{-- View Recipes --}}
        <a href="{{ Route::has('employee.recipes.index') ? route('employee.recipes.index') : '#' }}"
           id="menu-employee-recipes"
           class="group flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
           {{ request()->routeIs('employee.recipes.index') 
              ? 'bg-white/10 text-white border-l-2 border-caramel' 
              : 'text-white/70 hover:bg-white/5 hover:text-white border-l-2 border-transparent' }}">
            <i class="fas fa-book-open w-6 text-center text-sm {{ request()->routeIs('employee.recipes.index') ? 'text-caramel' : 'text-white/50 group-hover:text-white transition-colors' }}"></i>
            <span class="ml-2">View Recipes</span>
        </a>

        {{-- SECTION: SYSTEM --}}
        <div class="px-3 pt-6 pb-2">
            <p class="text-[10px] font-bold text-white/40 uppercase tracking-widest font-display">System</p>
        </div>

        {{-- Notifications --}}
        <a href="{{ Route::has('employee.notifications') ? route('employee.notifications') : '#' }}"
           id="menu-employee-notifications"
           class="group flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200
           {{ request()->routeIs('employee.notifications*') 
              ? 'bg-white/10 text-white border-l-2 border-caramel' 
              : 'text-white/70 hover:bg-white/5 hover:text-white border-l-2 border-transparent' }}">
            <div class="flex items-center">
                <i class="fas fa-bell w-6 text-center text-sm {{ request()->routeIs('employee.notifications*') ? 'text-caramel' : 'text-white/50 group-hover:text-white transition-colors' }}"></i>
                <span class="ml-2">Notifications</span>
            </div>
            {{-- Dynamic Badge --}}
            <span id="notification-badge" class="bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded min-w-[1.25rem] text-center shadow-sm" style="display: none;">
                <span id="notification-count">0</span>
            </span>
        </a>

    </nav>

    {{-- DECORATIVE FOOTER ELEMENT --}}
    <div class="p-4 relative overflow-hidden mt-auto">
        <div class="absolute -bottom-10 -right-10 w-32 h-32 bg-caramel/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative z-10 flex items-center gap-2 text-[10px] text-white/30 uppercase tracking-widest">
            <div class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></div> Portal Active
        </div>
    </div>
</aside>

{{-- Notification Badge Script --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const badge = document.getElementById('notification-badge');
    const countElement = document.getElementById('notification-count');
    
    // Function to update badge count
    function updateNotificationBadge() {
        fetch('{{ route("employee.notifications.unread-count") }}', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && typeof data.unread_count === 'number') {
                const count = data.unread_count;
                
                if (count > 0) {
                    countElement.textContent = count > 99 ? '99+' : count;
                    badge.style.display = 'inline-block';
                } else {
                    badge.style.display = 'none';
                }
            }
        })
        .catch(error => {
            console.error('Error fetching notification count:', error);
            badge.style.display = 'none';
        });
    }
    
    // Update badge on page load
    updateNotificationBadge();
    
    // Refresh badge every 30 seconds for real-time updates
    setInterval(updateNotificationBadge, 30000);
    
    // Expose function globally for other scripts to call
    window.refreshNotificationBadge = updateNotificationBadge;
});
</script>

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