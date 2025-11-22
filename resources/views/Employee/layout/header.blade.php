<!-- HEADER COMPONENT START -->
<header class="bg-white shadow-sm border-b-2 border-border-soft component-body sticky top-0 z-30">
    
    <div class="flex items-center justify-between px-4 sm:px-6 py-3">
        <!-- Left Section -->
        <div class="flex items-center space-x-3">
            <!-- Toggle Sidebar Button -->
            <button onclick="toggleSidebar()" class="p-2 text-text-muted hover:text-text-dark hover:bg-cream-bg transition-colors square-button rounded-lg">
                <i class="fas fa-bars text-base"></i>
            </button>
            
            <!-- Breadcrumb / Logo -->
            <div class="flex items-center space-x-2 text-sm">
                <i class="fas fa-birthday-cake text-caramel text-xl"></i>
                <span class="font-display font-bold text-text-dark text-lg hidden sm:block">WellKenz</span>
                <span class="text-border-soft mx-2 hidden sm:block">/</span>
                <span class="text-text-muted text-xs uppercase tracking-wider hidden md:block">@yield('breadcrumb', 'Employee Portal')</span>
            </div>
        </div>

        <!-- Right Section -->
        <div class="flex items-center space-x-3">
            <!-- Search -->
            <div class="hidden md:block relative">
                <div class="relative">
                    <input type="text" placeholder="Search..." 
                        class="pl-10 pr-4 py-2 bg-white border border-border-soft rounded-lg
                               placeholder-text-muted text-text-dark text-sm
                               focus:outline-none focus:border-caramel focus:ring-1 focus:ring-caramel transition-all w-40 lg:w-64">
                    <i class="fas fa-search absolute left-3 top-2.5 text-text-muted text-xs"></i>
                </div>
            </div>

            <!-- Notifications -->
            <div class="relative" role="menu">
                <button id="notificationsBtn" onclick="toggleNotifications()" 
                        class="p-2 text-text-muted hover:text-text-dark hover:bg-cream-bg transition-colors relative focus:outline-none rounded-lg">
                    <i class="fas fa-bell text-lg"></i>
                    <!-- Dynamic Count -->
                    <span id="notificationCount" class="hidden absolute top-1 right-1 bg-red-500 text-white text-[10px] h-4 w-4 rounded-full flex items-center justify-center font-bold border-2 border-white">
                        0
                    </span>
                </button>
                
                <!-- Notifications Dropdown -->
                <div id="notificationsDropdown" class="hidden absolute right-0 mt-3 w-80 sm:w-96 bg-white shadow-xl border border-gray-100 z-50 rounded-xl overflow-hidden origin-top-right transition-all duration-200 transform" role="menu">
                    <!-- Header -->
                    <div class="px-4 py-3 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                        <h3 class="font-bold text-gray-800 text-sm" id="notifHeader">
                            Notifications
                        </h3>
                        <button onclick="markAllAsRead()" class="text-xs text-chocolate hover:text-chocolate-dark font-semibold hover:underline decoration-chocolate/30 underline-offset-2">
                            Mark all read
                        </button>
                    </div>
                    
                    <!-- Loading State -->
                    <div id="notificationsLoading" class="p-8 text-center">
                        <div class="animate-spin inline-block w-6 h-6 border-[3px] border-gray-200 border-t-chocolate rounded-full"></div>
                        <p class="text-xs text-gray-400 mt-2 font-medium">Loading updates...</p>
                    </div>
                    
                    <!-- Error State -->
                    <div id="notificationsError" class="hidden p-6 text-center">
                        <div class="w-10 h-10 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-2">
                            <i class="fas fa-exclamation text-red-500"></i>
                        </div>
                        <p class="text-xs text-red-500">Failed to load</p>
                        <button onclick="loadHeaderNotifications()" class="text-xs text-gray-500 hover:text-gray-800 font-medium mt-1 underline">
                            Try again
                        </button>
                    </div>
                    
                    <!-- Empty State -->
                    <div id="notificationsEmpty" class="hidden p-8 text-center">
                        <div class="w-12 h-12 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-bell-slash text-gray-300 text-xl"></i>
                        </div>
                        <p class="text-sm font-medium text-gray-900">No new notifications</p>
                        <p class="text-xs text-gray-500 mt-1">You're all caught up!</p>
                    </div>
                    
                    <!-- Notifications List -->
                    <div class="max-h-[24rem] overflow-y-auto scrollbar-thin scrollbar-thumb-gray-200 scrollbar-track-transparent" id="notificationsList">
                        <!-- Notifications will be loaded here dynamically -->
                    </div>
                    
                    <!-- View All Link -->
                    <a href="{{ route('employee.notifications') }}" class="block p-3 text-center text-xs font-bold text-gray-500 hover:text-chocolate hover:bg-gray-50 transition border-t border-gray-100">
                        VIEW ALL ACTIVITY
                    </a>
                </div>
            </div>

            <!-- Profile -->
            <div class="relative" role="menu">
                <button id="profileBtn" onclick="toggleProfile()" 
                        class="flex items-center space-x-2 p-1 hover:bg-cream-bg transition-colors focus:outline-none rounded-full border border-transparent hover:border-border-soft">
                    <div class="w-8 h-8 bg-gradient-to-br from-caramel to-chocolate flex items-center justify-center rounded-full flex-shrink-0 shadow-sm">
                        <span class="text-white text-xs font-bold">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                    </div>
                    <div class="hidden lg:block text-left pr-1">
                        <p class="text-sm font-bold text-text-dark leading-none">{{ auth()->user()->name }}</p>
                        <p class="text-[10px] text-text-muted leading-none mt-1 font-medium uppercase">{{ ucfirst(auth()->user()->role) }}</p>
                    </div>
                    <i class="fas fa-chevron-down text-text-muted text-[10px] hidden lg:block ml-1"></i>
                </button>

                <!-- Profile Dropdown -->
                <div id="profileDropdown" class="hidden absolute right-0 mt-3 w-60 bg-white shadow-xl border border-gray-100 z-50 rounded-xl overflow-hidden origin-top-right" role="menu">
                    <div class="p-5 border-b border-gray-100 bg-gradient-to-b from-gray-50 to-white">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 bg-gradient-to-br from-caramel to-chocolate flex items-center justify-center rounded-full flex-shrink-0 shadow-md ring-4 ring-white">
                                <span class="text-white text-lg font-bold">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                            </div>
                        </div>
                        <div class="mt-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide {{ auth()->user()->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-current mr-1.5"></span>
                                {{ auth()->user()->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                    <div class="p-2">
                        <a href="#" class="flex items-center space-x-3 px-3 py-2.5 text-sm text-gray-700 hover:bg-gray-50 hover:text-chocolate transition rounded-lg group">
                            <div class="w-8 h-8 rounded-lg bg-gray-50 text-gray-400 group-hover:bg-chocolate/10 group-hover:text-chocolate flex items-center justify-center transition-colors">
                                <i class="fas fa-user"></i>
                            </div>
                            <span class="font-medium">My Profile</span>
                        </a>
                    </div>
                    <div class="p-2 border-t border-gray-100">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex items-center space-x-2 w-full px-3 py-2.5 text-sm font-medium text-red-600 hover:bg-red-50 transition rounded-lg">
                                <i class="fas fa-sign-out-alt w-8 text-center"></i>
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

            const response = await fetch('{{ route("employee.notifications.header") }}', {
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                const notifications = data.notifications;
                const unreadCount = data.unread_count;

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
                    listEl.innerHTML = notifications.map(notification => {
                        const iconParts = notification.icon_class.split(' ');
                        const bgColor = iconParts[1] || 'bg-gray-100';
                        const textColor = iconParts[2] || 'text-gray-600';
                        const icon = iconParts[0] || 'fas fa-bell';
                        
                        // Determine read status based on available properties
                        const isRead = notification.read_at !== null;
                        const containerClass = isRead ? 'bg-white hover:bg-gray-50' : 'bg-blue-50/40 hover:bg-blue-50/70';
                        const titleClass = isRead ? 'text-gray-700 font-semibold' : 'text-gray-900 font-bold';
                        const unreadDot = !isRead ? `<div class="w-2 h-2 bg-chocolate rounded-full flex-shrink-0 mt-1.5" title="Unread"></div>` : '';

                        return `
                            <div class="p-3 border-b border-gray-50 cursor-pointer transition-colors group ${containerClass}"
                                 data-notification-id="${notification.id}"
                                 onclick="handleNotificationClick(${notification.id}, '${notification.action_url}')">
                                <div class="flex items-start gap-3">
                                    <div class="flex-shrink-0 mt-0.5">
                                        <div class="w-9 h-9 ${bgColor} rounded-full flex items-center justify-center ring-1 ring-black/5">
                                            <i class="${icon} ${textColor} text-xs"></i>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex justify-between items-start">
                                            <p class="text-sm ${titleClass} truncate pr-2">${notification.title}</p>
                                            <span class="text-[10px] text-gray-400 whitespace-nowrap pt-0.5">${notification.time_ago}</span>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-0.5 line-clamp-2 leading-relaxed">${notification.message}</p>
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
                item.classList.remove('bg-blue-50/40', 'hover:bg-blue-50/70');
                item.classList.add('bg-white', 'hover:bg-gray-50');
                const dot = item.querySelector('.w-2.h-2.bg-chocolate');
                if(dot) dot.remove();
                const title = item.querySelector('p.text-sm');
                if(title) {
                    title.classList.remove('text-gray-900', 'font-bold');
                    title.classList.add('text-gray-700', 'font-semibold');
                }
            }

            // Update count locally
            const countEl = getEl('notificationCount');
            let currentCount = parseInt(countEl.textContent) || 0;
            if (currentCount > 0) {
                currentCount--;
                countEl.textContent = currentCount > 99 ? '99+' : currentCount;
                if (currentCount === 0) countEl.classList.add('hidden');
            }

            // Send request to backend
            await fetch(`{{ route('employee.notifications.mark-read', ['notification' => '__ID__']) }}`.replace('__ID__', notificationId), {
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
            const response = await fetch('{{ route("employee.notifications.mark-all-read") }}', {
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
        if (sidebar) {
            sidebar.classList.toggle('collapsed');
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('sidebarCollapsed', isCollapsed);
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
<!-- HEADER COMPONENT END -->