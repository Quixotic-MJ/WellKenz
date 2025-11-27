<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Excel;

class AuditLogController extends Controller
{
    /**
     * Display a listing of audit logs.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = AuditLog::with('user')
            ->orderBy('created_at', 'desc');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('table_name', 'ilike', "%{$search}%")
                  ->orWhere('action', 'ilike', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'ilike', "%{$search}%");
                  });
            });
        }

        // Action filter
        if ($request->filled('action')) {
            $query->where('action', strtoupper($request->action));
        }

        // User filter
        if ($request->filled('user') && $request->user !== 'system') {
            $query->where('user_id', $request->user);
        }

        // Module filter (based on table names)
        if ($request->filled('module')) {
            $module = $request->module;
            $moduleTables = [
                'auth' => ['users', 'user_profiles'],
                'inventory' => ['items', 'stock_movements', 'batches', 'current_stock'],
                'finance' => ['purchase_orders', 'purchase_order_items', 'suppliers'],
                'users' => ['users', 'user_profiles']
            ];
            
            if (isset($moduleTables[$module])) {
                $query->whereIn('table_name', $moduleTables[$module]);
            }
        }

        // Table filter
        if ($request->filled('table')) {
            $query->where('table_name', strtolower($request->table));
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $perPage = $request->get('per_page', 15);
        $auditLogs = $query->paginate($perPage)
            ->withQueryString();

        // Get users for filter dropdown
        $users = User::select('id', 'name', 'role')
            ->orderBy('name')
            ->get();

        // Get total logs count for display
        $totalLogs = AuditLog::count();

        return view('Admin.system.audit_logs', compact('auditLogs', 'users', 'totalLogs'));
    }

    /**
     * Show details of a specific audit log.
     *
     * @param \App\Models\AuditLog $auditLog
     * @return \Illuminate\Http\Response
     */
    public function show(AuditLog $auditLog)
    {
        $auditLog->load('user');
        
        return view('Admin.system.audit_logs_show', compact('auditLog'));
    }

