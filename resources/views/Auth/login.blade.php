<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'WellKenz') }} - ERP Login</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'cream-bg': '#faf7f3',
                        'chocolate': '#3d2817',
                        'chocolate-dark': '#2a1a0f',
                        'caramel': '#c48d3f',
                        'text-dark': '#1a1410',
                        'text-muted': '#8b7355',
                        'border-soft': '#e8dfd4',
                    }
                }
            }
        }
    </script>
    <style>
        .body-pattern { 
            background-color: #faf7f3;
            background-image: url("data:image/svg+xml,%3Csvg width='20' height='20' viewBox='0 0 20 20' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23e8dfd4' fill-opacity='0.3' fill-rule='evenodd'%3E%3Ccircle cx='3' cy='3' r='1'/%3E%3Ccircle cx='13' cy='13' r='1'/%3E%3C/g%3E%3C/svg%3E");
        }
        .font-display { font-family: 'Playfair Display', serif; }
        .gradient-text {
            background: linear-gradient(135deg, #c48d3f 0%, #d4a961 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        /* Loading Spinner */
        .btn-loading {
            color: transparent !important;
            pointer-events: none;
            position: relative;
        }
        .btn-loading::after {
            content: '';
            position: absolute;
            width: 1.25rem;
            height: 1.25rem;
            top: 50%;
            left: 50%;
            margin-top: -0.625rem;
            margin-left: -0.625rem;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body class="antialiased body-pattern font-sans text-text-dark">

    <div class="flex items-center justify-center min-h-screen p-4 sm:p-8">
        <div class="flex w-full max-w-6xl overflow-hidden shadow-[0_20px_50px_rgba(61,40,23,0.15)] border border-border-soft bg-white rounded-2xl">

            <div class="hidden md:block w-5/12 bg-chocolate relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-chocolate via-[#4a3221] to-chocolate-dark opacity-90"></div>
                
                <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'%23ffffff\' fill-rule=\'evenodd\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/svg%3E');"></div>

                <div class="relative flex flex-col justify-between h-full p-12 text-white z-10">
                    <div>
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="w-12 h-12 bg-caramel/20 backdrop-blur-sm flex items-center justify-center border border-caramel/30 rounded-lg">
                                <i class="fas fa-birthday-cake text-caramel text-xl"></i>
                            </div>
                            <div>
                                <h1 class="font-display text-2xl font-bold tracking-wide">{{ config('app.name', 'WellKenz') }}</h1>
                                <p class="text-[10px] text-white/70 uppercase tracking-[0.2em]">Cakes & Pastries</p>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h2 class="font-display text-4xl font-bold leading-tight mb-4">
                            Cakeshop Supply Management <br/><span class="gradient-text">System</span>
                        </h2>
                        <p class="text-sm text-white/80 leading-relaxed border-l-2 border-caramel pl-4">
                            Secure ERP access for Inventory, Procurement, and Production management.
                        </p>
                    </div>
                </div>
            </div>

            <div class="w-full md:w-7/12 bg-white p-8 sm:p-12 lg:p-16">
                <div class="mb-10">
                    <h2 class="font-display text-3xl font-bold text-chocolate mb-2">Welcome Back</h2>
                    <p class="text-text-muted text-sm">Please sign in to your WellKenz ERP account to continue.</p>
                </div>

                {{-- Global Errors (e.g. "Account Deactivated" from Controller) --}}
                @if(session('error'))
                    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 text-sm flex items-center animate-pulse">
                        <i class="fas fa-exclamation-circle mr-3 text-lg"></i>
                        {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}" class="space-y-6" id="loginForm">
                    @csrf
                    
                    {{-- Email Input --}}
                    <div>
                        <label for="email" class="block text-xs font-bold text-text-dark uppercase tracking-wider mb-2">
                            Email Address
                        </label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-colors group-focus-within:text-chocolate">
                                <i class="fas fa-envelope text-text-muted text-sm"></i>
                            </div>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required
                                class="block w-full pl-11 pr-4 py-3.5 bg-cream-bg/30 border border-border-soft rounded-lg
                                       text-text-dark placeholder-text-muted/70 text-sm transition-all duration-200
                                       focus:border-chocolate focus:ring-1 focus:ring-chocolate focus:bg-white focus:outline-none
                                       {{ $errors->has('email') ? 'border-red-400 bg-red-50' : '' }}"
                                placeholder="name@wellkenz.com"
                                autocomplete="email" autofocus>
                        </div>
                        @error('email')
                            <p class="mt-1 text-xs text-red-600 font-medium flex items-center">
                                <i class="fas fa-times-circle mr-1"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Password Input --}}
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label for="password" class="block text-xs font-bold text-text-dark uppercase tracking-wider">
                                Password
                            </label>
                        </div>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-colors group-focus-within:text-chocolate">
                                <i class="fas fa-lock text-text-muted text-sm"></i>
                            </div>
                            <input id="password" name="password" type="password" required
                                class="block w-full pl-11 pr-12 py-3.5 bg-cream-bg/30 border border-border-soft rounded-lg
                                       text-text-dark placeholder-text-muted/70 text-sm transition-all duration-200
                                       focus:border-chocolate focus:ring-1 focus:ring-chocolate focus:bg-white focus:outline-none
                                       {{ $errors->has('password') ? 'border-red-400 bg-red-50' : '' }}"
                                placeholder="Enter your password">
                            <button type="button" class="absolute inset-y-0 right-0 pr-4 flex items-center text-text-muted hover:text-chocolate transition-colors cursor-pointer focus:outline-none" onclick="togglePassword()">
                                <i class="fas fa-eye text-sm" id="passwordToggle"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1 text-xs text-red-600 font-medium flex items-center">
                                <i class="fas fa-times-circle mr-1"></i> {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Remember Me --}}
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox" 
                               class="h-4 w-4 text-chocolate focus:ring-chocolate border-gray-300 rounded cursor-pointer">
                        <label for="remember" class="ml-2 block text-sm text-gray-600 cursor-pointer select-none">
                            Remember me
                        </label>
                    </div>

                    {{-- Submit Button --}}
                    <div class="pt-2">
                        <button type="submit" id="submitBtn"
                                class="w-full flex justify-center items-center px-6 py-4 text-sm font-bold tracking-widest text-white uppercase
                                       bg-chocolate rounded-lg shadow-lg hover:bg-chocolate-dark hover:shadow-xl 
                                       focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-chocolate 
                                       transition-all duration-300 transform hover:-translate-y-0.5">
                            <span>Sign In</span>
                            <i class="fas fa-arrow-right ml-2 opacity-80 group-hover:translate-x-1 transition-transform"></i>
                        </button>
                    </div>
                </form>

                {{-- Footer Links --}}
                <div class="mt-8 pt-6 border-t border-border-soft text-center">
                    <p class="text-xs text-text-muted">
                        Having trouble logging in? <br/>
                        <a href="mailto:support@wellkenz.com" class="font-bold text-chocolate hover:underline mt-1 inline-block">
                            Contact IT Support
                        </a>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="fixed bottom-4 text-center w-full text-[10px] text-gray-400">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>

    <script>
        function togglePassword() {
            const pwd = document.getElementById('password');
            const icon = document.getElementById('passwordToggle');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                pwd.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            btn.classList.add('btn-loading');
            btn.innerHTML = ''; // Clear text to show spinner only
        });
    </script>
</body>
</html>