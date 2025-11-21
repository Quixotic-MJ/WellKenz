@extends('Admin.layout.app')

@section('content')
<div class="space-y-6">
    <!-- Success/Error Messages -->
    <div id="alert-container"></div>

    {{-- 1. HEADER --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">General Settings</h1>
            <p class="text-sm text-gray-500 mt-1">Configure core company details, branding, and system notifications.</p>
        </div>
        <div class="flex items-center gap-3">
            <!-- Save Button (Global) -->
            <button type="button" id="save-settings-btn" class="inline-flex items-center justify-center px-6 py-2 bg-chocolate text-white text-sm font-medium rounded-lg hover:bg-chocolate-dark transition shadow-sm">
                <i class="fas fa-save mr-2"></i> Save Changes
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- LEFT COLUMN: NAVIGATION / ANCHORS --}}
        <div class="hidden lg:block lg:col-span-1 space-y-6">
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4">
                <nav class="space-y-1">
                    <a href="#company-profile" class="flex items-center px-3 py-2 text-sm font-medium text-chocolate bg-orange-50 rounded-md group">
                        <i class="fas fa-building w-6 text-center mr-2"></i> Company Profile
                    </a>
                    <a href="#notifications" class="flex items-center px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-md group">
                        <i class="fas fa-bell w-6 text-center mr-2"></i> Notifications
                    </a>
                    <a href="#finance" class="flex items-center px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-md group">
                        <i class="fas fa-coins w-6 text-center mr-2"></i> Finance & Tax
                    </a>
                    <a href="#system" class="flex items-center px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900 rounded-md group">
                        <i class="fas fa-server w-6 text-center mr-2"></i> System Maintenance
                    </a>
                </nav>
            </div>

            <!-- System Info Card -->
            <div class="bg-blue-50 border border-blue-100 rounded-lg p-4">
                <h4 class="text-sm font-bold text-blue-800 mb-2">System Information</h4>
                <div class="text-xs text-blue-700 space-y-1">
                    <p class="flex justify-between"><span>Version:</span> <span class="font-mono">{{ $systemInfo['version'] ?? 'v2.4.0' }}</span></p>
                    <p class="flex justify-between"><span>Last Backup:</span> <span>{{ $systemInfo['last_backup'] ?? 'Never' }}</span></p>
                    <p class="flex justify-between"><span>Timezone:</span> <span>{{ $systemInfo['timezone'] ?? 'Asia/Manila' }}</span></p>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: FORMS --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- SECTION 1: COMPANY PROFILE --}}
            <div id="company-profile" class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-b-100 bg-gray-50">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Company Profile</h3>
                    <p class="mt-1 text-sm text-gray-500">This information will appear on Purchase Orders and Reports.</p>
                </div>
                <form id="company-profile-form" class="p-6 space-y-6">
                    
                    <!-- Logo Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Company Logo</label>
                        <div class="mt-2 flex items-center">
                            <span class="h-16 w-16 rounded-lg overflow-hidden bg-gray-100 border border-gray-300 flex items-center justify-center">
                                @if(!empty($settings['company_logo']))
                                    <img src="{{ $settings['company_logo'] }}" alt="Company Logo" class="h-full w-full object-cover">
                                @else
                                    <i class="fas fa-birthday-cake text-3xl text-gray-400"></i>
                                @endif
                            </span>
                            <input type="hidden" name="company_logo" id="company_logo" value="{{ $settings['company_logo'] ?? '' }}">
                            <button type="button" id="change-logo-btn" class="ml-5 bg-white py-2 px-3 border border-gray-300 rounded-md shadow-sm text-sm leading-4 font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-chocolate">
                                Change
                            </button>
                            <button type="button" id="remove-logo-btn" class="ml-2 bg-white py-2 px-3 border border-transparent rounded-md text-sm leading-4 font-medium text-red-600 hover:bg-red-50 focus:outline-none">
                                Remove
                            </button>
                        </div>
                    </div>

                    <!-- Company Details -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700">Company Name</label>
                            <input type="text" name="company_name" value="{{ $settings['company_name'] ?? 'WellKenz Bakery' }}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-chocolate focus:border-chocolate sm:text-sm">
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700">Tax ID / TIN</label>
                            <input type="text" name="tax_id" value="{{ $settings['tax_id'] ?? '' }}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-chocolate focus:border-chocolate sm:text-sm">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Official Address</label>
                            <textarea name="company_address" rows="2" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-chocolate focus:border-chocolate sm:text-sm">{{ $settings['company_address'] ?? '' }}</textarea>
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700">Contact Email</label>
                            <input type="email" name="contact_email" value="{{ $settings['contact_email'] ?? 'admin@wellkenz.com' }}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-chocolate focus:border-chocolate sm:text-sm">
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-sm font-medium text-gray-700">Contact Phone</label>
                            <input type="text" name="contact_phone" value="{{ $settings['contact_phone'] ?? '' }}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-chocolate focus:border-chocolate sm:text-sm">
                        </div>
                    </div>
                </form>
            </div>

            {{-- SECTION 2: NOTIFICATION PREFERENCES --}}
            <div id="notifications" class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-b-100 bg-gray-50">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Notification Preferences</h3>
                    <p class="mt-1 text-sm text-gray-500">Control when and how the system alerts administrators.</p>
                </div>
                <form id="notifications-form" class="p-6 space-y-6">
                    
                    <div class="flex items-start justify-between">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="notif_lowstock" name="notif_lowstock" type="checkbox" value="1" {{ ($settings['notif_lowstock'] ?? true) ? 'checked' : '' }} class="focus:ring-chocolate h-4 w-4 text-chocolate border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="notif_lowstock" class="font-medium text-gray-700">Low Stock Alerts</label>
                                <p class="text-gray-500">Send email when an item reaches its reorder level.</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-start justify-between">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="notif_req" name="notif_req" type="checkbox" value="1" {{ ($settings['notif_req'] ?? true) ? 'checked' : '' }} class="focus:ring-chocolate h-4 w-4 text-chocolate border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="notif_req" class="font-medium text-gray-700">New Requisition Requests</label>
                                <p class="text-gray-500">Notify when staff submits a new stock request.</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-start justify-between">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="notif_expiry" name="notif_expiry" type="checkbox" value="1" {{ ($settings['notif_expiry'] ?? true) ? 'checked' : '' }} class="focus:ring-chocolate h-4 w-4 text-chocolate border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="notif_expiry" class="font-medium text-gray-700">Near Expiry Warnings</label>
                                <p class="text-gray-500">Daily summary of items expiring within 7 days.</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-start justify-between">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input id="notif_system" name="notif_system" type="checkbox" value="1" {{ ($settings['notif_system'] ?? false) ? 'checked' : '' }} class="focus:ring-chocolate h-4 w-4 text-chocolate border-gray-300 rounded">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="notif_system" class="font-medium text-gray-700">System Security Alerts</label>
                                <p class="text-gray-500">Notify on failed logins or suspicious activity immediately.</p>
                            </div>
                        </div>
                    </div>

                </form>
            </div>

            {{-- SECTION 3: FINANCE & SYSTEM DEFAULTS --}}
            <div id="finance" class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-b-100 bg-gray-50">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Finance & System Defaults</h3>
                    <p class="mt-1 text-sm text-gray-500">Global settings for pricing and maintenance.</p>
                </div>
                <form id="finance-form" class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Currency Symbol</label>
                        <select name="currency" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-chocolate focus:border-chocolate sm:text-sm">
                            <option value="PHP" {{ ($settings['currency'] ?? 'PHP') == 'PHP' ? 'selected' : '' }}>₱ (PHP)</option>
                            <option value="USD" {{ ($settings['currency'] ?? 'PHP') == 'USD' ? 'selected' : '' }}>$ (USD)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Default Tax Rate (%)</label>
                        <input type="number" name="tax_rate" value="{{ number_format(($settings['tax_rate'] ?? 0.12) * 100, 1) }}" step="0.1" min="0" max="100" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-chocolate focus:border-chocolate sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Low Stock Threshold</label>
                        <input type="number" name="low_stock_threshold" value="{{ $settings['low_stock_threshold'] ?? 10 }}" min="0" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-chocolate focus:border-chocolate sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Default Lead Time (Days)</label>
                        <input type="number" name="default_lead_time" value="{{ $settings['default_lead_time'] ?? 3 }}" min="0" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-chocolate focus:border-chocolate sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Business Hours Open</label>
                        <input type="time" name="business_hours_open" value="{{ $settings['business_hours_open'] ?? '06:00' }}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-chocolate focus:border-chocolate sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Business Hours Close</label>
                        <input type="time" name="business_hours_close" value="{{ $settings['business_hours_close'] ?? '20:00' }}" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-chocolate focus:border-chocolate sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Default Batch Size</label>
                        <input type="number" name="default_batch_size" value="{{ $settings['default_batch_size'] ?? 100 }}" min="1" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:ring-chocolate focus:border-chocolate sm:text-sm">
                    </div>

                    <div class="col-span-2 border-t border-gray-100 pt-4 mt-2">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-sm font-medium text-gray-900">Maintenance Mode</h4>
                                <p class="text-sm text-gray-500">Prevent non-admin users from accessing the system.</p>
                            </div>
                            <button type="button" id="maintenance-toggle" class="relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-chocolate {{ ($settings['maintenance_mode'] ?? false) ? 'bg-chocolate' : 'bg-gray-200' }}" role="switch" aria-checked="{{ ($settings['maintenance_mode'] ?? false) ? 'true' : 'false' }}">
                                <span aria-hidden="true" class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200 {{ ($settings['maintenance_mode'] ?? false) ? 'translate-x-5' : 'translate-x-0' }}"></span>
                            </button>
                            <input type="hidden" name="maintenance_mode" id="maintenance_mode" value="{{ ($settings['maintenance_mode'] ?? false) ? '1' : '0' }}">
                        </div>
                    </div>

                </form>
            </div>

        </div>
    </div>
</div>

<!-- Hidden CSRF token for AJAX requests -->
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
        this.classList.toggle('bg-gray-200', !newState);
        
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
        if (logoUrl !== null) {
            document.getElementById('company_logo').value = logoUrl;
            // Update preview (you might want to implement actual file upload)
        }
    });
    
    document.getElementById('remove-logo-btn').addEventListener('click', function() {
        document.getElementById('company_logo').value = '';
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
                    // Could update page title or other elements
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
        const alertClass = type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        
        const alertHtml = `
            <div class="${alertClass} border px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">
                    <i class="fas ${icon} mr-2"></i>${message}
                </span>
                <span class="absolute top-0 bottom-0 right-0 px-4 py-3 cursor-pointer" onclick="this.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </span>
            </div>
        `;
        
        alertContainer.innerHTML = alertHtml;
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            const alert = alertContainer.querySelector('.relative');
            if (alert) {
                alert.remove();
            }
        }, 5000);
    }
});
</script>
@endsection