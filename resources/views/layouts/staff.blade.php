<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Staff Dashboard') - AI ELMSP</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-header {
            background: #e8d5f2;
        }
        .sidebar-gradient {
            background: #6b46c1;
        }
        .main-content-bg {
            background: #f3e8ff;
        }
        .nav-item {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .nav-item:hover {
            background-color: rgba(255, 255, 255, 0.15);
            color: #fbbf24;
        }
        .nav-item.active {
            background-color: #8b5cf6 !important;
            border-left: 4px solid #fbbf24;
            color: #fbbf24 !important;
        }
        .card-gradient-blue {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        }
        .card-gradient-green {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
        }
        .card-gradient-purple {
            background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%);
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <header class="gradient-header h-16 flex items-center justify-between px-6 shadow-lg fixed top-0 left-0 right-0 z-50">
        <div class="flex items-center">
            <button id="sidebarToggle" class="text-black text-xl mr-4">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        <div class="flex items-center">
            <div class="relative">
                <button id="userMenuButton" class="text-black text-xl hover:text-gray-600 transition-colors">
                    <i class="fas fa-user-circle"></i>
                </button>
                
                <!-- Dropdown Menu -->
                <div id="userMenu" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 hidden">
                    <div class="px-4 py-2 text-sm text-gray-700 border-b">
                        <div class="font-medium">{{ Auth::user()->name }}</div>
                        <div class="text-gray-500">{{ Auth::user()->email }}</div>
                    </div>
                    <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-cog mr-2"></i>Settings
                    </a>
                    <div class="border-t">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="flex">
    <!-- Sidebar -->
    <aside id="sidebar" class="sidebar-gradient w-64 min-h-screen shadow-xl transition-all duration-300 fixed top-16 left-0 z-40 h-[calc(100vh-4rem)]">
            <!-- Logo Section -->
            <div class="p-6 text-center">
                <div class="w-20 h-20 mx-auto mb-4 bg-white rounded-full shadow-lg flex items-center justify-center p-2">
                    <img src="{{ asset('images/elmsp-logo.png') }}" alt="ELMSP Logo" class="w-full h-full object-contain rounded-full">
                </div>
            </div>

            <!-- Navigation Menu -->
            <nav class="px-4">
                <ul class="space-y-2">
                    <li>
                        <a href="{{ route('staff.dashboard') }}" class="nav-item {{ request()->routeIs('staff.dashboard') ? 'active' : '' }} flex items-center px-4 py-3 text-white rounded-lg">
                            <i class="fas fa-tachometer-alt mr-3"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('profile.show') }}" class="nav-item {{ request()->routeIs('profile.*') ? 'active' : '' }} flex items-center px-4 py-3 text-white rounded-lg">
                            <i class="fas fa-user mr-3"></i>
                            <span>Profile</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('staff.timetable') }}" class="nav-item {{ request()->routeIs('staff.timetable') ? 'active' : '' }} flex items-center px-4 py-3 text-white rounded-lg">
                            <i class="fas fa-calendar-days mr-3"></i>
                            <span>My Timetable</span>
                        </a>
                    </li>
                    <a href="#" class="nav-item flex items-center justify-between px-4 py-3 text-white rounded-lg" onclick="toggleOtDropdown(event)">
                        <div class="flex items-center">
                            <i class="fas fa-clock mr-3"></i>
                            <span>Overtime</span>
                        </div>
                        <i class="fas fa-chevron-right text-sm" id="overtime-chevron" style="transform: {{ request()->routeIs('staff.*Ot') ? 'rotate(90deg)' : 'rotate(0deg)' }}"></i>
                    </a>
                    <ul class="ml-4 mt-2 space-y-1 {{ request()->routeIs('staff.*Ot') ? '' : 'hidden' }}" id="overtime-dropdown">
                        <li>
                            <a href="{{ route('staff.claimOt') }}" class="nav-item flex items-center px-4 py-2 text-white rounded-lg text-sm {{ request()->routeIs('staff.claimOt') ? 'active' : '' }}">
                                <i class="fas fa-exchange-alt mr-3"></i>
                                <span>Claim</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('staff.applyOt') }}" class="nav-item flex items-center px-4 py-2 text-white rounded-lg text-sm {{ request()->routeIs('staff.applyOt') ? 'active' : '' }}">
                                <i class="fas fa-clock mr-3"></i>
                                <span>Apply</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('staff.statusOt') }}" class="nav-item flex items-center px-4 py-2 text-white rounded-lg text-sm {{ request()->routeIs('staff.statusOt') ? 'active' : '' }}">
                                <i class="fas fa-list-check mr-3"></i>
                                <span>Status</span>
                            </a>
                        </li>
                    </ul>
                    <a href="#" class="nav-item flex items-center justify-between px-4 py-3 text-white rounded-lg" onclick="toggleLeaveDropdown(event)">
                        <div class="flex items-center">
                            <i class="fas fa-calendar-times mr-3"></i>
                            <span>Leave</span>
                        </div>
                        <i class="fas fa-chevron-right text-sm" id="leave-chevron" style="transform: {{ request()->routeIs('staff.leave-*') ? 'rotate(90deg)' : 'rotate(0deg)' }}"></i>
                    </a>
                    <ul class="ml-4 mt-2 space-y-1 {{ request()->routeIs('staff.leave-*') ? '' : 'hidden' }}" id="leave-dropdown">
                        <li>
                            <a href="{{ route('staff.leave-application') }}" class="nav-item flex items-center px-4 py-2 text-white rounded-lg text-sm {{ request()->routeIs('staff.leave-application') ? 'active' : '' }}">
                                <i class="fas fa-file-alt mr-3"></i>
                                <span>Application</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('staff.leave-status') }}" class="nav-item flex items-center px-4 py-2 text-white rounded-lg text-sm {{ request()->routeIs('staff.leave-status') ? 'active' : '' }}">
                                <i class="fas fa-info-circle mr-3"></i>
                                <span>Status</span>
                            </a>
                        </li>
                    </ul>
                    <li>
                        <a href="{{ route('staff.payslip') }}" class="nav-item flex items-center px-4 py-3 text-white rounded-lg">
                            <i class="fas fa-money-bill-wave mr-3"></i>
                            <span>Payslip</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">

            <div class="mt-20 ml-64">
                @yield('content')
            </div>
        </main>
    </div>

    <script>
        function toggleLeaveDropdown(event) {
            event.preventDefault();
            const dropdown = document.getElementById('leave-dropdown');
            const chevron = document.getElementById('leave-chevron');
            dropdown.classList.toggle('hidden');
            chevron.style.transform = dropdown.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(90deg)';
        }

        function toggleOtDropdown(event) {
            event.preventDefault();
            const dropdown = document.getElementById('overtime-dropdown');
            const chevron = document.getElementById('overtime-chevron');
            dropdown.classList.toggle('hidden');
            chevron.style.transform = dropdown.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(90deg)';
        }

        // Sidebar toggle functionality
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('-ml-64');
        });

        // User menu toggle functionality
        document.getElementById('userMenuButton').addEventListener('click', function(e) {
            e.stopPropagation();
            const userMenu = document.getElementById('userMenu');
            userMenu.classList.toggle('hidden');
        });

        // Close user menu when clicking outside
        document.addEventListener('click', function(e) {
            const userMenu = document.getElementById('userMenu');
            const userMenuButton = document.getElementById('userMenuButton');
            
            if (!userMenu.contains(e.target) && !userMenuButton.contains(e.target)) {
                userMenu.classList.add('hidden');
            }
        });

        // Mobile responsive sidebar
        function handleResize() {
            const sidebar = document.getElementById('sidebar');
            if (window.innerWidth < 768) {
                sidebar.classList.add('-ml-64');
            } else {
                sidebar.classList.remove('-ml-64');
            }
        }

        window.addEventListener('resize', handleResize);
        handleResize(); // Initial call

        // Dynamic menu active state
        document.addEventListener('DOMContentLoaded', function() {
            const menuItems = document.querySelectorAll('.nav-item');
            
            menuItems.forEach(function(item) {
                item.addEventListener('click', function(e) {
                    // Remove active class from all menu items
                    menuItems.forEach(function(menuItem) {
                        menuItem.classList.remove('active');
                    });
                    
                    // Add active class to clicked item
                    this.classList.add('active');
                });
            });
        });
    </script>
</body>
</html>


