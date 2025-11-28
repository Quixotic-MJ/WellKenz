
<header class="bg-white shadow-sm border-b border-border-soft component-body sticky top-0 z-30 font-sans">
    
    <div class="flex items-center justify-between px-6 py-4">
        <div class="flex items-center space-x-4">
            <button onclick="toggleSidebar()" class="header-sidebar-toggle p-2 text-chocolate hover:bg-cream-bg hover:text-caramel transition-all rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel/20">
                <i class="fas fa-bars text-xl"></i>
            </button>
            
            <div class="flex items-center gap-3 text-sm">
                <div class="flex items-center gap-2">
                    <i class="fas fa-birthday-cake text-caramel text-2xl"></i>
                    <span class="font-display font-bold text-chocolate text-xl hidden sm:block tracking-tight">WellKenz</span>
                </div>
                <span class="text-border-soft text-xl font-light hidden sm:block">|</span>
                <span class="text-gray-500 text-xs font-bold uppercase tracking-widest hidden md:block">
                    @yield('breadcrumb', 'Dashboard')
                </span>
            </div>
        </div>

        <div class="flex items-center space-x-5">
            
            <div class="hidden md:block relative group">
                <div class="relative">
                    <input type="text" placeholder="Type to search..." 
                        class="pl-10 pr-4 py-2.5 bg-cream-bg border border-transparent hover:border-border-soft rounded-xl
                               placeholder-gray-400 text-chocolate text-sm w-48 lg:w-72
                               focus:bg-white focus:outline-none focus:border-caramel focus:ring-2 focus:ring-caramel/20 transition-all duration-300">
                    <i class="fas fa-search absolute left-3.5 top-3 text-chocolate/50 group-hover:text-caramel transition-colors text-sm"></i>
                </div>
            </div>

            <div class="relative" role="menu">
                <button id="notificationsBtn" onclick="toggleNotifications()" 
                        class="relative p-2.5 text-chocolate hover:bg-cream-bg hover:text-caramel transition-all rounded-xl focus:outline-none group">
                    <i class="fas fa-bell text-xl transition-transform group-hover:scale-110"></i>
                    
                    <span id="notificationCount" class="hidden absolute top-1.5 right-2 bg-caramel text-white text-[10px] h-4 w-4 rounded-full flex items-center justify-center font-bold border-2 border-white shadow-sm">
                        0
                    </span>
                </button>
                
                <div id="notificationsDropdown" class="hidden absolute right-0 mt-4 w-80 sm:w-96 bg-white shadow-xl border border-border-soft z-50 rounded-xl overflow-hidden origin-top-right transition-all duration-200 transform">
                    <div class="px-5 py-4 border-b border-border-soft bg-cream-bg flex justify-between items-center">
                        <h3 class="font-display font-bold text-chocolate text-lg" id="notifHeader">
                            Notifications
                        </h3>
                        <button onclick="markAllAsRead()" class="text-xs text-caramel hover:text-chocolate font-bold uppercase tracking-wider transition-colors">
                            Mark all read
                        </button>
                    </div>
                    
                    <div id="notificationsLoading" class="p-10 text-center">
                        <div class="animate-spin inline-block w-6 h-6 border-[3px] border-border-soft border-t-caramel rounded-full"></div>
                        <p class="text-xs text-gray-400 mt-3 font-medium uppercase tracking-wide">Loading updates...</p>
                    </div>
                    
                    <div id="notificationsError" class="hidden p-8 text-center">
                        <div class="w-12 h-12 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-3 border border-red-100">
                            <i class="fas fa-exclamation text-red-500"></i>
                        </div>
                        <p class="text-sm text-red-500 font-medium">Failed to load</p>
                        <button onclick="loadHeaderNotifications()" class="text-xs text-gray-500 hover:text-chocolate font-bold mt-2 underline decoration-gray-300 underline-offset-2">
                            Try again
                        </button>
                    </div>
                    
                    <div id="notificationsEmpty" class="hidden p-10 text-center">
                        <div class="w-14 h-14 bg-cream-bg rounded-full flex items-center justify-center mx-auto mb-4 border border-border-soft">
                            <i class="fas fa-bell-slash text-chocolate/30 text-xl"></i>
                        </div>
                        <p class="font-display text-lg font-bold text-chocolate">All caught up</p>
                        <p class="text-xs text-gray-500 mt-1">No new notifications at this time.</p>
                    </div>
                    
                    <div class="max-h-[26rem] overflow-y-auto scrollbar-thin scrollbar-thumb-border-soft scrollbar-track-transparent" id="notificationsList">
                        </div>
                    
                    <a href="{{ route('admin.notifications.index') }}" class="block p-4 text-center text-xs font-bold text-chocolate hover:bg-chocolate hover:text-white transition-all border-t border-border-soft uppercase tracking-widest">
                        View All Activity
                    </a>
                </div>
            </div>

            <div class="relative" role="menu">
                <button id="profileBtn" onclick="toggleProfile()" 
                        class="flex items-center space-x-3 p-1.5 hover:bg-cream-bg transition-all rounded-full border border-transparent hover:border-border-soft focus:outline-none">
                    <div class="w-9 h-9 bg-gradient-to-br from-caramel to-chocolate flex items-center justify-center rounded-full flex-shrink-0 shadow-md ring-2 ring-white">
                        <span class="text-white text-xs font-bold">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                    </div>
                    <div class="hidden lg:block text-left pr-2">
                        <p class="text-sm font-bold text-chocolate leading-none">{{ auth()->user()->name }}</p>
                        <p class="text-[10px] text-gray-500 leading-none mt-1.5 font-medium uppercase tracking-wide">{{ ucfirst(auth()->user()->role) }}</p>
                    </div>
                    <i class="fas fa-chevron-down text-chocolate/40 text-[10px] hidden lg:block mr-1"></i>
                </button>

                <div id="profileDropdown" class="hidden absolute right-0 mt-4 w-64 bg-white shadow-xl border border-border-soft z-50 rounded-xl overflow-hidden origin-top-right transform transition-all">
                    <div class="p-5 border-b border-border-soft bg-cream-bg">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-caramel to-chocolate flex items-center justify-center rounded-full flex-shrink-0 shadow-md ring-4 ring-white">
                                <span class="text-white text-lg font-bold">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                            </div>
                            <div class="overflow-hidden">
                                <p class="text-sm font-bold text-chocolate truncate">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border {{ auth()->user()->is_active ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200' }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-current mr-2"></span>
                                {{ auth()->user()->is_active ? 'Active Status' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                    <div class="p-2 space-y-1">
                        <a href="#" class="flex items-center space-x-3 px-3 py-3 text-sm text-gray-600 hover:bg-cream-bg hover:text-chocolate transition rounded-lg group font-medium">
                            <div class="w-8 h-8 rounded-lg bg-white border border-border-soft text-gray-400 group-hover:border-caramel group-hover:text-caramel flex items-center justify-center transition-colors shadow-sm">
                                <i class="fas fa-user text-xs"></i>
                            </div>
                            <span>My Profile</span>
                        </a>
                    </div>
                    <div class="p-2 border-t border-border-soft">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex items-center justify-center space-x-2 w-full px-3 py-3 text-sm font-bold text-white bg-chocolate hover:bg-chocolate-dark transition rounded-lg shadow-sm">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Sign Out</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
    // Utility function to safely access elements
    const getEl = (id) => document.getElementById(id);

    // Load header notifications from API
    async function loadHeaderNotifications() {
        const loadingEl = getEl('notificationsLoading');
        const errorEl = getEl('notificationsError');
        const emptyEl = getEl('notificationsEmpty');
        const listEl = getEl('notificationsList');
        const countEl = getEl('notificationCount');
        const headerEl = getEl('notifHeader');

        try {
            // Show loading state
            loadingEl.classList.remove('hidden');
            errorEl.classList.add('hidden');
            emptyEl.classList.add('hidden');
            listEl.innerHTML = '';

            const response = await fetch('{{ route("admin.notifications.header") }}', {
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                const notifications = data.notifications || [];
                const unreadCount = data.unread_count || 0;

                // Hide loading
                loadingEl.classList.add('hidden');

                // Update count badge
                if (unreadCount > 0) {
                    countEl.textContent = unreadCount > 99 ? '99+' : unreadCount;
                    countEl.classList.remove('hidden');
                } else {
                    countEl.classList.add('hidden');
                }

                // Update header
                headerEl.textContent = `Notifications (${unreadCount})`;

                // Show appropriate state
                if (notifications.length === 0) {
                    emptyEl.classList.remove('hidden');
                } else {
                    // Render notifications
                    // REDESIGNED TEMPLATE LITERALS TO MATCH THEME
                    listEl.innerHTML = notifications.map(notification => {
                        const iconParts = notification.icon_class.split(' ');
                        const bgColor = iconParts[1] || 'bg-cream-bg';
                        // Force icon text to be chocolate or generic gray if not specified
                        const textColor = iconParts[2] || 'text-chocolate'; 
                        const icon = iconParts[0] || 'fas fa-bell';
                        
                        // Determine read status styles for WellKenz Theme
                        const isRead = notification.read_at !== null;
                        
                        // Unread: Caramel tint bg. Read: White bg.
                        const containerClass = isRead 
                            ? 'bg-white hover:bg-cream-bg' 
                            : 'bg-orange-50/50 hover:bg-orange-50';
                            
                        const titleClass = isRead 
                            ? 'text-gray-600 font-semibold' 
                            : 'text-chocolate font-bold';
                            
                        const unreadDot = !isRead 
                            ? `<div class="w-2 h-2 bg-caramel rounded-full flex-shrink-0 mt-2 shadow-sm" title="Unread"></div>` 
                            : '';

                        return `
                            <div class="p-4 border-b border-border-soft last:border-0 cursor-pointer transition-colors group ${containerClass}"
                                 data-notification-id="${notification.id}"
                                 onclick="handleNotificationClick(${notification.id}, '${notification.action_url}')">
                                <div class="flex items-start gap-4">
                                    <div class="flex-shrink-0 mt-0.5">
                                        <div class="w-10 h-10 ${bgColor} rounded-xl flex items-center justify-center border border-border-soft">
                                            <i class="${icon} ${textColor} text-sm"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex justify-between items-start">
                                            <p class="text-sm ${titleClass} truncate pr-2 font-sans">${notification.title}</p>
                                            <span class="text-[10px] text-gray-400 whitespace-nowrap pt-1 uppercase tracking-wide font-medium">${notification.time_ago}</span>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1 line-clamp-2 leading-relaxed font-sans">${notification.message}</p>
                                    </div>
                                    ${unreadDot}
                                </div>
                            </div>
                        `;
                    }).join('');
                }

            } else {
                throw new Error(data.message || 'Failed to load notifications');
            }

        } catch (error) {
            console.error('Error loading notifications:', error);
            loadingEl.classList.add('hidden');
            errorEl.classList.remove('hidden');
        }
    }

    // Handle notification click
    async function handleNotificationClick(notificationId, actionUrl) {
        try {
            // Optimistic UI update: Remove unread highlighting immediately
            const item = document.querySelector(`div[data-notification-id="${notificationId}"]`);
            if(item) {
                // Remove Unread styles
                item.classList.remove('bg-orange-50/50', 'hover:bg-orange-50');
                // Add Read styles
                item.classList.add('bg-white', 'hover:bg-cream-bg');
                
                const dot = item.querySelector('.bg-caramel'); // Select caramel dot
                if(dot) dot.remove();
                
                const title = item.querySelector('p.text-sm');
                if(title) {
                    title.classList.remove('text-chocolate', 'font-bold');
                    title.classList.add('text-gray-600', 'font-semibold');
                }
            }

            // Update count locally
            const countEl = getEl('notificationCount');
            let currentCount = parseInt(countEl.textContent) || 0;
            if (currentCount > 0) {
                currentCount--;
                countEl.textContent = currentCount > 99 ? '99+' : currentCount;
                if (currentCount === 0) countEl.classList.add('hidden');
                
                // Update header text count
                const headerEl = getEl('notifHeader');
                if(headerEl) headerEl.textContent = `Notifications (${currentCount})`;
            }

            // Send request to backend
            await fetch(`{{ route('admin.notifications.mark_read', ['notification' => '__ID__']) }}`.replace('__ID__', notificationId), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            });

            // Navigate
            if (actionUrl && actionUrl !== 'null' && actionUrl !== '') {
                window.location.href = actionUrl;
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    // Mark all notifications as read
    async function markAllAsRead() {
        try {
            const response = await fetch('{{ route("admin.notifications.mark_all_read") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();
            if (data.success) {
                loadHeaderNotifications();
            }
        } catch (error) {
            console.error('Error marking all as read:', error);
        }
    }

    // Toggles the Notifications dropdown visibility
    function toggleNotifications() {
        const notificationsDropdown = getEl('notificationsDropdown');
        const profileDropdown = getEl('profileDropdown');
        
        // Close other dropdown
        if (profileDropdown) profileDropdown.classList.add('hidden');
        
        // Toggle self
        if (notificationsDropdown) {
            notificationsDropdown.classList.toggle('hidden');
            
            // Load notifications when opening
            if (!notificationsDropdown.classList.contains('hidden')) {
                loadHeaderNotifications();
            }
        }
    }

    // Toggles the Profile dropdown visibility
    function toggleProfile() {
        const profileDropdown = getEl('profileDropdown');
        const notificationsDropdown = getEl('notificationsDropdown');
        
        // Close other dropdown
        if (notificationsDropdown) notificationsDropdown.classList.add('hidden');
        
        // Toggle self
        if (profileDropdown) profileDropdown.classList.toggle('hidden');
    }

    // Toggle sidebar function
    function toggleSidebar() {
        const sidebar = getEl('sidebar');
        
        console.log('Toggle sidebar called, sidebar element:', sidebar);
        if (sidebar) {
            console.log('Before toggle - classes:', sidebar.className);
            sidebar.classList.toggle('collapsed');
            const isCollapsed = sidebar.classList.contains('collapsed');
            
            console.log('After toggle - collapsed:', isCollapsed, 'classes:', sidebar.className);
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        } else {
            console.error('Sidebar element not found!');
        }
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        const notificationsBtn = getEl('notificationsBtn');
        const profileBtn = getEl('profileBtn');
        const notificationsDropdown = getEl('notificationsDropdown');
        const profileDropdown = getEl('profileDropdown');
        
        if (notificationsDropdown && notificationsBtn && !notificationsBtn.contains(event.target) && !notificationsDropdown.contains(event.target)) {
            notificationsDropdown.classList.add('hidden');
        }
        
        if (profileDropdown && profileBtn && !profileBtn.contains(event.target) && !profileDropdown.contains(event.target)) {
            profileDropdown.classList.add('hidden');
        }
    });

    // Restore sidebar state from localStorage
    document.addEventListener('DOMContentLoaded', function() {
        const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        const sidebar = getEl('sidebar');
        
        if (sidebar && sidebarCollapsed) {
            sidebar.classList.add('collapsed');
        }

        // Load initial notification count
        loadHeaderNotifications();
        
        // Refresh notifications every 60 seconds
        setInterval(() => {
            if (!document.hidden) loadHeaderNotifications();
        }, 60000);
    });
</script>