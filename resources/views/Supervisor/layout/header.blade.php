<!-- HEADER COMPONENT START -->
<header class="bg-white shadow-sm border-b-2 border-border-soft component-body">
    
    <div class="flex items-center justify-between px-4 sm:px-6 py-3">
        <!-- Left Section -->
        <div class="flex items-center space-x-3">
            <!-- Toggle Sidebar Button -->
            <button onclick="toggleSidebar()" class="p-2 text-text-muted hover:text-text-dark hover:bg-cream-bg transition-colors square-button">
                <i class="fas fa-bars text-base"></i>
            </button>
            
            <!-- Breadcrumb / Logo -->
            <div class="flex items-center space-x-2 text-sm">
                <i class="fas fa-birthday-cake text-caramel text-xl"></i>
                <span class="font-display font-bold text-text-dark text-lg hidden sm:block">WellKenz</span>
                <span class="text-border-soft mx-2 hidden sm:block">/</span>
                <span class="text-text-muted text-xs uppercase tracking-wider hidden md:block">@yield('breadcrumb', 'Dashboard')</span>
            </div>
        </div>

        <!-- Right Section -->
        <div class="flex items-center space-x-3">
            <!-- Search -->
            <div class="hidden md:block relative">
                <div class="relative">
                    <input type="text" placeholder="Search..." 
                        class="pl-10 pr-4 py-2 bg-white border border-border-soft
                               placeholder-text-muted text-text-dark text-sm
                               focus:outline-none focus:border-caramel transition-colors w-40 lg:w-64 square-button">
                    <i class="fas fa-search absolute left-3 top-2.5 text-text-muted text-xs"></i>
                </div>
            </div>

            <!-- Notifications -->
            <div class="relative" role="menu">
                <button id="notificationsBtn" onclick="toggleNotifications()" 
                        class="square-button p-2 text-text-muted hover:text-text-dark hover:bg-cream-bg transition-colors relative focus:outline-none">
                    <i class="fas fa-bell text-base"></i>
                    <!-- Dynamic Count -->
                    <span class="absolute -top-1 -right-1 bg-caramel text-white text-xs h-5 w-5 rounded-full flex items-center justify-center font-bold ring-2 ring-white hidden" id="notificationCount">
                        0
                    </span>
                </button>
                
                <!-- Notifications Dropdown -->
                <div id="notificationsDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white shadow-lg border-2 border-border-soft z-50 rounded-lg" role="menu">
                    <div class="p-3 border-b-2 border-border-soft bg-cream-bg rounded-t-lg flex justify-between items-center">
                        <h3 class="font-bold text-text-dark text-xs uppercase tracking-wider" id="notifHeader">
                            Notifications (0)
                        </h3>
                        <button onclick="markAllAsRead()" class="text-xs text-chocolate hover:text-chocolate-dark font-bold">
                            Mark all as read
                        </button>
                    </div>
                    <div class="max-h-80 overflow-y-auto" id="notificationsList">
                        <!-- Loading state -->
                        <div id="notificationsLoading" class="p-4 text-center">
                            <i class="fas fa-spinner fa-spin text-text-muted"></i>
                            <p class="text-xs text-text-muted mt-2">Loading notifications...</p>
                        </div>
                        
                        <!-- Empty state -->
                        <div id="notificationsEmpty" class="p-4 text-center hidden">
                            <i class="fas fa-bell-slash text-text-muted text-2xl"></i>
                            <p class="text-xs text-text-muted mt-2">No new notifications</p>
                        </div>
                    </div>
                    <div class="p-3 border-t-2 border-border-soft rounded-b-lg">
                        <a href="{{ route('supervisor.notifications') }}" class="block text-center text-xs font-bold text-chocolate hover:text-chocolate-dark transition uppercase tracking-wider">
                            View All Notifications
                        </a>
                    </div>
                </div>
            </div>

            <!-- Profile -->
            <div class="relative" role="menu">
                <button id="profileBtn" onclick="toggleProfile()" 
                        class="flex items-center space-x-2 p-1.5 hover:bg-cream-bg transition-colors focus:outline-none square-button">
                    <div class="w-8 h-8 bg-caramel flex items-center justify-center rounded-full flex-shrink-0">
                        <span class="text-white text-sm font-bold">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                    </div>
                    <div class="hidden lg:block text-left pr-1">
                        <p class="text-sm font-semibold text-text-dark leading-none">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-text-muted leading-none mt-0.5">{{ ucfirst(auth()->user()->role) }}</p>
                    </div>
                    <i class="fas fa-chevron-down text-text-muted text-xs hidden lg:block"></i>
                </button>

                <!-- Profile Dropdown -->
                <div id="profileDropdown" class="hidden absolute right-0 mt-2 w-56 bg-white shadow-lg border-2 border-border-soft z-50 rounded-lg" role="menu">
                    <div class="p-4 border-b-2 border-border-soft bg-cream-bg rounded-t-lg">
                        <div class="flex items-center space-x-3 mb-2">
                            <div class="w-10 h-10 bg-caramel flex items-center justify-center rounded-full flex-shrink-0">
                                <span class="text-white text-base font-bold">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-text-dark">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-text-muted">{{ ucfirst(auth()->user()->role) }}</p>
                            </div>
                        </div>
                        <p class="text-xs text-text-muted mt-2 border-t border-border-soft pt-2 truncate">
                            {{ auth()->user()->name }} • {{ ucfirst(auth()->user()->role) }}
                        </p>
                        <p class="text-xs text-text-muted truncate">
                            {{ auth()->user()->email }}
                        </p>
                        <div class="flex items-center mt-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ auth()->user()->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ auth()->user()->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                    <div class="p-2">
                        <a href="#" class="flex items-center space-x-3 px-3 py-2 text-sm text-text-dark hover:bg-cream-bg transition rounded-md">
                            <i class="fas fa-user text-text-muted w-4 text-center"></i>
                            <span>My Profile</span>
                        </a>
                        <a href="#" class="flex items-center space-x-3 px-3 py-2 text-sm text-text-dark hover:bg-cream-bg transition rounded-md">
                            <i class="fas fa-cog text-text-muted w-4 text-center"></i>
                            <span>Settings</span>
                        </a>
                    </div>
                    <div class="p-2 border-t-2 border-border-soft">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex items-center space-x-3 w-full px-3 py-2 text-sm font-bold text-white bg-chocolate hover:bg-chocolate-dark transition justify-center rounded-md">
                                <i class="fas fa-sign-out-alt w-4 text-center"></i>
                                <span>Sign Out</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
