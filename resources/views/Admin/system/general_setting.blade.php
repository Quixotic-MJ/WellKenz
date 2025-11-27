@extends('Admin.layout.app')

@section('content')
<div class="space-y-8 font-sans text-gray-600">
    
    <div id="alert-container" class="fixed top-5 right-5 z-50 max-w-sm w-full"></div>

    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <h1 class="font-display text-3xl font-bold text-chocolate mb-2">General Settings</h1>
            <p class="text-sm text-gray-500">Configure core company details, branding, and system notifications.</p>
        </div>
        <div>
            <button type="button" id="save-settings-btn" 
                class="inline-flex items-center justify-center px-6 py-3 bg-chocolate text-white text-sm font-bold rounded-lg hover:bg-chocolate-dark transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                <i class="fas fa-save mr-2"></i> Save Changes
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        {{-- LEFT COLUMN: NAVIGATION / ANCHORS --}}
        <div class="hidden lg:block lg:col-span-1 space-y-6">
            <div class="bg-white border border-border-soft rounded-xl shadow-sm p-4 sticky top-6">
                <nav class="space-y-2">
                    <a href="#company-profile" class="flex items-center px-4 py-3 text-sm font-bold text-white bg-chocolate rounded-lg shadow-sm transition-all">
                        <i class="fas fa-building w-6 text-center mr-3"></i> Company Profile
                    </a>
                    <a href="#notifications" class="flex items-center px-4 py-3 text-sm font-medium text-gray-600 hover:bg-cream-bg hover:text-caramel rounded-lg transition-colors group">
                        <i class="fas fa-bell w-6 text-center mr-3 text-gray-400 group-hover:text-caramel"></i> Notifications
                    </a>
                    <a href="#finance" class="flex items-center px-4 py-3 text-sm font-medium text-gray-600 hover:bg-cream-bg hover:text-caramel rounded-lg transition-colors group">
                        <i class="fas fa-coins w-6 text-center mr-3 text-gray-400 group-hover:text-caramel"></i> Finance & Tax
                    </a>
                    <a href="#system" class="flex items-center px-4 py-3 text-sm font-medium text-gray-600 hover:bg-cream-bg hover:text-caramel rounded-lg transition-colors group">
                        <i class="fas fa-server w-6 text-center mr-3 text-gray-400 group-hover:text-caramel"></i> System Maintenance
                    </a>
                </nav>

                <div class="mt-6 bg-cream-bg border border-border-soft rounded-lg p-5">
                    <h4 class="text-xs font-bold text-caramel uppercase tracking-widest mb-3">System Info</h4>
                    <div class="text-xs text-gray-600 space-y-2 font-medium">
                        <div class="flex justify-between border-b border-border-soft pb-1">
                            <span>Version</span> 
                            <span class="font-mono text-chocolate">{{ $systemInfo['version'] ?? 'v2.4.0' }}</span>
                        </div>
                        <div class="flex justify-between border-b border-border-soft pb-1">
                            <span>Last Backup</span> 
                            <span class="text-chocolate">{{ $systemInfo['last_backup'] ?? 'Never' }}</span>
                        </div>
                        <div class="flex justify-between pt-1">
                            <span>Timezone</span> 
                            <span class="text-chocolate">{{ $systemInfo['timezone'] ?? 'Asia/Manila' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: FORMS --}}
        <div class="lg:col-span-2 space-y-8">

            {{-- SECTION 1: COMPANY PROFILE --}}
            <div id="company-profile" class="bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden scroll-mt-24">
                <div class="px-6 py-5 border-b border-border-soft bg-cream-bg">
                    <h3 class="font-display text-lg font-bold text-chocolate">Company Profile</h3>
                    <p class="mt-1 text-xs text-gray-500">This information will appear on Purchase Orders and Reports.</p>
                </div>
                <form id="company-profile-form" class="p-6 space-y-6">
                    
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6 pb-6 border-b border-gray-100">
                        <div class="relative group">
                            <div class="h-24 w-24 rounded-xl overflow-hidden bg-white border-2 border-dashed border-gray-300 flex items-center justify-center shadow-sm group-hover:border-caramel transition-colors">
                                @if(!empty($settings['company_logo']))
                                    <img src="{{ $settings['company_logo'] }}" id="logo-preview" alt="Company Logo" class="h-full w-full object-cover">
                                @else
                                    <i class="fas fa-birthday-cake text-4xl text-gray-300 group-hover:text-caramel/50 transition-colors" id="logo-placeholder"></i>
                                    <img src="" id="logo-preview" alt="Company Logo" class="h-full w-full object-cover hidden">
                                @endif
                            </div>
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-bold text-chocolate mb-1">Company Logo</label>
                            <p class="text-xs text-gray-500 mb-3">Recommended size: 500x500px. Formats: PNG, JPG.</p>
                            <div class="flex gap-2">
                                <input type="hidden" name="company_logo" id="company_logo" value="{{ $settings['company_logo'] ?? '' }}">
                                <button type="button" id="change-logo-btn" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-xs font-bold text-gray-700 hover:text-chocolate hover:border-chocolate transition-all shadow-sm">
                                    Change Logo
                                </button>
                                <button type="button" id="remove-logo-btn" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-xs font-bold text-red-600 hover:bg-red-50 hover:border-red-200 transition-all shadow-sm">
                                    Remove
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-sm font-bold text-chocolate mb-1">Company Name</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><i class="fas fa-store"></i></span>
                                <input type="text" name="company_name" value="{{ $settings['company_name'] ?? 'WellKenz Bakery' }}" class="pl-10 block w-full border-gray-300 rounded-lg shadow-sm focus:border-caramel focus:ring-caramel sm:text-sm">
                            </div>
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-sm font-bold text-chocolate mb-1">Tax ID / TIN</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><i class="fas fa-file-invoice"></i></span>
                                <input type="text" name="tax_id" value="{{ $settings['tax_id'] ?? '' }}" class="pl-10 block w-full border-gray-300 rounded-lg shadow-sm focus:border-caramel focus:ring-caramel sm:text-sm">
                            </div>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-bold text-chocolate mb-1">Official Address</label>
                            <textarea name="company_address" rows="2" class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-caramel focus:ring-caramel sm:text-sm">{{ $settings['company_address'] ?? '' }}</textarea>
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-sm font-bold text-chocolate mb-1">Contact Email</label>
                            <input type="email" name="contact_email" value="{{ $settings['contact_email'] ?? 'admin@wellkenz.com' }}" class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-caramel focus:ring-caramel sm:text-sm">
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-sm font-bold text-chocolate mb-1">Contact Phone</label>
                            <input type="text" name="contact_phone" value="{{ $settings['contact_phone'] ?? '' }}" class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-caramel focus:ring-caramel sm:text-sm">
                        </div>
                    </div>
                </form>
            </div>

            {{-- SECTION 2: NOTIFICATION PREFERENCES --}}
            <div id="notifications" class="bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden scroll-mt-24">
                <div class="px-6 py-5 border-b border-border-soft bg-cream-bg">
                    <h3 class="font-display text-lg font-bold text-chocolate">Notification Preferences</h3>
                    <p class="mt-1 text-xs text-gray-500">Control when and how the system alerts administrators.</p>
                </div>
                <form id="notifications-form" class="p-6 space-y-5">
                    
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-100 hover:border-caramel/30 transition-colors">
                        <div>
                            <label for="notif_lowstock" class="font-bold text-chocolate text-sm cursor-pointer">Low Stock Alerts</label>
                            <p class="text-xs text-gray-500 mt-0.5">Send email when an item reaches its reorder level.</p>
                        </div>
                        <div class="flex items-center h-5">
                            <input id="notif_lowstock" name="notif_lowstock" type="checkbox" value="1" {{ ($settings['notif_lowstock'] ?? true) ? 'checked' : '' }} class="h-5 w-5 text-chocolate focus:ring-caramel border-gray-300 rounded cursor-pointer">
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-100 hover:border-caramel/30 transition-colors">
                        <div>
                            <label for="notif_req" class="font-bold text-chocolate text-sm cursor-pointer">New Requisition Requests</label>
                            <p class="text-xs text-gray-500 mt-0.5">Notify when staff submits a new stock request.</p>
                        </div>
                        <div class="flex items-center h-5">
                            <input id="notif_req" name="notif_req" type="checkbox" value="1" {{ ($settings['notif_req'] ?? true) ? 'checked' : '' }} class="h-5 w-5 text-chocolate focus:ring-caramel border-gray-300 rounded cursor-pointer">
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-100 hover:border-caramel/30 transition-colors">
                        <div>
                            <label for="notif_expiry" class="font-bold text-chocolate text-sm cursor-pointer">Near Expiry Warnings</label>
                            <p class="text-xs text-gray-500 mt-0.5">Daily summary of items expiring within 7 days.</p>
                        </div>
                        <div class="flex items-center h-5">
                            <input id="notif_expiry" name="notif_expiry" type="checkbox" value="1" {{ ($settings['notif_expiry'] ?? true) ? 'checked' : '' }} class="h-5 w-5 text-chocolate focus:ring-caramel border-gray-300 rounded cursor-pointer">
                        </div>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border border-gray-100 hover:border-caramel/30 transition-colors">
                        <div>
                            <label for="notif_system" class="font-bold text-chocolate text-sm cursor-pointer">System Security Alerts</label>
                            <p class="text-xs text-gray-500 mt-0.5">Notify on failed logins or suspicious activity immediately.</p>
                        </div>
                        <div class="flex items-center h-5">
                            <input id="notif_system" name="notif_system" type="checkbox" value="1" {{ ($settings['notif_system'] ?? false) ? 'checked' : '' }} class="h-5 w-5 text-chocolate focus:ring-caramel border-gray-300 rounded cursor-pointer">
                        </div>
                    </div>

                </form>
            </div>

            {{-- SECTION 3: FINANCE & SYSTEM DEFAULTS --}}
            <div id="finance" class="bg-white border border-border-soft rounded-xl shadow-sm overflow-hidden scroll-mt-24">
                <div class="px-6 py-5 border-b border-border-soft bg-cream-bg">
                    <h3 class="font-display text-lg font-bold text-chocolate">Finance & System Defaults</h3>
                    <p class="mt-1 text-xs text-gray-500">Global settings for pricing and maintenance.</p>
                </div>
                <form id="finance-form" class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <div>
                            <label class="block text-sm font-bold text-chocolate mb-1">Currency Symbol</label>
                            <select name="currency" class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-caramel focus:ring-caramel sm:text-sm">
                                <option value="PHP" {{ ($settings['currency'] ?? 'PHP') == 'PHP' ? 'selected' : '' }}>₱ (PHP)</option>
                                <option value="USD" {{ ($settings['currency'] ?? 'PHP') == 'USD' ? 'selected' : '' }}>$ (USD)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-chocolate mb-1">Default Tax Rate (%)</label>
                            <input type="number" name="tax_rate" value="{{ number_format(($settings['tax_rate'] ?? 0.12) * 100, 1) }}" step="0.1" min="0" max="100" class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-caramel focus:ring-caramel sm:text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-chocolate mb-1">Low Stock Threshold</label>
                            <input type="number" name="low_stock_threshold" value="{{ $settings['low_stock_threshold'] ?? 10 }}" min="0" class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-caramel focus:ring-caramel sm:text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-chocolate mb-1">Default Lead Time (Days)</label>
                            <input type="number" name="default_lead_time" value="{{ $settings['default_lead_time'] ?? 3 }}" min="0" class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-caramel focus:ring-caramel sm:text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-chocolate mb-1">Business Hours Open</label>
                            <input type="time" name="business_hours_open" value="{{ $settings['business_hours_open'] ?? '06:00' }}" class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-caramel focus:ring-caramel sm:text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-chocolate mb-1">Business Hours Close</label>
                            <input type="time" name="business_hours_close" value="{{ $settings['business_hours_close'] ?? '20:00' }}" class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-caramel focus:ring-caramel sm:text-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-chocolate mb-1">Default Batch Size</label>
                            <input type="number" name="default_batch_size" value="{{ $settings['default_batch_size'] ?? 100 }}" min="1" class="block w-full border-gray-300 rounded-lg shadow-sm focus:border-caramel focus:ring-caramel sm:text-sm">
                        </div>
                    </div>

                    <div id="system" class="mt-8 pt-6 border-t border-border-soft scroll-mt-24">
                        <div class="flex items-center justify-between p-5 bg-cream-bg rounded-lg border border-border-soft">
                            <div>
                                <h4 class="text-sm font-bold text-chocolate flex items-center">
                                    <i class="fas fa-lock mr-2 text-caramel"></i> Maintenance Mode
                                </h4>
                                <p class="text-xs text-gray-500 mt-1">Prevent non-admin users from accessing the system.</p>
                            </div>
                            <div>
                                <button type="button" id="maintenance-toggle" 
                                    class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-caramel {{ ($settings['maintenance_mode'] ?? false) ? 'bg-chocolate' : 'bg-gray-300' }}" 
                                    role="switch" 
                                    aria-checked="{{ ($settings['maintenance_mode'] ?? false) ? 'true' : 'false' }}">
                                    <span aria-hidden="true" class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200 {{ ($settings['maintenance_mode'] ?? false) ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                </button>
                                <input type="hidden" name="maintenance_mode" id="maintenance_mode" value="{{ ($settings['maintenance_mode'] ?? false) ? '1' : '0' }}">
                            </div>
                        </div>
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>

<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
document.addEventListener('DOMContentLoaded', function() {
    // CSRF token for AJAX requests
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Maintenance mode toggle
    const maintenanceToggle = document.getElementById('maintenance-toggle');
    const maintenanceInput = document.getElementById('maintenance_mode');
    
    maintenanceToggle.addEventListener('click', function() {
        const isChecked = this.getAttribute('aria-checked') === 'true';
        const newState = !isChecked;
        
        this.setAttribute('aria-checked', newState);
        this.classList.toggle('bg-chocolate', newState);
        this.classList.toggle('bg-gray-300', !newState);
        
        const span = this.querySelector('span');
        span.classList.toggle('translate-x-5', newState);
        span.classList.toggle('translate-x-0', !newState);
        
        maintenanceInput.value = newState ? '1' : '0';
    });
    
    // Save settings button
    document.getElementById('save-settings-btn').addEventListener('click', function() {
        saveSettings();
    });
    
    // Logo management
    document.getElementById('change-logo-btn').addEventListener('click', function() {
        // In a real implementation, this would open a file picker
        const logoUrl = prompt('Enter logo URL:', document.getElementById('company_logo').value);
        if (logoUrl !== null && logoUrl.trim() !== '') {
            document.getElementById('company_logo').value = logoUrl;
            document.getElementById('logo-preview').src = logoUrl;
            document.getElementById('logo-preview').classList.remove('hidden');
            document.getElementById('logo-placeholder').classList.add('hidden');
        }
    });
    
    document.getElementById('remove-logo-btn').addEventListener('click', function() {
        document.getElementById('company_logo').value = '';
        document.getElementById('logo-preview').src = '';
        document.getElementById('logo-preview').classList.add('hidden');
        document.getElementById('logo-placeholder').classList.remove('hidden');
    });
    
    function saveSettings() {
        const saveBtn = document.getElementById('save-settings-btn');
        const originalText = saveBtn.innerHTML;
        
        // Show loading state
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';
        saveBtn.disabled = true;
        
        // Collect form data
        const formData = new FormData();
        
        // Collect data from all forms
        const forms = ['company-profile-form', 'notifications-form', 'finance-form'];
        forms.forEach(formId => {
            const form = document.getElementById(formId);
            const inputs = form.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                if (input.type === 'checkbox') {
                    formData.append(input.name, input.checked ? '1' : '0');
                } else {
                    formData.append(input.name, input.value);
                }
            });
        });
        
        // Send AJAX request
        fetch('/admin/settings', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            showAlert(data.success ? 'success' : 'error', data.message);
            
            if (data.success) {
                // Optionally update system info if company name changed
                if (data.updated_fields && data.updated_fields.includes('company_name')) {
                    // Update any DOM elements reflecting company name if necessary
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'An unexpected error occurred. Please try again.');
        })
        .finally(() => {
            // Restore button state
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
        });
    }
    
    function showAlert(type, message) {
        const alertContainer = document.getElementById('alert-container');
        // WellKenz theme alerts
        const alertClass = type === 'success' 
            ? 'bg-green-100 border border-green-200 text-green-800 shadow-lg' 
            : 'bg-red-100 border border-red-200 text-red-800 shadow-lg';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        
        const alertHtml = `
            <div class="${alertClass} px-4 py-4 rounded-lg relative mb-4 flex items-start animate-fade-in-down transition-all" role="alert">
                <i class="fas ${icon} mr-3 mt-0.5 text-lg"></i>
                <span class="block sm:inline text-sm font-medium">${message}</span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer opacity-70 hover:opacity-100" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </span>
            </div>
        `;
        
        alertContainer.innerHTML = alertHtml;
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            const alert = alertContainer.querySelector('[role="alert"]');
            if (alert) {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }
        }, 5000);
    }
});
</script>
@endsection