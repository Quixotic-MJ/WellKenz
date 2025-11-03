<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'WellKenz - Cakes & Pastries')</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

   <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

    
    <!-- Custom Styles matching the sidebar theme -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'cream-bg': '#faf7f3',
                        'white': '#ffffff',
                        'chocolate': '#3d2817',
                        'chocolate-dark': '#2a1a0f',
                        'caramel': '#c48d3f',
                        'caramel-dark': '#a67332',
                        'text-dark': '#1a1410',
                        'text-muted': '#8b7355',
                        'border-soft': '#e8dfd4',
                    },
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@400;500;600;700&display=swap');
        
        .body-pattern {
            background-color: #faf7f3;
            background-image: url("data:image/svg+xml,%3Csvg width='10' height='10' viewBox='0 0 10 10' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23c48d3f' fill-opacity='0.08' fill-rule='evenodd'%3E%3Cpath d='M0 0h1v1H0zM9 0h1v1H9zM0 9h1v1H0zM9 9h1v1H9z'/%3E%3C/g%3E%3C/svg%3E");
        }
        
        .geometric-bg {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23c48d3f' fill-opacity='0.08' fill-rule='evenodd'%3E%3Cpath d='M0 0h30v30H0V0zm30 30h30v30H30V30z'/%3E%3C/g%3E%3C/svg%3E");
            background-size: 30px 30px;
        }
        
        .sidebar {
            transition: all 0.3s ease;
            background: linear-gradient(180deg, #3d2817 0%, #2a1a0f 100%);
        }
        
        .sidebar.collapsed {
            width: 70px;
        }
        
        .sidebar.collapsed .sidebar-text {
            display: none;
        }
        
        .active-menu {
            background-color: rgba(196, 141, 63, 0.15);
            color: #c48d3f;
            border-left: 3px solid #c48d3f;
        }
        
        .hover-lift:hover {
            transform: translateY(-2px);
            transition: transform 0.2s ease;
        }
        
        /* Custom scrollbar for main content */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #c48d3f;
            border-radius: 3px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #a67332;
        }
        
        /* Floating animation for sidebar icon */
        .animate-float {
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-5px); }
        }
        
        /* Hide scrollbar for sidebar */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>
<body class="antialiased body-pattern font-sans">
    <div class="flex h-screen">
        <!-- Sidebar -->
        @include('Inventory.layout.sidebar')
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Header -->
            @include('Admin.layout.header')
            
            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6 custom-scrollbar">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Toggle sidebar
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('collapsed');
        }

        // Set active menu item
        function setActiveMenu(menuId) {
            document.querySelectorAll('.menu-item').forEach(item => {
                item.classList.remove('active-menu');
            });
            document.getElementById(menuId).classList.add('active-menu');
        }

        // Toggle dropdowns
        function toggleNotifications() {
            document.getElementById('notificationsDropdown').classList.toggle('hidden');
        }

        function toggleProfile() {
            document.getElementById('profileDropdown').classList.toggle('hidden');
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            const notificationsBtn = document.getElementById('notificationsBtn');
            const profileBtn = document.getElementById('profileBtn');
            const notificationsDropdown = document.getElementById('notificationsDropdown');
            const profileDropdown = document.getElementById('profileDropdown');

            if (notificationsBtn && !notificationsBtn.contains(event.target) && notificationsDropdown && !notificationsDropdown.contains(event.target)) {
                notificationsDropdown.classList.add('hidden');
            }

            if (profileBtn && !profileBtn.contains(event.target) && profileDropdown && !profileDropdown.contains(event.target)) {
                profileDropdown.classList.add('hidden');
            }
        });

        // Set dashboard as active by default
        document.addEventListener('DOMContentLoaded', function() {
            setActiveMenu('menu-dashboard');
        });
    </script>
    
    @yield('scripts')
</body>
</html>