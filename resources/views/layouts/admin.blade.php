<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - ELMSP</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-header {
            background: #dbeafe;
        }
        .sidebar-gradient {
            background: #1e40af;
        }
        .main-content-bg {
            background: #eff6ff;
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
            background-color: #3b82f6 !important;
            border-left: 4px solid #fbbf24;
            color: #fbbf24 !important;
        }
        .fa-chevron-right {
            transition: transform 0.3s ease;
        }
        #staff-dropdown {
            transition: all 0.3s ease;
        }
        .card-gradient-blue {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        }
        .card-gradient-green {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
        }
        .card-gradient-purple {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
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
                    <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-user mr-2"></i>Profile
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
                        <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }} flex items-center px-4 py-3 text-white rounded-lg">
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
                        <a href="#" class="nav-item flex items-center justify-between px-4 py-3 text-white rounded-lg" onclick="toggleStaffDropdown(event)">
                            <div class="flex items-center">
                                <i class="fas fa-users mr-3"></i>
                                <span>Staff</span>
                            </div>
                            <i class="fas fa-chevron-right text-sm" id="staff-chevron" style="transform: {{ request()->routeIs('admin.manage-staff') || request()->routeIs('admin.staff-*') ? 'rotate(90deg)' : 'rotate(0deg)' }}"></i>
                        </a>
                        <ul class="ml-4 mt-2 space-y-1 {{ request()->routeIs('admin.manage-staff') || request()->routeIs('admin.staff-*') ? '' : 'hidden' }}" id="staff-dropdown">
                            <li>
                                <a href="{{ route('admin.manage-staff') }}" class="nav-item {{ request()->routeIs('admin.manage-staff') ? 'active' : '' }} flex items-center px-4 py-2 text-white rounded-lg text-sm">
                                    <i class="fas fa-list mr-3"></i>
                                    <span>Staff Management</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.staff-timetable') }}" class="nav-item {{ request()->routeIs('admin.staff-timetable') ? 'active' : '' }} flex items-center px-4 py-2 text-white rounded-lg text-sm">
                                    <i class="fas fa-calendar-days mr-3"></i>
                                    <span>Staff Timetable</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.staff-leave-status') }}" class="nav-item {{ request()->routeIs('admin.staff-leave-status') ? 'active' : '' }} flex items-center px-4 py-2 text-white rounded-lg text-sm">
                                    <i class="fas fa-calendar-check mr-3"></i>
                                    <span>Staff Leave Status</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="#" class="nav-item flex items-center justify-between px-4 py-3 text-white rounded-lg" onclick="togglePayrollDropdown(event)">
                            <div class="flex items-center">
                                <i class="fas fa-money-check-alt mr-3"></i>
                                <span>Payroll</span>
                            </div>
                            <i class="fas fa-chevron-right text-sm" id="payroll-chevron" style="transform: {{ request()->routeIs('admin.payroll') || request()->routeIs('admin.payslip') || request()->routeIs('admin.payslip.*') ? 'rotate(90deg)' : 'rotate(0deg)' }}"></i>
                        </a>
                        <ul class="ml-4 mt-2 space-y-1 {{ request()->routeIs('admin.payroll') || request()->routeIs('admin.payslip') || request()->routeIs('admin.payslip.*') ? '' : 'hidden' }}" id="payroll-dropdown">
                            <li>
                                <a href="{{ route('admin.payroll') }}" class="nav-item {{ request()->routeIs('admin.payroll') ? 'active' : '' }} flex items-center px-4 py-2 text-white rounded-lg text-sm" onclick="event.stopPropagation();">
                                    <i class="fas fa-calculator mr-3"></i>
                                    <span>Calculation</span>
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('admin.payslip') }}" class="nav-item {{ request()->routeIs('admin.payslip') ? 'active' : '' }} flex items-center px-4 py-2 text-white rounded-lg text-sm" onclick="event.stopPropagation();">
                                    <i class="fas fa-file-invoice-dollar mr-3"></i>
                                    <span>Staff Payslip</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            <div id="mainContent" class="mt-20 ml-64 transition-all duration-300">
                @yield('content')
            </div>
        </main>
    </div>

    <script>
        // Payroll dropdown toggle function (persistent open)
        function togglePayrollDropdown(event) {
            event.preventDefault();
            const dropdown = document.getElementById('payroll-dropdown');
            const chevron = document.getElementById('payroll-chevron');
            dropdown.classList.toggle('hidden');
            chevron.style.transform = dropdown.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(90deg)';
        }
        // Sidebar toggle functionality
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const isHidden = sidebar.classList.contains('-ml-64');
            
            if (isHidden) {
                sidebar.classList.remove('-ml-64');
                mainContent.classList.remove('ml-0');
                mainContent.classList.add('ml-64');
            } else {
                sidebar.classList.add('-ml-64');
                mainContent.classList.remove('ml-64');
                mainContent.classList.add('ml-0');
            }
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
            const mainContent = document.getElementById('mainContent');
            if (window.innerWidth < 768) {
                sidebar.classList.add('-ml-64');
                mainContent.classList.remove('ml-64');
                mainContent.classList.add('ml-0');
            } else {
                sidebar.classList.remove('-ml-64');
                mainContent.classList.remove('ml-0');
                mainContent.classList.add('ml-64');
            }
        }

        window.addEventListener('resize', handleResize);
        handleResize(); // Initial call

        // Staff dropdown toggle function
        function toggleStaffDropdown(event) {
            event.preventDefault();
            const dropdown = document.getElementById('staff-dropdown');
            const chevron = document.getElementById('staff-chevron');
            dropdown.classList.toggle('hidden');
            chevron.style.transform = dropdown.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(90deg)';
        }
        // Dynamic menu active state
        document.addEventListener('DOMContentLoaded', function() {
            const menuItems = document.querySelectorAll('.nav-item');
            menuItems.forEach(function(item) {
                item.addEventListener('click', function(e) {
                    // Only set active for actual links, not dropdown toggles
                    if (this.getAttribute('onclick') && this.getAttribute('onclick').includes('toggle')) {
                        return;
                    }
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

    @yield('scripts')
</body>
</html>
