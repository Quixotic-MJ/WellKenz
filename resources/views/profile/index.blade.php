@extends(auth()->user()->role . '.layout.app')

@section('title', 'My Profile')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">My Profile</li>
        </ol>
    </nav>
@endsection

@section('content')
<div class="p-6">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-chocolate font-display mb-2">My Profile</h1>
            <p class="text-gray-600">Manage your account information and security settings</p>
        </div>
        <div class="flex gap-3">
            @php
                $dashboardRoute = auth()->user()->role . '.dashboard';
            @endphp
            <a href="{{ route($dashboardRoute) }}" class="px-4 py-2 bg-white border border-border-soft text-chocolate rounded-lg hover:bg-cream-bg transition-all">
                <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>
            {{ session('error') }}
        </div>
    @endif

    @if(session('info'))
        <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-lg mb-6 flex items-center">
            <i class="fas fa-info-circle mr-2"></i>
            {{ session('info') }}
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- Profile Information Card -->
        <div class="xl:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-border-soft">
                <div class="p-6 border-b border-border-soft bg-gradient-to-r from-cream-bg to-white">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gradient-to-br from-caramel to-chocolate rounded-xl flex items-center justify-center mr-4 shadow-sm">
                            <i class="fas fa-user text-white"></i>
                        </div>
                        <div>
                            <h5 class="text-lg font-bold text-chocolate font-display mb-1">Personal Information</h5>
                            <p class="text-sm text-gray-600">Update your personal details and contact information</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        @method('PATCH')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Basic Information -->
                            <div>
                                <label for="name" class="block text-sm font-bold text-chocolate mb-2">
                                    <i class="fas fa-user mr-1"></i>Full Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       class="w-full px-3 py-2 border border-border-soft rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel focus:border-caramel @error('name') border-red-500 @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name', $user->name) }}" 
                                       required>
                                @error('name')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-bold text-chocolate mb-2">
                                    <i class="fas fa-envelope mr-1"></i>Email Address <span class="text-red-500">*</span>
                                </label>
                                <input type="email" 
                                       class="w-full px-3 py-2 border border-border-soft rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel focus:border-caramel @error('email') border-red-500 @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email', $user->email) }}" 
                                       required>
                                @error('email')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Contact Information -->
                            <div>
                                <label for="phone" class="block text-sm font-bold text-chocolate mb-2">
                                    <i class="fas fa-phone mr-1"></i>Phone Number
                                </label>
                                <input type="text" 
                                       class="w-full px-3 py-2 border border-border-soft rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel focus:border-caramel @error('phone') border-red-500 @enderror" 
                                       id="phone" 
                                       name="phone" 
                                       value="{{ old('phone', $user->profile->phone ?? '') }}">
                                @error('phone')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="date_of_birth" class="block text-sm font-bold text-chocolate mb-2">
                                    <i class="fas fa-calendar mr-1"></i>Date of Birth
                                </label>
                                <input type="date" 
                                       class="w-full px-3 py-2 border border-border-soft rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel focus:border-caramel @error('date_of_birth') border-red-500 @enderror" 
                                       id="date_of_birth" 
                                       name="date_of_birth" 
                                       value="{{ old('date_of_birth', $user->profile->date_of_birth ? $user->profile->date_of_birth->format('Y-m-d') : '') }}">
                                @error('date_of_birth')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Address -->
                            <div class="md:col-span-2">
                                <label for="address" class="block text-sm font-bold text-chocolate mb-2">
                                    <i class="fas fa-map-marker-alt mr-1"></i>Address
                                </label>
                                <textarea class="w-full px-3 py-2 border border-border-soft rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel focus:border-caramel @error('address') border-red-500 @enderror" 
                                          id="address" 
                                          name="address" 
                                          rows="3">{{ old('address', $user->profile->address ?? '') }}</textarea>
                                @error('address')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Work Information -->
                            <div>
                                <label for="department" class="block text-sm font-bold text-chocolate mb-2">
                                    <i class="fas fa-building mr-1"></i>Department
                                </label>
                                <input type="text" 
                                       class="w-full px-3 py-2 border border-border-soft rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel focus:border-caramel @error('department') border-red-500 @enderror" 
                                       id="department" 
                                       name="department" 
                                       value="{{ old('department', $user->profile->department ?? '') }}">
                                @error('department')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="position" class="block text-sm font-bold text-chocolate mb-2">
                                    <i class="fas fa-briefcase mr-1"></i>Position
                                </label>
                                <input type="text" 
                                       class="w-full px-3 py-2 border border-border-soft rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel focus:border-caramel @error('position') border-red-500 @enderror" 
                                       id="position" 
                                       name="position" 
                                       value="{{ old('position', $user->profile->position ?? '') }}">
                                @error('position')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Emergency Contact -->
                            <div>
                                <label for="emergency_contact_name" class="block text-sm font-bold text-chocolate mb-2">
                                    <i class="fas fa-user-friends mr-1"></i>Emergency Contact Name
                                </label>
                                <input type="text" 
                                       class="w-full px-3 py-2 border border-border-soft rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel focus:border-caramel @error('emergency_contact_name') border-red-500 @enderror" 
                                       id="emergency_contact_name" 
                                       name="emergency_contact_name" 
                                       value="{{ old('emergency_contact_name', $user->profile->emergency_contact_name ?? '') }}">
                                @error('emergency_contact_name')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="emergency_contact_phone" class="block text-sm font-bold text-chocolate mb-2">
                                    <i class="fas fa-phone-alt mr-1"></i>Emergency Contact Phone
                                </label>
                                <input type="text" 
                                       class="w-full px-3 py-2 border border-border-soft rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel focus:border-caramel @error('emergency_contact_phone') border-red-500 @enderror" 
                                       id="emergency_contact_phone" 
                                       name="emergency_contact_phone" 
                                       value="{{ old('emergency_contact_phone', $user->profile->emergency_contact_phone ?? '') }}">
                                @error('emergency_contact_phone')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Password Change Section -->
                        <div class="border-t border-border-soft mt-6 pt-6">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h6 class="text-lg font-bold text-chocolate font-display flex items-center">
                                        <i class="fas fa-lock mr-2"></i>Change Password
                                    </h6>
                                    <p class="text-sm text-gray-600 mt-1">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Password change is optional. Leave fields empty or partially filled to keep your current password.
                                    </p>
                                </div>
                                <div class="text-xs text-gray-500 bg-gray-50 px-3 py-2 rounded-lg">
                                    <i class="fas fa-shield-alt mr-1"></i>
                                    Min. 8 characters
                                </div>
                            </div>
                            
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                                <div class="flex">
                                    <i class="fas fa-lightbulb text-blue-500 mt-0.5 mr-3"></i>
                                    <div class="text-sm text-blue-700">
                                        <strong>Tip:</strong> If you want to change your password, you MUST fill in ALL three password fields. 
                                        If you leave them empty or only fill some fields, your current password will remain unchanged and the form will save successfully.
                                    </div>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="current_password" class="block text-sm font-bold text-chocolate mb-2">
                                        <i class="fas fa-key mr-1"></i>Current Password
                                    </label>
                                    <div class="relative">
                                        <input type="password" 
                                               class="w-full px-3 py-2 pr-10 border border-border-soft rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel focus:border-caramel @error('current_password') border-red-500 @enderror" 
                                               id="current_password" 
                                               name="current_password"
                                               placeholder="Enter current password">
                                        <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-chocolate" onclick="togglePassword('current_password')">
                                            <i class="fas fa-eye" id="current_password_icon"></i>
                                        </button>
                                    </div>
                                    @error('current_password')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="new_password" class="block text-sm font-bold text-chocolate mb-2">
                                        <i class="fas fa-lock mr-1"></i>New Password
                                    </label>
                                    <div class="relative">
                                        <input type="password" 
                                               class="w-full px-3 py-2 pr-10 border border-border-soft rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel focus:border-caramel @error('new_password') border-red-500 @enderror" 
                                               id="new_password" 
                                               name="new_password"
                                               placeholder="Enter new password">
                                        <button type="button" class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-chocolate" onclick="togglePassword('new_password')">
                                            <i class="fas fa-eye" id="new_password_icon"></i>
                                        </button>
                                    </div>
                                    @error('new_password')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="new_password_confirmation" class="block text-sm font-bold text-chocolate mb-2">
                                        <i class="fas fa-check-circle mr-1"></i>Confirm New Password
                                    </label>
                                    <input type="password" 
                                           class="w-full px-3 py-2 border border-border-soft rounded-lg focus:outline-none focus:ring-2 focus:ring-caramel focus:border-caramel @error('new_password_confirmation') border-red-500 @enderror" 
                                           id="new_password_confirmation" 
                                           name="new_password_confirmation"
                                           placeholder="Confirm new password">
                                    @error('new_password_confirmation')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-border-soft mt-6 pt-6 flex justify-end">
                            <button type="submit" class="px-6 py-2 bg-chocolate text-white rounded-lg hover:bg-chocolate-dark transition-all">
                                <i class="fas fa-save mr-2"></i>Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Profile Summary & Password Change -->
        <div class="space-y-6">
            <!-- Profile Summary Card -->
            <div class="bg-white rounded-xl shadow-sm border border-border-soft">
                <div class="p-6 border-b border-border-soft bg-gradient-to-r from-cream-bg to-white">
                    <h5 class="text-lg font-bold text-chocolate font-display flex items-center">
                        <i class="fas fa-id-card mr-2"></i>Profile Summary
                    </h5>
                </div>
                <div class="p-6">
                    <div class="text-center mb-6">
                        <div class="w-20 h-20 bg-gradient-to-br from-caramel to-chocolate rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                            <span class="text-white text-2xl font-bold">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                        </div>
                        <h5 class="text-lg font-bold text-chocolate mb-2">{{ $user->name }}</h5>
                        <p class="text-gray-600 text-sm mb-3">{{ $user->email }}</p>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                            {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : 
                               ($user->role === 'supervisor' ? 'bg-blue-100 text-blue-800' : 
                                ($user->role === 'purchasing' ? 'bg-green-100 text-green-800' : 
                                 ($user->role === 'inventory' ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-800'))) }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 text-center">
                        <div class="p-3 bg-cream-bg rounded-lg">
                            <div class="font-bold text-chocolate">{{ $user->profile ? ($user->profile->hire_date ? $user->profile->hire_date->diffForHumans() : 'N/A') : 'N/A' }}</div>
                            <div class="text-sm text-gray-600">Time with Company</div>
                        </div>
                        <div class="p-3 bg-cream-bg rounded-lg">
                            <div class="font-bold text-chocolate">{{ $user->is_active ? 'Active' : 'Inactive' }}</div>
                            <div class="text-sm text-gray-600">Account Status</div>
                        </div>
                    </div>

                    @if($user->profile && $user->profile->employee_id)
                        <div class="border-t border-border-soft mt-4 pt-4 text-center">
                            <div class="text-sm text-gray-600">Employee ID: <strong class="text-chocolate">{{ $user->profile->employee_id }}</strong></div>
                        </div>
                    @endif
                </div>
            </div>


        </div>
    </div>
</div>

<!-- Password Toggle Script -->
<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '_icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>
@endsection