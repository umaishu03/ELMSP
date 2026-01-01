<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Staff Dashboard') - ELMSP</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
    <header class="gradient-header h-16 flex items-center justify-between px-6 shadow-lg fixed top-0 left-0 right-0 z-[60]" style="pointer-events: auto;">
        <div class="flex items-center">
            <button id="sidebarToggle" class="text-black text-xl mr-4">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        <div class="flex items-center">
            <div class="relative">
                <button id="userMenuButton" class="text-black text-xl hover:text-gray-600 transition-colors" style="pointer-events: auto; position: relative; z-index: 70;">
                    <i class="fas fa-user-circle"></i>
                </button>
                
                <!-- Dropdown Menu -->
                <div id="userMenu" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-[70] hidden" style="pointer-events: auto;">
                    <div class="px-4 py-2 text-sm text-gray-700 border-b">
                        <div class="font-medium">{{ Auth::user()->name }}</div>
                        <div class="text-gray-500">{{ Auth::user()->email }}</div>
                    </div>
                    <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-user mr-2"></i>Profile
                    </a>
                    <div class="border-t">
                        <form method="POST" action="{{ route('logout') }}" id="logoutForm" style="pointer-events: auto;">
                            @csrf
                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100" style="pointer-events: auto; cursor: pointer;" onclick="event.stopPropagation(); return true;">
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
                        <a href="{{ route('staff.payslip') }}" class="nav-item {{ request()->routeIs('staff.payslip') || request()->routeIs('staff.payslip.*') ? 'active' : '' }} flex items-center px-4 py-3 text-white rounded-lg">
                            <i class="fas fa-money-bill-wave mr-3"></i>
                            <span>Payslip</span>
                        </a>
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
            const logoutForm = userMenu ? userMenu.querySelector('form[action*="logout"]') : null;
            const logoutButton = logoutForm ? logoutForm.querySelector('button[type="submit"]') : null;
            
            // Don't close menu if clicking on logout button or form
            if (logoutButton && (logoutButton.contains(e.target) || e.target === logoutButton)) {
                return; // Allow logout to proceed
            }
            if (logoutForm && logoutForm.contains(e.target)) {
                return; // Allow logout form to submit
            }
            
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
            
            // Ensure logout button is always clickable
            const logoutForm = document.getElementById('logoutForm');
            const logoutButton = logoutForm ? logoutForm.querySelector('button[type="submit"]') : null;
            
            if (logoutButton) {
                // Remove any event listeners that might block it
                logoutButton.addEventListener('click', function(e) {
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    // Allow form to submit normally
                    return true;
                }, true); // Use capture phase to ensure it fires first
                
                logoutForm.addEventListener('submit', function(e) {
                    e.stopPropagation();
                    // Allow form submission
                    return true;
                }, true);
            }
        });
    </script>
    
    <!-- Chatbot Widget -->
    <div id="chatbot-widget" style="position: fixed; bottom: 20px; right: 20px; z-index: 1050;">
        <!-- Chatbot Button -->
        <button id="chatbot-toggle" class="btn btn-primary rounded-circle shadow-lg position-relative" style="width: 60px; height: 60px; background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%); border: none; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(111, 66, 193, 0.4);">
            <i class="fas fa-comments fs-4"></i>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display: none;" id="chatbot-notification">
                0
            </span>
        </button>
        
        <!-- Chatbot Window -->
        <div id="chatbot-window" class="card shadow-lg border-0" style="display: none; position: fixed; bottom: 100px; right: 20px; width: 380px; max-height: calc(100vh - 180px); height: 600px; z-index: 1050; animation: slideUp 0.3s ease-out;">
            <!-- Chatbot Header -->
            <div class="card-header text-white border-0" style="background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%); border-radius: 0.375rem 0.375rem 0 0 !important;">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 45px; height: 45px;">
                            <i class="fas fa-robot text-primary fs-5" style="color: #6f42c1 !important;"></i>
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold">ELMSP Assistant</h5>
                            <small class="opacity-75">
                                <span class="badge bg-success bg-opacity-50">
                                    <i class="fas fa-circle me-1" style="font-size: 6px; vertical-align: middle;"></i>
                                    Online
                                </span>
                            </small>
                        </div>
                    </div>
                    <button id="chatbot-close" class="btn btn-link text-white p-0" style="text-decoration: none; opacity: 0.8; transition: opacity 0.2s;">
                        <i class="fas fa-times fs-5"></i>
                    </button>
                </div>
            </div>
            
            <!-- Chat Messages -->
            <div id="chatbot-messages" class="card-body p-3" style="overflow-y: auto; height: calc(100% - 120px); min-height: 400px; max-height: 500px; background: linear-gradient(to bottom, #f8f9fa 0%, #ffffff 100%);">
                <div class="d-flex align-items-start gap-2 mb-3 animate-message">
                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 35px; height: 35px; background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%) !important;">
                        <i class="fas fa-robot text-white" style="font-size: 0.875rem;"></i>
                    </div>
                    <div class="bg-white rounded-3 p-3 shadow-sm" style="max-width: 80%; border-left: 3px solid #6f42c1;">
                        <p class="mb-0 small text-dark" style="line-height: 1.5;">Hello! I'm your ELMSP assistant. I can help you with leave, shifts, overtime, payroll, and system information. How can I assist you today?</p>
                        <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">Just now</small>
                    </div>
                </div>
            </div>
            
            <!-- Chat Input -->
            <div class="card-footer bg-white border-top">
                <form id="chatbot-form" class="d-flex gap-2">
                    <input type="text" id="chatbot-input" placeholder="Type your message..." class="form-control form-control-sm" autocomplete="off" style="border-radius: 20px; border: 1px solid #dee2e6; transition: all 0.3s;">
                    <button type="submit" class="btn btn-primary btn-sm rounded-circle" style="width: 38px; height: 38px; background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%); border: none; transition: transform 0.2s;">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <style>
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes messageSlide {
            from {
                opacity: 0;
                transform: translateX(-10px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .animate-message {
            animation: messageSlide 0.3s ease-out;
        }
        
        #chatbot-toggle:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(111, 66, 193, 0.6) !important;
        }
        
        #chatbot-close:hover {
            opacity: 1 !important;
            transform: rotate(90deg);
        }
        
        #chatbot-input:focus {
            border-color: #6f42c1 !important;
            box-shadow: 0 0 0 0.2rem rgba(111, 66, 193, 0.25) !important;
        }
        
        #chatbot-form button:hover {
            transform: scale(1.1);
        }
        
        #chatbot-messages::-webkit-scrollbar {
            width: 6px;
        }
        
        #chatbot-messages::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        #chatbot-messages::-webkit-scrollbar-thumb {
            background: #6f42c1;
            border-radius: 10px;
        }
        
        #chatbot-messages::-webkit-scrollbar-thumb:hover {
            background: #5a32a3;
        }
    </style>
    
    <!-- Chatbot Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chatbotToggle = document.getElementById('chatbot-toggle');
            const chatbotWindow = document.getElementById('chatbot-window');
            const chatbotClose = document.getElementById('chatbot-close');
            const chatbotForm = document.getElementById('chatbot-form');
            const chatbotInput = document.getElementById('chatbot-input');
            const chatbotMessages = document.getElementById('chatbot-messages');
            
            // Toggle chatbot window
            chatbotToggle.addEventListener('click', function() {
                const isHidden = chatbotWindow.style.display === 'none';
                chatbotWindow.style.display = isHidden ? 'block' : 'none';
                if (isHidden) {
                    chatbotInput.focus();
                }
            });
            
            // Close chatbot window
            chatbotClose.addEventListener('click', function() {
                chatbotWindow.style.display = 'none';
            });
            
            // Handle form submission
            chatbotForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const message = chatbotInput.value.trim();
                if (!message) return;
                
                // Add user message to chat
                addMessage(message, 'user');
                chatbotInput.value = '';
                
                // Send message to backend
                sendMessage(message);
            });
            
            // Add message to chat
            function addMessage(text, type) {
                if (!text) {
                    console.error('addMessage called with empty text');
                    return;
                }
                
                try {
                    const messageDiv = document.createElement('div');
                    const isUser = type === 'user';
                    messageDiv.className = `d-flex align-items-start gap-2 mb-3 animate-message ${isUser ? 'flex-row-reverse' : ''}`;
                    
                    const now = new Date();
                    const timeStr = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
                    
                    if (isUser) {
                        messageDiv.innerHTML = `
                            <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 35px; height: 35px;">
                                <i class="fas fa-user text-white" style="font-size: 0.75rem;"></i>
                            </div>
                            <div class="bg-primary text-white rounded-3 p-3 shadow-sm" style="max-width: 80%; background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%) !important; border-right: 3px solid #5a32a3;">
                                <p class="mb-0 small" style="white-space: pre-wrap; line-height: 1.5;">${escapeHtml(text)}</p>
                                <small class="d-block mt-1 opacity-75" style="font-size: 0.7rem;">${timeStr}</small>
                            </div>
                        `;
                    } else {
                        const iconClass = type === 'error' ? 'fa-exclamation-circle text-danger' : 
                                        type === 'success' ? 'fa-check-circle text-success' : 
                                        'fa-robot';
                        const iconColor = type === 'error' ? '#dc3545' : type === 'success' ? '#198754' : '#6f42c1';
                        messageDiv.innerHTML = `
                            <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 35px; height: 35px; background: linear-gradient(135deg, ${iconColor} 0%, ${iconColor}dd 100%);">
                                <i class="fas ${iconClass === 'fa-robot' || iconClass.includes('fa-robot') ? 'fa-robot' : 'fa-info-circle'} text-white" style="font-size: 0.875rem;"></i>
                            </div>
                            <div class="bg-white rounded-3 p-3 shadow-sm" style="max-width: 80%; border-left: 3px solid ${iconColor};">
                                <p class="mb-0 small text-dark" style="white-space: pre-wrap; line-height: 1.5;">${formatMessage(text)}</p>
                                <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">${timeStr}</small>
                            </div>
                        `;
                    }
                    
                    chatbotMessages.appendChild(messageDiv);
                    chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
                } catch (error) {
                    console.error('Error adding message to chat:', error);
                    // Fallback: try to add a simple text message
                    const fallbackDiv = document.createElement('div');
                    fallbackDiv.className = 'd-flex align-items-start gap-2 mb-3';
                    fallbackDiv.innerHTML = `
                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 35px; height: 35px; background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);">
                            <i class="fas fa-robot text-white" style="font-size: 0.875rem;"></i>
                        </div>
                        <div class="bg-white rounded-3 p-3 shadow-sm" style="max-width: 80%;">
                            <p class="mb-0 small text-dark">${escapeHtml(String(text))}</p>
                        </div>
                    `;
                    chatbotMessages.appendChild(fallbackDiv);
                    chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
                }
            }
            
            // Send message to backend
            function sendMessage(message) {
                const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
                if (!csrfTokenMeta) {
                    console.error('CSRF token meta tag not found!');
                    addMessage('Error: CSRF token not found. Please refresh the page.', 'error');
                    return;
                }
                
                const csrfToken = csrfTokenMeta.getAttribute('content');
                if (!csrfToken) {
                    console.error('CSRF token is empty!');
                    addMessage('Error: CSRF token is empty. Please refresh the page.', 'error');
                    return;
                }
                
                // Show loading indicator
                const loadingDiv = document.createElement('div');
                loadingDiv.id = 'chatbot-loading';
                loadingDiv.className = 'd-flex align-items-start gap-2 mb-3';
                loadingDiv.innerHTML = `
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 35px; height: 35px; background: linear-gradient(135deg, #6f42c1 0%, #5a32a3 100%);">
                        <i class="fas fa-robot text-white" style="font-size: 0.875rem;"></i>
                    </div>
                    <div class="bg-white rounded-3 p-3 shadow-sm" style="max-width: 80%; border-left: 3px solid #6f42c1;">
                        <div class="d-flex align-items-center gap-2">
                            <div class="spinner-border spinner-border-sm text-primary" role="status" style="width: 1rem; height: 1rem; color: #6f42c1 !important;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <small class="text-muted">Thinking...</small>
                        </div>
                    </div>
                `;
                chatbotMessages.appendChild(loadingDiv);
                chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
                
                fetch('{{ route("chatbot.message") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ message: message })
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => {
                            throw new Error(err.message || `HTTP error! status: ${response.status}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    // Remove loading indicator
                    const loading = document.getElementById('chatbot-loading');
                    if (loading) loading.remove();
                    
                    console.log('Chatbot Response:', data); // Debug log
                    
                    if (data.success) {
                        addMessage(data.response, data.type || 'info');
                    } else {
                        addMessage(data.response || 'Sorry, I encountered an error. Please try again.', 'error');
                    }
                })
                .catch(error => {
                    // Remove loading indicator
                    const loading = document.getElementById('chatbot-loading');
                    if (loading) loading.remove();
                    
                    console.error('Chatbot Error:', error);
                    console.error('Error details:', error.message, error.stack);
                    addMessage('Sorry, I encountered an error. Please try again or check your connection.', 'error');
                });
            }
            
            // Escape HTML
            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
            
            // Format message (convert markdown-like formatting)
            function formatMessage(text) {
                // First escape HTML to prevent XSS
                text = escapeHtml(text);
                // Convert **text** to bold
                text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
                // Convert line breaks
                text = text.replace(/\n/g, '<br>');
                return text;
            }
        });
    </script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


