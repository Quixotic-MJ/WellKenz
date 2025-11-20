<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WellKenz Cakes & Pastries - ERP Login</title>

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
        /* Kept your existing styles */
        .body-pattern { background-color: #faf7f3; }
        .font-display { font-family: 'Playfair Display', serif; }
        .gradient-text {
            background: linear-gradient(135deg, #c48d3f 0%, #d4a961 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>
<body class="antialiased body-pattern font-sans">

    <div class="flex items-center justify-center min-h-screen p-4 sm:p-8">
        <div class="flex w-full max-w-6xl overflow-hidden shadow-xl border-2 border-border-soft bg-white">

            <div class="hidden md:block w-5/12 bg-chocolate relative overflow-hidden">
                <div class="absolute inset-0 bg-gradient-to-br from-chocolate via-chocolate to-chocolate-dark"></div>
                <div class="relative flex flex-col justify-between h-full p-12 text-white">
                    <div>
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="w-12 h-12 bg-caramel/20 flex items-center justify-center border border-caramel/30">
                                <i class="fas fa-birthday-cake text-caramel text-xl"></i>
                            </div>
                            <div>
                                <h1 class="font-display text-2xl font-bold">WellKenz</h1>
                                <p class="text-xs text-white/70 uppercase tracking-widest">Cakes & Pastries</p>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h2 class="font-display text-4xl font-bold leading-tight mb-4">
                            Bakery Management <br/><span class="gradient-text">System</span>
                        </h2>
                        <p class="text-sm text-white/80 leading-relaxed">
                            Authorized access only. Please log in with your provided credentials to access Inventory, Purchasing, or Production modules.
                        </p>
                    </div>
                </div>
            </div>

            <div class="w-full md:w-7/12 bg-white p-8 sm:p-12">
                <div class="mb-8">
                    <h2 class="font-display text-3xl font-bold text-text-dark mb-2">Sign In</h2>
                    <p class="text-text-muted">Access your dashboard</p>
                </div>

                @if(session('error'))
                    <div class="mb-4 p-3 bg-red-50 border-l-4 border-red-500 text-red-700 text-sm">
                        {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}" class="space-y-6" id="loginForm">
                    @csrf
                    
                    <div>
                        <label for="email" class="block text-xs font-bold text-text-dark uppercase tracking-wider mb-2">
                            Email Address
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-text-muted text-sm"></i>
                            </div>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required
                                class="block w-full pl-11 pr-4 py-3.5 bg-white border-2 border-border-soft
                                        text-text-dark focus:border-chocolate focus:outline-none transition-colors"
                                placeholder="name@bakery.com"
                                autocomplete="email" autofocus>
                        </div>
                        @if($errors->has('email'))
                            <p class="mt-1 text-sm text-red-600">{{ $errors->first('email') }}</p>
                        @endif
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-bold text-text-dark uppercase tracking-wider mb-2">
                            Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-text-muted text-sm"></i>
                            </div>
                            <input id="password" name="password" type="password" required
                                class="block w-full pl-11 pr-12 py-3.5 bg-white border-2 border-border-soft
                                        text-text-dark focus:border-chocolate focus:outline-none transition-colors"
                                placeholder="Enter your password">
                            <button type="button" class="absolute inset-y-0 right-0 pr-4 flex items-center" onclick="togglePassword()">
                                <i class="fas fa-eye text-text-muted hover:text-text-dark text-sm transition-colors" id="passwordToggle"></i>
                            </button>
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit" id="submitBtn"
                                class="w-full px-6 py-4 text-sm font-bold tracking-widest text-white uppercase
                                        bg-chocolate hover:bg-chocolate-dark transition-all duration-300">
                            Login
                        </button>
                    </div>
                </form>
            </div>
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
    </script>
</body>
</html>