<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Item;
use App\Models\Category;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard overview.
     *
     * @return \Illuminate\Http\Response
     */
    public function systemOverview()
    {
        // Get greeting based on time of day
        $hour = Carbon::now()->hour;
        if ($hour < 12) {
            $greeting = 'Good Morning';
        } elseif ($hour < 17) {
            $greeting = 'Good Afternoon';
        } else {
            $greeting = 'Good Evening';
        }

        // Get current user name
        $userName = auth()->user()->name ?? 'Admin';

        // Get database health info
        $databaseHealth = $this->getDatabaseHealth();

        // Get security alerts count (locked users + failed logins)
        $securityAlertsCount = $this->getSecurityAlertsCount();

        // Get audit log volume (last 24 hours)
        $auditLogVolume = $this->getAuditLogVolume();

        $data = [
            // Welcome message data
            'greeting' => $greeting,
            'userName' => $userName,
            'currentTime' => Carbon::now(),
            'currentDate' => Carbon::now()->format('F j, Y'),
            'currentDay' => Carbon::now()->format('l'),

            // User Statistics
            'totalUsers' => User::count(),
            'activeUsers' => User::where('is_active', true)->count(),
            'inactiveUsers' => User::where('is_active', false)->count(),

            // System Health Metrics
            'databaseHealth' => $databaseHealth,
            'securityAlertsCount' => $securityAlertsCount,
            'auditLogVolume' => $auditLogVolume,

            // System Statistics
            'totalItems' => Item::count(),
            'categoryCount' => Category::where('is_active', true)->count(),
        ];

        return view('Admin.system_overview', $data);
    }

    /**
     * Get database health information.
     *
     * @return array
     */
    private function getDatabaseHealth()
    {
        try {
            // Test database connection
            DB::connection()->getPdo();
            
            // Get last backup info from audit logs
            $lastBackup = AuditLog::where('action', 'backup')
                ->where('table_name', 'database')
                ->orderBy('created_at', 'desc')
                ->first();
                
            return [
                'status' => 'healthy',
                'status_color' => 'green', // green for healthy status
                'message' => 'Database connection stable',
                'last_backup' => $lastBackup ? $lastBackup->created_at->diffForHumans() : 'No backups found'
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'status_color' => 'red', // red for error status
                'message' => 'Database connection failed',
                'last_backup' => 'Backup information unavailable'
            ];
        }
    }

    /**
     * Get security alerts count (inactive users + failed login attempts).
     *
     * @return int
     */
    private function getSecurityAlertsCount()
    {
        // Count inactive users (potential security concern)
        $inactiveUsers = User::where('is_active', false)->count();
        
        // Count failed login attempts in the last 7 days
        $failedLogins = AuditLog::where('table_name', 'users')
            ->where('action', 'failed_login')
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->count();
            
        return $inactiveUsers + $failedLogins;
    }

    /**
     * Get audit log volume (count of audit logs in the last 24 hours).
     *
     * @return int
     */
    private function getAuditLogVolume()
    {
        return AuditLog::where('created_at', '>=', Carbon::now()->subHours(24))
            ->count();
    }



    /**
     * Generate log description for audit log entries.
     *
     * @param \App\Models\AuditLog $log
     * @return string
     */
    private function generateLogDescription($log)
    {
        $action = strtolower($log->action);
        $table = $log->table_name;
        
        switch ($table) {
            case 'items':
                if ($action === 'create') return 'New Item Added';
                if ($action === 'update') return 'Item Updated';
                if ($action === 'delete') return 'Item Removed';
                break;
            case 'users':
                if ($action === 'create') return 'New User Added';
                if ($action === 'update') return 'User Modified';
                if ($action === 'delete') return 'User Removed';
                break;
            case 'purchase_orders':
                if ($action === 'create') return 'Purchase Order Created';
                if ($action === 'update') return 'Purchase Order Updated';
                break;
            case 'suppliers':
                if ($action === 'create') return 'Supplier Added';
                if ($action === 'update') return 'Supplier Updated';
                if ($action === 'delete') return 'Supplier Removed';
                break;
            default:
                return ucfirst($action) . ' on ' . ucwords(str_replace('_', ' ', $table));
        }
        
        return ucfirst($action) . ' on ' . ucwords(str_replace('_', ' ', $table));
    }

    /**
     * Generate security log details.
     *
     * @param \App\Models\AuditLog $log
     * @return string
     */
    private function generateSecurityLogDetails($log)
    {
        $action = strtolower($log->action);
        $table = $log->table_name;
        
        if ($action === 'login') {
            return 'User login attempt recorded.';
        } elseif ($action === 'update' && $table === 'users') {
            return 'User profile or permissions modified.';
        } elseif ($action === 'create') {
            return 'New system record created.';
        } elseif ($action === 'delete') {
            return 'System record removed.';
        }
        
        return 'System activity recorded.';
    }
}