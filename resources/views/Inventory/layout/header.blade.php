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
                    <span class="absolute -top-1 -right-1 bg-caramel text-white text-xs h-5 w-5 rounded-full flex items-center justify-center font-bold ring-2 ring-white">5</span>
                </button>
                
                <!-- Notifications Dropdown -->
                <div id="notificationsDropdown" class="hidden absolute right-0 mt-2 w-80 bg-white shadow-lg border-2 border-border-soft z-50 rounded-lg" role="menu">
                    <div class="p-3 border-b-2 border-border-soft bg-cream-bg rounded-t-lg">
                        <h3 class="font-bold text-text-dark text-xs uppercase tracking-wider">Notifications (5)</h3>
                    </div>
                    <div class="max-h-80 overflow-y-auto">
                        <!-- Notification Items -->
                        <div class="p-4 border-b border-border-soft hover:bg-cream-bg cursor-pointer transition-colors">
                            <div class="flex items-start space-x-3">
                                <div class="w-8 h-8 bg-caramel flex items-center justify-center flex-shrink-0 rounded-full">
                                    <i class="fas fa-shopping-bag text-white text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-text-dark">New Order Received</p>
                                    <p class="text-xs text-text-muted mt-1">Wedding cake - 50 servings</p>
                                    <p class="text-xs text-text-muted mt-2">5 min ago</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="p-4 border-b border-border-soft hover:bg-cream-bg cursor-pointer transition-colors">
                            <div class="flex items-start space-x-3">
                                <div class="w-8 h-8 bg-chocolate flex items-center justify-center flex-shrink-0 rounded-full">
                                    <i class="fas fa-box text-white text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-text-dark">Low Stock Alert</p>
                                    <p class="text-xs text-text-muted mt-1">Chocolate chips running low</p>
                                    <p class="text-xs text-text-muted mt-2">1 hour ago</p>
                                </div>
                            </div>
                        </div>

                        <div class="p-4 border-b border-border-soft hover:bg-cream-bg cursor-pointer transition-colors">
                            <div class="flex items-start space-x-3">
                                <div class="w-8 h-8 bg-caramel flex items-center justify-center flex-shrink-0 rounded-full">
                                    <i class="fas fa-truck text-white text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-semibold text-text-dark">Delivery Completed</p>
                                    <p class="text-xs text-text-muted mt-1">Order #4523 delivered successfully</p>
                                    <p class="text-xs text-text-muted mt-2">2 hours ago</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="p-3 border-t-2 border-border-soft rounded-b-lg">
                        <a href="#" class="block text-center text-xs font-bold text-chocolate hover:text-chocolate-dark transition uppercase tracking-wider">
                            View All
                        </a>
                    </div>
                </div>
            </div>

            <!-- Profile -->
            <div class="relative" role="menu">
                <button id="profileBtn" onclick="toggleProfile()" 
                        class="flex items-center space-x-2 p-1.5 hover:bg-cream-bg transition-colors focus:outline-none square-button">
                    <div class="w-8 h-8 bg-caramel flex items-center justify-center rounded-full flex-shrink-0">
                        <span class="text-white text-sm font-bold">
                            {{ substr(session('emp_name'), 0, 1) }}{{ substr(strstr(session('emp_name'), ' ') ?: '', 1, 1) }}
                        </span>
                    </div>
                    <div class="hidden lg:block text-left pr-1">
                        <p class="text-sm font-semibold text-text-dark leading-none">{{ session('emp_name') }}</p>
                        <p class="text-xs text-text-muted leading-none mt-0.5">{{ session('emp_position') }}</p>
                    </div>
                    <i class="fas fa-chevron-down text-text-muted text-xs hidden lg:block"></i>
                </button>

                <!-- Profile Dropdown -->
                <div id="profileDropdown" class="hidden absolute right-0 mt-2 w-56 bg-white shadow-lg border-2 border-border-soft z-50 rounded-lg" role="menu">
                    <div class="p-4 border-b-2 border-border-soft bg-cream-bg rounded-t-lg">
                        <div class="flex items-center space-x-3 mb-2">
                            <div class="w-10 h-10 bg-caramel flex items-center justify-center rounded-full flex-shrink-0">
                                <span class="text-white text-base font-bold">
                                    {{ substr(session('emp_name'), 0, 1) }}{{ substr(strstr(session('emp_name'), ' ') ?: '', 1, 1) }}
                                </span>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-text-dark">{{ session('emp_name') }}</p>
                                <p class="text-xs text-text-muted">{{ session('emp_position') }}</p>
                            </div>
                        </div>
                        <p class="text-xs text-text-muted mt-2 border-t border-border-soft pt-2 truncate">{{ session('username') }}</p>
                    </div>
                    <div class="p-2">
                        <a href="#" class="flex items-center space-x-3 px-3 py-2 text-sm text-text-dark hover:bg-cream-bg transition rounded-md">
                            <i class="fas fa-cog text-text-muted w-4 text-center"></i>
                            <span>Settings</span>
                        </a>
                    </div>
                    <div class="p-2 border-t-2 border-border-soft">
                        <form action="{{ route('logout') }}" method="POST">
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
</script>