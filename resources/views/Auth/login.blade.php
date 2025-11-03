<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WellKenz Cakes & Pastries - ERP System</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <script src="https://cdn.tailwindcss.com"></script>
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

        .geometric-bg {
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23c48d3f' fill-opacity='0.08' fill-rule='evenodd'%3E%3Cpath d='M0 0h30v30H0V0zm30 30h30v30H30V30z'/%3E%3C/g%3E%3C/svg%3E");
            background-size: 30px 30px;
        }

        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@400;500;600;700&display=swap');
        
        .font-display {
            font-family: 'Playfair Display', serif;
        }

        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(-5deg); }
            50% { transform: translateY(-10px) rotate(-5deg); }
        }

        .gradient-text {
            background: linear-gradient(135deg, #c48d3f 0%, #d4a961 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>
<body class="antialiased body-pattern font-sans">

    <div class="flex items-center justify-center min-h-screen p-4 sm:p-8">

        <div class="flex w-full max-w-6xl overflow-hidden shadow-[0_20px_80px_rgba(61,40,23,0.15)] border-2 border-border-soft bg-white">

            <!-- Left Panel -->
            <div class="hidden md:block w-5/12 bg-chocolate geometric-bg relative overflow-hidden">
                
                <div class="absolute inset-0 bg-gradient-to-br from-chocolate/95 via-chocolate/90 to-chocolate-dark/95"></div>
                
                <!-- Decorative Elements -->
                <div class="absolute top-20 -right-20 w-64 h-64 bg-caramel/10 rounded-full blur-3xl"></div>
                <div class="absolute bottom-20 -left-20 w-64 h-64 bg-caramel/10 rounded-full blur-3xl"></div>
                
                <div class="relative flex flex-col justify-between h-full p-12 text-white">
                    <div>
                        <div class="flex items-center space-x-3 mb-6">
                            <div class="w-12 h-12 bg-caramel/20 backdrop-blur-sm flex items-center justify-center border border-caramel/30">
                                <i class="fas fa-birthday-cake text-caramel text-xl animate-float"></i>
                            </div>
                            <div>
                                <h1 class="font-display text-2xl font-bold tracking-wide">WellKenz</h1>
                                <p class="text-xs text-white/70 uppercase tracking-widest">Cakes & Pastries</p>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <h2 class="font-display text-4xl font-bold leading-tight mb-4">
                            Where sweetness<br/>meets <span class="gradient-text">efficiency</span>
                        </h2>
                        <p class="text-sm text-white/80 leading-relaxed">
                            Streamline operations from recipe management to delivery tracking. 
                            Built exclusively for artisan bakeries that value precision and quality.
                        </p>

                     
                    </div>
                </div>
            </div>

            <!-- Right Panel -->
            <div class="w-full md:w-7/12 bg-white p-8 sm:p-12">
                
                <div class="mb-8">
                    <div class="inline-block px-3 py-1 bg-caramel/10 border border-caramel/20 mb-4">
                        <span class="text-xs font-bold text-caramel uppercase tracking-wider">Portal</span>
                    </div>
                    
                    <h2 class="font-display text-3xl font-bold text-text-dark mb-2">
                        Welcome Back
                    </h2>
                    <p class="text-text-muted">
                        Sign in to access your workspace and start managing operations.
                    </p>
                </div>

                <!-- Error Messages -->
                @if(session('error'))
                    <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
                        {{ session('error') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}" class="space-y-6">
                    @csrf
                    
                    <div>
                        <label for="username" class="block text-xs font-bold text-text-dark uppercase tracking-wider mb-2">
                            Username
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <i class="fas fa-user text-text-muted text-sm"></i>
                            </div>
                            <input id="username" name="username" type="text" value="{{ old('username') }}" required
                                class="block w-full pl-11 pr-4 py-3.5 bg-white border-2 border-border-soft
                                       placeholder-text-muted text-text-dark text-base
                                       focus:outline-none focus:border-chocolate transition-colors"
                                placeholder="Enter your username">
                        </div>
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
                                       placeholder-text-muted text-text-dark text-base
                                       focus:outline-none focus:border-chocolate transition-colors"
                                placeholder="Enter your password">
                            <button type="button" class="absolute inset-y-0 right-0 pr-4 flex items-center" onclick="togglePassword()">
                                <i class="fas fa-eye text-text-muted hover:text-text-dark text-sm transition-colors" id="passwordToggle"></i>
                            </button>
                        </div>
                    </div>


                    <div class="pt-4">
                        <button type="submit"
                                class="group relative w-full px-6 py-4 text-sm font-bold tracking-widest text-white uppercase
                                       bg-chocolate border-2 border-chocolate overflow-hidden
                                       hover:bg-chocolate-dark hover:border-chocolate-dark transition-all duration-300
                                       focus:outline-none focus:ring-2 focus:ring-chocolate focus:ring-offset-2">
                            <span class="relative z-10 flex items-center justify-center">
                                Sign In to Dashboard
                                <i class="fas fa-arrow-right ml-3 group-hover:translate-x-1 transition-transform"></i>
                            </span>
                        </button>
                    </div>
                </form>

                <div class="mt-8 pt-8 border-t border-border-soft">
                    <p class="text-sm text-text-muted text-center">
                        Need help accessing your account?
                    </p>
                    <p class="text-center mt-2">
                        <a href="#" class="inline-flex items-center text-sm font-bold text-chocolate hover:text-chocolate-dark transition-colors">
                            <i class="fas fa-headset mr-2"></i>
                            Contact IT Support
                        </a>
                    </p>
                </div>

            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const passwordToggle = document.getElementById('passwordToggle');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordToggle.classList.remove('fa-eye');
                passwordToggle.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                passwordToggle.classList.remove('fa-eye-slash');
                passwordToggle.classList.add('fa-eye');
            }
        }
    </script>

</body>
</html>