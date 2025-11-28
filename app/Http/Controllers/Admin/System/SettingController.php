<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    /**
     * Display the system settings page.
     *
     * @return \Illuminate\Http\Response
     */
    // public function index()
    // {
    //     // Settings functionality removed
    //     // Get all system settings
    //     $settings = $this->getAllSettings();
    //     
    //     // Get system information for display
    //     $systemInfo = $this->getSystemInfo();
    //     
    //     return view('Admin.system.general_setting', compact('settings', 'systemInfo'));
    // }

    /**
     * Update system settings.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    // public function update(Request $request)
    // {
    //     // Settings functionality removed
    //     $request->validate([
    //         // Company Profile
    //         'company_name' => 'required|string|max:255',
    //         'company_address' => 'nullable|string|max:500',
    //         'company_logo' => 'nullable|string|max:255',
    //         'tax_id' => 'nullable|string|max:50',
    //         'contact_email' => 'nullable|email|max:255',
    //         'contact_phone' => 'nullable|string|max:20',
    //         
    //         // Notifications (boolean values)
    //         'notif_lowstock' => 'boolean',
    //         'notif_req' => 'boolean',
    //         'notif_expiry' => 'boolean',
    //         'notif_system' => 'boolean',
    //         
    //         // Finance & System
    //         'currency' => 'nullable|string|max:10',
    //         'tax_rate' => 'nullable|numeric|min:0|max:100',
    //         'low_stock_threshold' => 'nullable|integer|min:0',
    //         'default_lead_time' => 'nullable|integer|min:0',
    //         'business_hours_open' => 'nullable|date_format:H:i',
    //         'business_hours_close' => 'nullable|date_format:H:i',
    //         'default_batch_size' => 'nullable|integer|min:1',
    //         'maintenance_mode' => 'boolean',
    //         
    //         // Legacy settings (backward compatibility)
    //         'company_phone' => 'nullable|string|max:20',
    //         'company_email' => 'nullable|email|max:255',
    //         'business_hours_start' => 'nullable|date_format:H:i',
    //         'business_hours_end' => 'nullable|date_format:H:i',
    //         'notification_email_enabled' => 'boolean',
    //         'auto_backup_enabled' => 'boolean',
    //         'backup_retention_days' => 'nullable|integer|min:1|max:365',
    //         'session_timeout' => 'nullable|integer|min:15|max:480',
    //         'max_login_attempts' => 'nullable|integer|min:3|max:10',
    //         'password_min_length' => 'nullable|integer|min:8|max:32',
    //     ]);

    //     try {
    //         DB::beginTransaction();
    //         
    //         $updatedSettings = [];
    //         $oldSettings = $this->getAllSettings();

    //         // Handle all setting fields from the request
    //         $allFields = [
    //             // Company Profile
    //             'company_logo', 'company_name', 'tax_id', 'company_address', 'contact_email', 'contact_phone',
    //             // Notifications
    //             'notif_lowstock', 'notif_req', 'notif_expiry', 'notif_system',
    //             // Finance & System
    //             'currency', 'tax_rate', 'low_stock_threshold', 'default_lead_time', 'business_hours_open', 
    //             'business_hours_close', 'default_batch_size', 'maintenance_mode',
    //             // Legacy
    //             'company_phone', 'company_email', 'business_hours_start', 'business_hours_end', 
    //             'notification_email_enabled', 'auto_backup_enabled', 'backup_retention_days',
    //             'session_timeout', 'max_login_attempts', 'password_min_length'
    //         ];

    //         foreach ($request->only($allFields) as $key => $value) {
    //             // Handle checkbox values (unchecked checkboxes don't get sent in form data)
    //             if (in_array($key, ['notif_lowstock', 'notif_req', 'notif_expiry', 'notif_system', 'maintenance_mode', 'notification_email_enabled', 'auto_backup_enabled'])) {
    //                 $value = $request->has($key) ? 1 : 0;
    //             }
    //             
    //             // Handle tax_rate conversion (form sends percentage, store as decimal)
    //             if ($key === 'tax_rate' && $value !== null) {
    //                 $value = $value / 100;
    //             }
    //             
    //             if ($value !== null && $value !== $oldSettings[$key]) {
    //                 SystemSetting::set($key, $value);
    //                 $updatedSettings[$key] = [
    //                     'old' => $oldSettings[$key],
    //                     'new' => $value
    //                 ];
    //             }
    //         }

    //         // Clear cache for system settings
    //         Cache::forget('system_settings');

    //         // Create audit log for each updated setting
    //         foreach ($updatedSettings as $settingKey => $values) {
    //             AuditLog::create([
    //                 'table_name' => 'system_settings',
    //                 'record_id' => 0, // Settings don't have specific IDs
    //                 'action' => 'UPDATE',
    //                 'user_id' => Auth::id(),
    //                 'ip_address' => $request->ip(),
    //                 'user_agent' => $request->userAgent(),
    //                 'old_values' => json_encode([$settingKey => $values['old']]),
    //                 'new_values' => json_encode([$settingKey => $values['new']]),
    //                 'metadata' => json_encode(['setting_name' => $settingKey])
    //             ]);
    //         }

    //         DB::commit();

    //         // Handle maintenance mode if it was changed
    //         if (array_key_exists('maintenance_mode', $updatedSettings)) {
    //             if ($updatedSettings['maintenance_mode']['new']) {
    //                 Artisan::call('down --secret="' . bin2hex(random_bytes(16)) . '"');
    //             } else {
    //                 Artisan::call('up');
    //             }
    //         }

    //         return response()->json([
    //             'success' => true,
    //             'message' => 'Settings updated successfully!',
    //             'updated_fields' => array_keys($updatedSettings)
    //         ]);

    //     } catch (\Exception $e) {
    //         DB::rollback();
    //         
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Error updating settings: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    /**
     * Get all system settings.
     *
     * @return array
     */
    // private function getAllSettings()
    // {
    //     // Settings functionality removed
    //     return [
    //         // Company Profile Settings
    //         'company_logo' => SystemSetting::get('company_logo', ''),
    //         'company_name' => SystemSetting::get('company_name', 'WellKenz Bakery'),
    //         'tax_id' => SystemSetting::get('tax_id', ''),
    //         'company_address' => SystemSetting::get('company_address', ''),
    //         'contact_email' => SystemSetting::get('contact_email', 'admin@wellkenz.com'),
    //         'contact_phone' => SystemSetting::get('contact_phone', ''),
    //         
    //         // Notification Settings
    //         'notif_lowstock' => SystemSetting::get('notif_lowstock', true),
    //         'notif_req' => SystemSetting::get('notif_req', true),
    //         'notif_expiry' => SystemSetting::get('notif_expiry', true),
    //         'notif_system' => SystemSetting::get('notif_system', false),
    //         
    //         // Finance & System Settings
    //         'currency' => SystemSetting::get('currency', 'PHP'),
    //         'tax_rate' => SystemSetting::get('tax_rate', 0.12),
    //         'low_stock_threshold' => SystemSetting::get('low_stock_threshold', 10),
    //         'default_lead_time' => SystemSetting::get('default_lead_time', 3),
    //         'business_hours_open' => SystemSetting::get('business_hours_open', '06:00'),
    //         'business_hours_close' => SystemSetting::get('business_hours_close', '20:00'),
    //         'default_batch_size' => SystemSetting::get('default_batch_size', 100),
    //         'maintenance_mode' => SystemSetting::get('maintenance_mode', false),
    //         
    //         // Legacy settings (keeping for backward compatibility)
    //         'company_phone' => SystemSetting::get('company_phone', ''),
    //         'company_email' => SystemSetting::get('company_email', ''),
    //         'business_hours_start' => SystemSetting::get('business_hours_start', '08:00'),
    //         'business_hours_end' => SystemSetting::get('business_hours_end', '17:00'),
    //         'notification_email_enabled' => SystemSetting::get('notification_email_enabled', true),
    //         'auto_backup_enabled' => SystemSetting::get('auto_backup_enabled', true),
    //         'backup_retention_days' => SystemSetting::get('backup_retention_days', 30),
    //         'session_timeout' => SystemSetting::get('session_timeout', 120),
    //         'max_login_attempts' => SystemSetting::get('max_login_attempts', 5),
    //         'password_min_length' => SystemSetting::get('password_min_length', 8),
    //     ];
    // }

    /**
     * Get system information for display.
     *
     * @return array
     */
    // private function getSystemInfo()
    // {
    //     // Settings functionality removed
    //     return [
    //         'version' => SystemSetting::get('app_version', 'v2.4.0'),
    //         'last_backup' => SystemSetting::get('last_backup_date', 'Never'),
    //         'timezone' => SystemSetting::get('app_timezone', 'Asia/Manila'),
    //     ];
    // }

    /**
     * Get system health information.
     *
     * @return \Illuminate\Http\Response
     */
    public function getSystemHealth()
    {
        try {
            $health = [];
            
            // Database health
            try {
                DB::connection()->getPdo();
                $health['database'] = [
                    'status' => 'healthy',
                    'message' => 'Database connection successful'
                ];
            } catch (\Exception $e) {
                $health['database'] = [
                    'status' => 'error',
                    'message' => 'Database connection failed: ' . $e->getMessage()
                ];
            }

            // Cache health
            try {
                $testKey = 'health_check_' . time();
                Cache::put($testKey, 'test', 10);
                $retrieved = Cache::get($testKey);
                Cache::forget($testKey);
                
                $health['cache'] = [
                    'status' => 'healthy',
                    'message' => 'Cache system working correctly'
                ];
            } catch (\Exception $e) {
                $health['cache'] = [
                    'status' => 'error',
                    'message' => 'Cache system error: ' . $e->getMessage()
                ];
            }

            // Storage health
            try {
                $storageSize = $this->getStorageSize();
                $availableSpace = disk_free_space(storage_path());
                
                $health['storage'] = [
                    'status' => 'healthy',
                    'message' => 'Storage system working',
                    'size' => $storageSize,
                    'available_space' => $availableSpace
                ];
            } catch (\Exception $e) {
                $health['storage'] = [
                    'status' => 'warning',
                    'message' => 'Storage system error: ' . $e->getMessage()
                ];
            }

            // Application health
            $health['application'] = [
                'status' => 'healthy',
                'message' => 'Application running normally',
                'laravel_version' => app()->version(),
                'php_version' => PHP_VERSION,
                'environment' => app()->environment()
            ];

            return response()->json([
                'success' => true,
                'data' => $health
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error checking system health: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get storage size information.
     *
     * @return array
     */
    private function getStorageSize()
    {
        $storagePath = storage_path();
        $totalSize = 0;
        $fileCount = 0;

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($storagePath, \FilesystemIterator::SKIP_DOTS)) as $file) {
            if ($file->isFile()) {
                $totalSize += $file->getSize();
                $fileCount++;
            }
        }

        return [
            'size_in_bytes' => $totalSize,
            'size_formatted' => $this->formatBytes($totalSize),
            'file_count' => $fileCount
        ];
    }

    /**
     * Format bytes to human readable format.
     *
     * @param int $size
     * @return string
     */
    private function formatBytes($size)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $unitIndex = 0;
        
        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }
        
        return round($size, 2) . ' ' . $units[$unitIndex];
    }

    /**
     * Clear application cache.
     *
     * @return \Illuminate\Http\Response
     */
    public function clearCache()
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('view:clear');
            Artisan::call('route:clear');
            
            return response()->json([
                'success' => true,
                'message' => 'Application cache cleared successfully!'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error clearing cache: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Optimize application for production.
     *
     * @return \Illuminate\Http\Response
     */
    public function optimize()
    {
        try {
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');
            
            return response()->json([
                'success' => true,
                'message' => 'Application optimized successfully!'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error optimizing application: ' . $e->getMessage()
            ], 500);
        }
    }
}