@extends('layouts.' . (Auth::user()->isAdmin() ? 'admin' : 'staff'))

@php
    $isAdmin = Auth::user()->isAdmin();
    $themeColors = $isAdmin
        ? [ // Admin colors
            'primary' => 'blue-600',
            'secondary' => 'indigo-600',
            'fullName' => 'blue-600',
            'email' => 'purple-600',
            'employee' => 'green-600',
            'phone' => 'cyan-600',
            'address' => 'orange-600',
        ]
        : [ // Staff colors
            'primary' => 'purple-600',
            'secondary' => 'purple-700',
            'fullName' => 'purple-600',
            'email' => 'purple-600',
            'employee' => 'green-600',
            'phone' => 'cyan-600',
            'address' => 'orange-600',
        ];
@endphp

@section('title', 'Profile')

@section('content')
<!-- Breadcrumbs -->
<div class="mb-6">
    {!! \App\Helpers\BreadcrumbHelper::render() !!}
</div>
<div class="mb-8">
    <h1 class="text-4xl font-bold text-gray-800 mb-2">Profile</h1>
</div>
<!-- Success/Error Messages -->
@if(session('success'))
    <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-lg shadow-sm">
        <div class="flex items-center gap-3">
            <i class="fas fa-check-circle text-green-500 text-xl"></i>
            <p class="text-green-800 font-medium">{{ session('success') }}</p>
        </div>
    </div>
@endif

@if($errors->any())
    <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-lg shadow-sm">
        <div class="flex items-start gap-3">
            <i class="fas fa-exclamation-circle text-red-500 text-xl mt-0.5"></i>
            <div>
                <p class="text-red-800 font-semibold mb-2">Please fix the following errors:</p>
                <ul class="list-disc list-inside space-y-1 text-red-700">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endif

