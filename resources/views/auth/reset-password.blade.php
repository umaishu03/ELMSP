<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - ELMSP</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 25%, #f093fb 50%, #f5576c 75%, #4facfe 100%);
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl p-8 w-full max-w-md shadow-2xl">
        <!-- Logo Section -->
        <div class="text-center mb-8">
            <div class="w-24 h-24 mx-auto mb-4 bg-white rounded-full shadow-lg flex items-center justify-center p-2">
                <img src="{{ asset('images/elmsp-logo.png') }}" alt="ELMSP Logo" class="w-full h-full object-contain rounded-full">
            </div>
            <h1 class="text-2xl font-semibold text-gray-700">Reset Password</h1>
            <p class="text-sm text-gray-600 mt-2">Enter your new password below.</p>
        </div>

        <!-- Reset Password Form -->
        <form method="POST" action="{{ route('password.update') }}" class="space-y-6">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email ?? old('email') }}">
            
            <!-- Display validation errors -->
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl">
                    @foreach ($errors->all() as $error)
                        <p class="text-sm">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <!-- Email Field (read-only) -->
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-envelope text-gray-400"></i>
                </div>
                <input 
                    type="email" 
                    id="email"
                    value="{{ $email ?? old('email') }}"
                    class="w-full pl-10 pr-4 py-3 bg-gray-100 border-0 rounded-xl text-gray-600"
                    readonly
                >
            </div>

            <!-- Password Field -->
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-lock text-gray-400"></i>
                </div>
                <input 
                    type="password" 
                    id="password"
                    name="password" 
                    placeholder="Enter new password"
                    class="w-full pl-10 pr-4 py-3 bg-gray-100 border-0 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-colors @error('password') ring-2 ring-red-500 @enderror"
                    required
                    autocomplete="new-password"
                    autofocus
                >
                @error('password')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirm Password Field -->
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-lock text-gray-400"></i>
                </div>
                <input 
                    type="password" 
                    id="password_confirmation"
                    name="password_confirmation" 
                    placeholder="Confirm new password"
                    class="w-full pl-10 pr-4 py-3 bg-gray-100 border-0 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-colors @error('password_confirmation') ring-2 ring-red-500 @enderror"
                    required
                    autocomplete="new-password"
                >
                @error('password_confirmation')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <button 
                type="submit"
                class="w-full bg-gray-800 hover:bg-gray-900 text-white font-semibold py-3 px-4 rounded-xl transition-colors duration-200"
            >
                <i class="fas fa-key mr-2"></i>Reset Password
            </button>

            <!-- Back to Login Link -->
            <div class="text-center">
                <a href="{{ route('login') }}" class="text-blue-600 hover:text-blue-800 text-sm transition-colors">
                    <i class="fas fa-arrow-left mr-1"></i>Back to Login
                </a>
            </div>
        </form>
    </div>
</body>
</html>