<!-- HEADER COMPONENT END -->

<script>
    // Utility function to safely access elements
    const getEl = (id) => document.getElementById(id);

    // Toggles the Notifications dropdown visibility
    function toggleNotifications() {
        const notificationsDropdown = getEl('notificationsDropdown');
        const profileDropdown = getEl('profileDropdown');
        
        // Close other dropdown
        if (profileDropdown) profileDropdown.classList.add('hidden');
        
        // Toggle self
        if (notificationsDropdown) {
            notificationsDropdown.classList.toggle('hidden');
            
            // Load notifications when opening dropdown
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
            // Store sidebar state in localStorage
            const isCollapsed = sidebar.classList.contains('collapsed');
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        }
    }

    // Load header notifications from API
    function loadHeaderNotifications() {
        const notificationsList = getEl('notificationsList');
        const loadingEl = getEl('notificationsLoading');
        const emptyEl = getEl('notificationsEmpty');
        
        // Show loading
        if (loadingEl) loadingEl.classList.remove('hidden');
        if (emptyEl) emptyEl.classList.add('hidden');
        
        fetch('{{ route("supervisor.notifications.header") }}', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (loadingEl) loadingEl.classList.add('hidden');
            
            if (data.success && data.notifications.length > 0) {
                // Clear existing notifications (except loading and empty states)
                const existingNotifications = notificationsList.querySelectorAll('.notification-item');
                existingNotifications.forEach(item => item.remove());
                
                // Add new notifications
                data.notifications.forEach(notification => {
                    const notificationHtml = createNotificationItem(notification);
                    notificationsList.insertAdjacentHTML('beforeend', notificationHtml);
                });
                
                if (emptyEl) emptyEl.classList.add('hidden');
            } else {
                // Show empty state
                if (emptyEl) emptyEl.classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
            if (loadingEl) loadingEl.classList.add('hidden');
            if (emptyEl) emptyEl.classList.remove('hidden');
        });
    }

    // Create notification item HTML
    function createNotificationItem(notification) {
        const iconParts = notification.icon_class.split(' ');
        const iconClass = iconParts[0] + ' ' + iconParts[1];
        const bgClass = iconParts.slice(2).join(' ');
        
        return `
            <div class="p-4 border-b border-border-soft hover:bg-cream-bg cursor-pointer transition-colors notification-item"
                 data-notification-id="${notification.id}"
                 onclick="markNotificationAsRead(${notification.id})">
                <div class="flex items-start space-x-3">
                    <div class="w-8 h-8 ${bgClass} flex items-center justify-center flex-shrink-0 rounded-full">
                        <i class="${iconClass} text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-text-dark">${notification.title}</p>
                        <p class="text-xs text-text-muted mt-1">${notification.message}</p>
                        <p class="text-xs text-text-muted mt-2">${notification.time_ago}</p>
                    </div>
                    <div class="w-2 h-2 bg-caramel rounded-full flex-shrink-0 mt-1 unread-dot"></div>
                </div>
            </div>
        `;
    }

    // Mark notification as read
    function markNotificationAsRead(notificationId) {
        fetch(`/supervisor/notifications/${notificationId}/mark-read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI
                const notificationItem = document.querySelector(`[data-notification-id="${notificationId}"]`);
                if (notificationItem) {
                    const unreadDot = notificationItem.querySelector('.unread-dot');
                    if (unreadDot) {
                        unreadDot.remove();
                        // Update notification count
                        updateNotificationCount(-1);
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error marking notification as read:', error);
        });
    }

    // Mark all notifications as read
    function markAllAsRead() {
        fetch('{{ route("supervisor.notifications.mark-all-read") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove all unread indicators
                document.querySelectorAll('.unread-dot').forEach(dot => dot.remove());
                
                // Update notification count to zero
                const notificationCount = getEl('notificationCount');
                if (notificationCount) {
                    notificationCount.classList.add('hidden');
                    notificationCount.textContent = '0';
                }
                
                // Update the count in the header
                const header = getEl('notifHeader');
                if (header) header.textContent = 'Notifications (0)';
            }
        })
        .catch(error => {
            console.error('Error marking all as read:', error);
        });
    }

    // Update notification count in the bell
    function updateNotificationCount(change) {
        const notificationCount = getEl('notificationCount');
        let currentCount = parseInt(notificationCount?.textContent || 0);
        currentCount += change;
        
        if (currentCount <= 0) {
            if (notificationCount) {
                notificationCount.classList.add('hidden');
                notificationCount.textContent = '0';
            }
        } else {
            if (notificationCount) {
                notificationCount.classList.remove('hidden');
                notificationCount.textContent = currentCount > 99 ? '99+' : currentCount;
            }
        }
        
        // Update dropdown header
        const header = getEl('notifHeader');
        if (header) {
            header.textContent = `Notifications (${Math.max(0, currentCount)})`;
        }
    }

    // Load initial notification count
    function loadNotificationCount() {
        fetch('{{ route("supervisor.notifications.unread_count") }}', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const notificationCount = getEl('notificationCount');
                const header = getEl('notifHeader');
                
                if (data.count > 0) {
                    if (notificationCount) {
                        notificationCount.classList.remove('hidden');
                        notificationCount.textContent = data.count > 99 ? '99+' : data.count;
                    }
                    if (header) {
                        header.textContent = `Notifications (${data.count})`;
                    }
                } else {
                    if (notificationCount) {
                        notificationCount.classList.add('hidden');
                    }
                    if (header) {
                        header.textContent = 'Notifications (0)';
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error loading notification count:', error);
        });
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        const notificationsBtn = getEl('notificationsBtn');
        const profileBtn = getEl('profileBtn');
        const notificationsDropdown = getEl('notificationsDropdown');
        const profileDropdown = getEl('profileDropdown');
        
        // Close notifications if click is outside the button and the dropdown
        if (notificationsDropdown && notificationsBtn && !notificationsBtn.contains(event.target) && !notificationsDropdown.contains(event.target)) {
            notificationsDropdown.classList.add('hidden');
        }
        
        // Close profile if click is outside the button and the dropdown
        if (profileDropdown && profileBtn && !profileBtn.contains(event.target) && !profileDropdown.contains(event.target)) {
            profileDropdown.classList.add('hidden');
        }
    });

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Restore sidebar state from localStorage
        const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        const sidebar = getEl('sidebar');
        if (sidebar) {
            if (sidebarCollapsed) {
                sidebar.classList.add('collapsed');
            }
        }
        
        // Load initial notification count
        loadNotificationCount();
        
        // Refresh notification count every 60 seconds
        setInterval(loadNotificationCount, 60000);
    });
</script>