<!-- Profile Content -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Card - Profile Information Display -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-{{ $themeColors['primary'] }} to-{{ $themeColors['secondary'] }} px-6 py-8">
                <div class="text-center">
                    <div class="w-28 h-28 mx-auto bg-white rounded-full flex items-center justify-center mb-4 shadow-lg">
                        <i class="fas fa-user text-{{ $themeColors['primary'] }} text-4xl"></i>
                    </div>
                    <h2 class="text-xl font-bold text-white">{{ Auth::user()->name }}</h2>
                    <div class="inline-flex items-center gap-2 mt-2 px-4 py-1.5 bg-white/20 backdrop-blur-sm rounded-full">
                        <i class="fas fa-shield-alt text-white text-xs"></i>
                        <span class="text-sm font-medium text-white">{{ $isAdmin ? 'Administrator' : 'Staff Member' }}</span>
                    </div>
                </div>
            </div>

            <!-- Profile Details -->
            <div class="p-6 space-y-4">
                <div class="space-y-3">
                    <!-- Full Name -->
                    <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                        <div class="w-10 h-10 bg-{{ $themeColors['fullName'] }}-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-user text-{{ $themeColors['fullName'] }}"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-medium text-gray-500 mb-1">Full Name</p>
                            <p class="text-sm font-semibold text-gray-900 break-words">{{ Auth::user()->name }}</p>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                        <div class="w-10 h-10 bg-{{ $themeColors['email'] }}-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-envelope text-{{ $themeColors['email'] }}"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-medium text-gray-500 mb-1">Email Address</p>
                            <p class="text-sm font-semibold text-gray-900 break-all">{{ Auth::user()->email }}</p>
                        </div>
                    </div>

                    @if(Auth::user()->staff && Auth::user()->staff->department)
                    <!-- Department -->
                    <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-building text-blue-600"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-medium text-gray-500 mb-1">Department</p>
                            <p class="text-sm font-semibold text-gray-900">{{ ucfirst(Auth::user()->staff->department) }}</p>
                        </div>
                    </div>
                    @endif

                    @if(Auth::user()->employee_id)
                    <!-- Employee ID -->
                    <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                        <div class="w-10 h-10 bg-{{ $themeColors['employee'] }}-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-id-badge text-{{ $themeColors['employee'] }}"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-medium text-gray-500 mb-1">Employee ID</p>
                            <p class="text-sm font-semibold text-gray-900">{{ Auth::user()->employee_id }}</p>
                        </div>
                    </div>
                    @endif

                    @if(Auth::user()->phone)
                    <!-- Phone -->
                    <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                        <div class="w-10 h-10 bg-{{ $themeColors['phone'] }}-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-phone text-{{ $themeColors['phone'] }}"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-medium text-gray-500 mb-1">Phone Number</p>
                            <p class="text-sm font-semibold text-gray-900">{{ Auth::user()->phone }}</p>
                        </div>
                    </div>
                    @endif

                    @if(Auth::user()->address)
                    <!-- Address -->
                    <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                        <div class="w-10 h-10 bg-{{ $themeColors['address'] }}-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-map-marker-alt text-{{ $themeColors['address'] }}"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-medium text-gray-500 mb-1">Address</p>
                            <p class="text-sm font-semibold text-gray-900">{{ Auth::user()->address }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Right Card - Profile Edit Form -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Header -->
            <div class="px-8 py-6 border-b border-gray-100">
                <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-edit text-{{ $themeColors['primary'] }}"></i>
                    Edit Profile
                </h3>
                <p class="text-sm text-gray-500 mt-1">Update your personal information and password</p>
            </div>

            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PUT')
                
                <div class="p-8 space-y-6">
                    <!-- Personal Information Section -->
                    <div>
                        <h4 class="text-md font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <div class="w-1 h-5 bg-{{ $themeColors['primary'] }} rounded"></div>
                            Personal Information
                        </h4>
                        
                        <div class="space-y-4">
                            <!-- Name Field -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Full Name <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i class="fas fa-user text-gray-400"></i>
                                    </div>
                                    <input type="text" 
                                           name="name" 
                                           value="{{ Auth::user()->name }}" 
                                           class="w-full pl-11 pr-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-{{ $themeColors['primary'] }} focus:border-transparent transition-all hover:border-gray-400"
                                           placeholder="Enter your full name">
                                </div>
                            </div>

                            <!-- Email Field -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Email Address <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i class="fas fa-envelope text-gray-400"></i>
                                    </div>
                                    <input type="email" 
                                           name="email" 
                                           value="{{ Auth::user()->email }}" 
                                           class="w-full pl-11 pr-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-{{ $themeColors['primary'] }} focus:border-transparent transition-all hover:border-gray-400"
                                           placeholder="Enter your email address">
                                </div>
                            </div>

                            <!-- Phone Field -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Phone Number
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i class="fas fa-phone text-gray-400"></i>
                                    </div>
                                    <input type="tel" 
                                           name="phone" 
                                           value="{{ Auth::user()->phone ?? '' }}" 
                                           class="w-full pl-11 pr-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-{{ $themeColors['primary'] }} focus:border-transparent transition-all hover:border-gray-400"
                                           placeholder="Enter your phone number">
                                </div>
                            </div>

                            <!-- Address Field -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Address
                                </label>
                                <div class="relative">
                                    <div class="absolute top-4 left-4 pointer-events-none">
                                        <i class="fas fa-map-marker-alt text-gray-400"></i>
                                    </div>
                                    <textarea name="address" 
                                              rows="3" 
                                              class="w-full pl-11 pr-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-{{ $themeColors['primary'] }} focus:border-transparent resize-none transition-all hover:border-gray-400"
                                              placeholder="Enter your address">{{ Auth::user()->address ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Password Change Section -->
                    <div class="pt-6 border-t border-gray-200">
                        <h4 class="text-md font-semibold text-gray-800 mb-4 flex items-center gap-2">
                            <div class="w-1 h-5 bg-{{ $themeColors['primary'] }} rounded"></div>
                            Change Password
                        </h4>
                        <p class="text-sm text-gray-500 mb-4">Leave blank if you don't want to change your password</p>
                        
                        <div class="space-y-4">
                            <!-- Current Password Field -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Current Password
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i class="fas fa-lock text-gray-400"></i>
                                    </div>
                                    <input type="password" 
                                           name="current_password" 
                                           class="w-full pl-11 pr-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-{{ $themeColors['primary'] }} focus:border-transparent transition-all hover:border-gray-400"
                                           placeholder="Enter current password">
                                </div>
                            </div>

                            <!-- New Password Field -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    New Password
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i class="fas fa-key text-gray-400"></i>
                                    </div>
                                    <input type="password" 
                                           name="password" 
                                           class="w-full pl-11 pr-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-{{ $themeColors['primary'] }} focus:border-transparent transition-all hover:border-gray-400"
                                           placeholder="Enter new password">
                                </div>
                            </div>

                            <!-- Confirm Password Field -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-2">
                                    Confirm New Password
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i class="fas fa-shield-alt text-gray-400"></i>
                                    </div>
                                    <input type="password" 
                                           name="password_confirmation" 
                                           class="w-full pl-11 pr-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-{{ $themeColors['primary'] }} focus:border-transparent transition-all hover:border-gray-400"
                                           placeholder="Confirm new password">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="px-8 py-6 bg-gray-50 border-t border-gray-100 flex gap-4">
                    <button type="button" 
                            onclick="window.history.back()"
                            class="flex-1 bg-white hover:bg-gray-50 text-gray-700 font-semibold py-3 rounded-xl border-2 border-gray-300 transition-all duration-200">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="flex-1 bg-gradient-to-r from-{{ $themeColors['primary'] }} to-{{ $themeColors['secondary'] }} 
                                   hover:from-{{ $themeColors['primary'] }}-700 hover:to-{{ $themeColors['secondary'] }}-700
                                   text-white font-semibold py-3 rounded-xl shadow-md hover:shadow-lg transition-all duration-200 flex items-center justify-center gap-2">
                        <i class="fas fa-save"></i>
                        <span>Save Changes</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
