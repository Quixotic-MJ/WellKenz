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
                    <!-- Static Count -->
                    <span class="absolute -top-1 -right-1 bg-caramel text-white text-xs h-5 w-5 rounded-full flex items-center justify-center font-bold ring-2 ring-white" id="notificationCount">
                        3
                    </span>
                </button>
                
                <!-- Notifications Dropdown -->
                <div id="notificationsDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white shadow-lg border-2 border-border-soft z-50 rounded-lg" role="menu">
                    <div class="p-3 border-b-2 border-border-soft bg-cream-bg rounded-t-lg flex justify-between items-center">
                        <h3 class="font-bold text-text-dark text-xs uppercase tracking-wider" id="notifHeader">
                            Notifications (3)
                        </h3>
                        <button onclick="markAllAsRead()" class="text-xs text-chocolate hover:text-chocolate-dark font-bold">
                            Mark all as read
                        </button>
                    </div>
                    <div class="max-h-80 overflow-y-auto" id="notificationsList">
                        
                        <!-- Mock Notification 1 -->
                        <div class="p-4 border-b border-border-soft hover:bg-cream-bg cursor-pointer transition-colors notification-item"
                             data-notification-id="1"
                             onclick="markNotificationAsRead(1)">
                            <div class="flex items-start space-x-3">
                                <div class="w-8 h-8 bg-red-100 flex items-center justify-center flex-shrink-0 rounded-full">
                                    <i class="fas fa-exclamation-triangle text-red-600 text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-text-dark">Low Stock Alert</p>
                                    <p class="text-xs text-text-muted mt-1">Cake Flour is below reorder level (5kg remaining).</p>
                                    <p class="text-xs text-text-muted mt-2">2 mins ago</p>
                                </div>
                                <div class="w-2 h-2 bg-caramel rounded-full flex-shrink-0 mt-1 unread-dot"></div>
                            </div>
                        </div>

                        <!-- Mock Notification 2 -->
                        <div class="p-4 border-b border-border-soft hover:bg-cream-bg cursor-pointer transition-colors notification-item"
                             data-notification-id="2"
                             onclick="markNotificationAsRead(2)">
                            <div class="flex items-start space-x-3">
                                <div class="w-8 h-8 bg-blue-100 flex items-center justify-center flex-shrink-0 rounded-full">
                                    <i class="fas fa-file-invoice text-blue-600 text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-text-dark">New Requisition</p>
                                    <p class="text-xs text-text-muted mt-1">Baker John requested 50kg Sugar.</p>
                                    <p class="text-xs text-text-muted mt-2">1 hour ago</p>
                                </div>
                                <div class="w-2 h-2 bg-caramel rounded-full flex-shrink-0 mt-1 unread-dot"></div>
                            </div>
                        </div>

                        <!-- Mock Notification 3 -->
                        <div class="p-4 border-b border-border-soft hover:bg-cream-bg cursor-pointer transition-colors notification-item"
                             data-notification-id="3"
                             onclick="markNotificationAsRead(3)">
                            <div class="flex items-start space-x-3">
                                <div class="w-8 h-8 bg-green-100 flex items-center justify-center flex-shrink-0 rounded-full">
                                    <i class="fas fa-check-circle text-green-600 text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-text-dark">PO Delivered</p>
                                    <p class="text-xs text-text-muted mt-1">Order #PO-882 from Golden Grain has arrived.</p>
                                    <p class="text-xs text-text-muted mt-2">3 hours ago</p>
                                </div>
                                <div class="w-2 h-2 bg-caramel rounded-full flex-shrink-0 mt-1 unread-dot"></div>
                            </div>
                        </div>

                    </div>
                    <div class="p-3 border-t-2 border-border-soft rounded-b-lg">
                        <a href="#" class="block text-center text-xs font-bold text-chocolate hover:text-chocolate-dark transition uppercase tracking-wider">
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
                        <span class="text-white text-sm font-bold">AD</span>
                    </div>
                    <div class="hidden lg:block text-left pr-1">
                        <p class="text-sm font-semibold text-text-dark leading-none">Admin User</p>
                        <p class="text-xs text-text-muted leading-none mt-0.5">Administrator</p>
                    </div>
                    <i class="fas fa-chevron-down text-text-muted text-xs hidden lg:block"></i>
                </button>

                <!-- Profile Dropdown -->
                <div id="profileDropdown" class="hidden absolute right-0 mt-2 w-56 bg-white shadow-lg border-2 border-border-soft z-50 rounded-lg" role="menu">
                    <div class="p-4 border-b-2 border-border-soft bg-cream-bg rounded-t-lg">
                        <div class="flex items-center space-x-3 mb-2">
                            <div class="w-10 h-10 bg-caramel flex items-center justify-center rounded-full flex-shrink-0">
                                <span class="text-white text-base font-bold">AD</span>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-text-dark">Admin User</p>
                                <p class="text-xs text-text-muted">Administrator</p>
                            </div>
                        </div>
                        <p class="text-xs text-text-muted mt-2 border-t border-border-soft pt-2 truncate">
                            admin_main • Administrator
                        </p>
                        <p class="text-xs text-text-muted truncate">
                            admin@wellkenz.com
                        </p>
                        <div class="flex items-center mt-1">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                Active
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
                        <!-- Mock Logout -->
                        <a href="/" class="flex items-center space-x-3 w-full px-3 py-2 text-sm font-bold text-white bg-chocolate hover:bg-chocolate-dark transition justify-center rounded-md">
                            <i class="fas fa-sign-out-alt w-4 text-center"></i>
                            <span>Sign Out</span>
                        </a>
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
        if (notificationsDropdown) notificationsDropdown.classList.toggle('hidden');
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

    // Mock: Mark notification as read (Frontend Only)
    function markNotificationAsRead(notificationId) {
        // Update UI
        const notificationItem = document.querySelector(`[data-notification-id="${notificationId}"]`);
        if (notificationItem) {
            const unreadDot = notificationItem.querySelector('.unread-dot');
            if (unreadDot) {
                unreadDot.remove();
                // Update notification count locally
                updateNotificationCount(-1);
            }
        }
    }

    // Mock: Mark all notifications as read (Frontend Only)
    function markAllAsRead() {
        // Remove all unread indicators
        document.querySelectorAll('.unread-dot').forEach(dot => dot.remove());
        
        // Update notification count to zero
        const notificationCount = getEl('notificationCount');
        if (notificationCount) {
            notificationCount.remove();
        }
        
        // Update the count in the header
        const header = getEl('notifHeader');
        if (header) header.textContent = 'Notifications (0)';
    }

    // Update notification count in the bell
    function updateNotificationCount(change) {
        const notificationCount = getEl('notificationCount');
        let currentCount = parseInt(notificationCount?.textContent || 0);
        currentCount += change;
        
        if (currentCount <= 0) {
            if (notificationCount) notificationCount.remove();
        } else {
            if (!notificationCount) {
                // Create new count badge if it doesn't exist and count > 0
                const bell = getEl('notificationsBtn');
                const badge = document.createElement('span');
                badge.id = 'notificationCount';
                badge.className = 'absolute -top-1 -right-1 bg-caramel text-white text-xs h-5 w-5 rounded-full flex items-center justify-center font-bold ring-2 ring-white';
                badge.textContent = currentCount > 99 ? '99+' : currentCount;
                bell.appendChild(badge);
            } else {
                notificationCount.textContent = currentCount > 99 ? '99+' : currentCount;
            }
        }
        
        // Update dropdown header
        const header = getEl('notifHeader');
        if (header) {
            header.textContent = `Notifications (${Math.max(0, currentCount)})`;
        }
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

    // Restore sidebar state from localStorage
    document.addEventListener('DOMContentLoaded', function() {
        const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        const sidebar = getEl('sidebar');
        if (sidebar) {
            if (sidebarCollapsed) {
                sidebar.classList.add('collapsed');
            }
        }
    });
</script>