    /**
     * Export audit logs to CSV.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        try {
            $query = AuditLog::with('user')
                ->orderBy('created_at', 'desc');

            // Apply same filters as index
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('table_name', 'ilike', "%{$search}%")
                      ->orWhere('action', 'ilike', "%{$search}%")
                      ->orWhereHas('user', function($userQuery) use ($search) {
                          $userQuery->where('name', 'ilike', "%{$search}%");
                      });
                });
            }

            if ($request->filled('action')) {
                $query->where('action', strtoupper($request->action));
            }

            if ($request->filled('user') && $request->user !== 'system') {
                $query->where('user_id', $request->user);
            }

            // Module filter (based on table names)
            if ($request->filled('module')) {
                $module = $request->module;
                $moduleTables = [
                    'auth' => ['users', 'user_profiles'],
                    'inventory' => ['items', 'stock_movements', 'batches', 'current_stock'],
                    'finance' => ['purchase_orders', 'purchase_order_items', 'suppliers'],
                    'users' => ['users', 'user_profiles']
                ];
                
                if (isset($moduleTables[$module])) {
                    $query->whereIn('table_name', $moduleTables[$module]);
                }
            }

            if ($request->filled('table')) {
                $query->where('table_name', strtolower($request->table));
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            $auditLogs = $query->get();

            // Generate filename with timestamp
            $filename = 'audit_logs_' . now()->format('Y-m-d_H-i-s') . '.csv';
            $filepath = 'exports/' . $filename;

            // Create CSV content
            $csvContent = "Date,Time,User,Table Name,Action,Record ID,IP Address,Changes\n";
            
            foreach ($auditLogs as $log) {
                $userName = $log->user->name ?? 'System';
                $dateTime = $log->created_at->format('Y-m-d H:i:s');
                $tableName = ucwords(str_replace('_', ' ', $log->table_name));
                $action = ucfirst(strtolower($log->action));
                $recordId = $log->record_id;
                $ipAddress = $log->ip_address ?? 'N/A';
                
                // Format changes
                $changes = '';
                if ($log->old_values && $log->new_values) {
                    $oldValues = json_decode($log->old_values, true);
                    $newValues = json_decode($log->new_values, true);
                    
                    if ($oldValues && $newValues) {
                        foreach ($oldValues as $key => $oldValue) {
                            $newValue = $newValues[$key] ?? '';
                            if ($oldValue !== $newValue) {
                                $changes .= "{$key}: {$oldValue} → {$newValue}; ";
                            }
                        }
                    }
                }

                $csvContent .= "{$log->created_at->format('Y-m-d')},{$log->created_at->format('H:i:s')},\"{$userName}\",\"{$tableName}\",\"{$action}\",{$recordId},\"{$ipAddress}\",\"{$changes}\"\n";
            }

            // Store file temporarily
            Storage::put($filepath, $csvContent);

            // Download file
            return response()->download(storage_path('app/' . $filepath))->deleteFileAfterSend();

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error exporting audit logs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export audit log proof document.
     *
     * @param \App\Models\AuditLog $auditLog
     * @return \Illuminate\Http\Response
     */
    public function exportProof(AuditLog $auditLog)
    {
        try {
            $auditLog->load('user');
            
            // Generate proof document content
            $content = $this->generateProofDocument($auditLog);
            
            // Create filename
            $filename = "audit_proof_{$auditLog->id}_" . now()->format('Y-m-d_H-i-s') . '.txt';
            $filepath = 'exports/' . $filename;

            // Store file
            Storage::put($filepath, $content);

            // Download file
            return response()->download(storage_path('app/' . $filepath))->deleteFileAfterSend();

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error exporting audit proof: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate proof document content.
     *
     * @param \App\Models\AuditLog $auditLog
     * @return string
     */
    private function generateProofDocument(AuditLog $auditLog)
    {
        $userName = $auditLog->user->name ?? 'System';
        $content = "=========================================================\n";
        $content .= "          AUDIT LOG PROOF DOCUMENT\n";
        $content .= "=========================================================\n\n";
        
        $content .= "Audit Log ID: {$auditLog->id}\n";
        $content .= "Date & Time: {$auditLog->created_at->format('Y-m-d H:i:s')}\n";
        $content .= "User: {$userName}\n";
        $content .= "Table: {$auditLog->table_name}\n";
        $content .= "Record ID: {$auditLog->record_id}\n";
        $content .= "Action: {$auditLog->action}\n";
        $content .= "IP Address: " . ($auditLog->ip_address ?? 'N/A') . "\n";
        $content .= "User Agent: " . ($auditLog->user_agent ?? 'N/A') . "\n\n";
        
        $content .= "OLD VALUES:\n";
        $content .= "-----------\n";
        if ($auditLog->old_values) {
            $oldValues = json_decode($auditLog->old_values, true);
            if ($oldValues) {
                foreach ($oldValues as $key => $value) {
                    $content .= "{$key}: {$value}\n";
                }
            } else {
                $content .= "No old values recorded\n";
            }
        } else {
            $content .= "No old values recorded\n";
        }
        
        $content .= "\nNEW VALUES:\n";
        $content .= "-----------\n";
        if ($auditLog->new_values) {
            $newValues = json_decode($auditLog->new_values, true);
            if ($newValues) {
                foreach ($newValues as $key => $value) {
                    $content .= "{$key}: {$value}\n";
                }
            } else {
                $content .= "No new values recorded\n";
            }
        } else {
            $content .= "No new values recorded\n";
        }
        
        $content .= "\n=========================================================\n";
        $content .= "This document serves as legal proof of the audit trail.\n";
        $content .= "Generated by WellKenz Bakery ERP System on: " . now()->format('Y-m-d H:i:s') . "\n";
        $content .= "=========================================================\n";
        
        return $content;
    }

    /**
     * Get available table names for filter dropdown.
     *
     * @return \Illuminate\Http\Response
     */
    public function getTableNames()
    {
        $tableNames = AuditLog::select('table_name')
            ->distinct()
            ->orderBy('table_name')
            ->pluck('table_name')
            ->map(function ($table) {
                return [
                    'value' => $table,
                    'label' => ucwords(str_replace('_', ' ', $table))
                ];
            });

        return response()->json($tableNames);
    }

    /**
     * Get available actions for filter dropdown.
     *
     * @return \Illuminate\Http\Response
     */
    public function getActions()
    {
        $actions = AuditLog::select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action')
            ->map(function ($action) {
                return [
                    'value' => $action,
                    'label' => ucfirst(strtolower($action))
                ];
            });

        return response()->json($actions);
    }
}