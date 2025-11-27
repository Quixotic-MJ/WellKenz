<?php

namespace App\Http\Controllers\Admin\System;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class BackupController extends Controller
{
    protected $backupPath = 'backups/';

    /**
     * Display backup management interface.
     */
    public function index()
    {
        $backups = $this->getBackupList();
        $backupStats = $this->getBackupStats();
        
        // Extract backup stats with expected variable names for the view
        $lastBackupTime = $backupStats['newest_backup'];
        $storageUsed = $backupStats['total_size'];
        $storageLimit = '5 GB'; // Static limit for display
        $totalBackups = $backupStats['total_backups'];
        $nextScheduled = 'Not Scheduled'; // No automatic scheduling implemented yet
        $timeUntilNext = 'N/A';
        
        return view('Admin.system.backup', compact(
            'backups', 
            'backupStats', 
            'lastBackupTime', 
            'storageUsed', 
            'storageLimit', 
            'totalBackups', 
            'nextScheduled', 
            'timeUntilNext'
        ));
    }

    /**
     * Create a new database backup.
     */
    public function create()
    {
        try {
            $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
            $filename = "backup_{$timestamp}.sql";
            
            // Create backup directory if it doesn't exist
            $backupPath = storage_path('app/backups');
            if (!File::exists($backupPath)) {
                File::makeDirectory($backupPath, 0755, true);
            }

            // Generate database dump
            $command = $this->generateBackupCommand($filename);
            $output = [];
            $returnCode = 0;
            
            exec($command, $output, $returnCode);
            
            if ($returnCode === 0) {
                // Log the backup creation
                $this->logAction('create_backup', null, 'system_backups', [
                    'filename' => $filename,
                    'size' => File::size($backupPath . '/' . $filename),
                    'created_by' => Auth::user()->name,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Backup created successfully!',
                    'filename' => $filename
                ]);
            } else {
                throw new \Exception('Backup command failed with code: ' . $returnCode);
            }

        } catch (\Exception $e) {
            Log::error('Backup creation failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error creating backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download a backup file.
     */
    public function download($filename)
    {
        try {
            $filepath = storage_path('app/backups/' . $filename);
            
            if (!File::exists($filepath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup file not found.'
                ], 404);
            }

            // Log the download action
            $this->logAction('download_backup', null, 'system_backups', [
                'filename' => $filename,
                'size' => File::size($filepath),
                'downloaded_by' => Auth::user()->name,
            ]);

            return response()->download($filepath, $filename);

        } catch (\Exception $e) {
            Log::error('Backup download failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error downloading backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get backup history with details.
     */
    public function history()
    {
        $backups = $this->getBackupList();
        
        return response()->json([
            'success' => true,
            'backups' => $backups
        ]);
    }

    /**
     * Restore database from backup.
     */
    public function restore(Request $request)
    {
        $request->validate([
            'filename' => 'required|string',
            'confirm_restore' => 'required|accepted'
        ]);

        try {
            $filename = $request->filename;
            $filepath = storage_path('app/backups/' . $filename);
            
            if (!File::exists($filepath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup file not found.'
                ], 404);
            }

            // Check if file is too large for restoration
            $fileSize = File::size($filepath);
            $maxRestoreSize = 50 * 1024 * 1024; // 50MB limit
            
            if ($fileSize > $maxRestoreSize) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup file is too large to restore automatically. Please restore manually.'
                ], 422);
            }

            DB::beginTransaction();
            
            try {
                // Create a safety backup before restore
                $safetyBackup = $this->createSafetyBackup();
                
                // Read and execute the backup SQL file
                $sql = File::get($filepath);
                $statements = $this->parseSqlStatements($sql);
                
                foreach ($statements as $statement) {
                    if (trim($statement)) {
                        DB::statement($statement);
                    }
                }

                // Log the restore action
                $this->logAction('restore_backup', null, 'system_backups', [
                    'filename' => $filename,
                    'file_size' => $fileSize,
                    'safety_backup' => $safetyBackup,
                    'restored_by' => Auth::user()->name,
                ]);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Database restored successfully from backup!',
                    'safety_backup' => $safetyBackup
                ]);

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Backup restore failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error restoring backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a backup file.
     */
    public function destroy($filename)
    {
        try {
            $filepath = storage_path('app/backups/' . $filename);
            
            if (!File::exists($filepath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup file not found.'
                ], 404);
            }

            $fileSize = File::size($filepath);
            File::delete($filepath);

            // Log the deletion
            $this->logAction('delete_backup', null, 'system_backups', [
                'filename' => $filename,
                'file_size' => $fileSize,
                'deleted_by' => Auth::user()->name,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Backup deleted successfully!'
            ]);

        } catch (\Exception $e) {
            Log::error('Backup deletion failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error deleting backup: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available backup files list.
     */
    public function getBackupFiles()
    {
        $backups = $this->getBackupList();
        
        return response()->json([
            'success' => true,
            'backups' => $backups
        ]);
    }

    /**
     * Get list of all backup files.
     */
    private function getBackupList()
    {
        $backupPath = storage_path('app/backups');
        
        if (!File::exists($backupPath)) {
            return [];
        }

        $files = File::allFiles($backupPath);
        $backups = [];

        foreach ($files as $file) {
            if (pathinfo($file->getFilename(), PATHINFO_EXTENSION) === 'sql') {
                $backups[] = [
                    'filename' => $file->getFilename(),
                    'size' => $this->formatBytes($file->getSize()),
                    'size_bytes' => $file->getSize(),
                    'created_at' => Carbon::createFromTimestamp($file->getMTime()),
                    'created_at_formatted' => Carbon::createFromTimestamp($file->getMTime())->format('M d, Y H:i:s'),
                    'formatted_size' => $this->formatBytes($file->getSize()),
                    'formatted_date' => Carbon::createFromTimestamp($file->getMTime())->format('M j, Y • g:i A'),
                    'type' => 'manual', // All backups created through this system are manual
                    'path' => $file->getPathname(),
                ];
            }
        }

        // Sort by creation date (newest first)
        usort($backups, function ($a, $b) {
            return $b['created_at']->timestamp - $a['created_at']->timestamp;
        });

        return $backups;
    }

    /**
     * Get backup statistics.
     */
    private function getBackupStats()
    {
        $backups = $this->getBackupList();
        
        $totalSize = 0;
        $oldestBackup = null;
        $newestBackup = null;
        
        foreach ($backups as $backup) {
            $totalSize += $backup['size_bytes'];
            
            if (!$oldestBackup || $backup['created_at']->lt($oldestBackup)) {
                $oldestBackup = $backup['created_at'];
            }
            
            if (!$newestBackup || $backup['created_at']->gt($newestBackup)) {
                $newestBackup = $backup['created_at'];
            }
        }

        return [
            'total_backups' => count($backups),
            'total_size' => $this->formatBytes($totalSize),
            'total_size_bytes' => $totalSize,
            'oldest_backup' => $oldestBackup ? $oldestBackup->format('M d, Y H:i:s') : 'N/A',
            'newest_backup' => $newestBackup ? $newestBackup->format('M d, Y H:i:s') : 'N/A',
            'average_size' => count($backups) > 0 ? $this->formatBytes($totalSize / count($backups)) : '0 B',
        ];
    }

    /**
     * Generate backup command based on database type.
     */
    private function generateBackupCommand($filename)
    {
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");
        
        $backupPath = storage_path('app/backups/' . $filename);
        
        switch ($connection) {
            case 'mysql':
                $command = sprintf(
                    'mysqldump --user=%s --password=%s --host=%s --port=%s %s > %s',
                    escapeshellarg($config['username']),
                    escapeshellarg($config['password']),
                    escapeshellarg($config['host']),
                    escapeshellarg($config['port'] ?? '3306'),
                    escapeshellarg($config['database']),
                    escapeshellarg($backupPath)
                );
                break;
                
            case 'pgsql':
                $command = sprintf(
                    'PGPASSWORD=%s pg_dump --username=%s --host=%s --port=%s --format=plain --file=%s %s',
                    escapeshellarg($config['password']),
                    escapeshellarg($config['username']),
                    escapeshellarg($config['host']),
                    escapeshellarg($config['port'] ?? '5432'),
                    escapeshellarg($backupPath),
                    escapeshellarg($config['database'])
                );
                break;
                
            default:
                throw new \Exception('Unsupported database connection: ' . $connection);
        }
        
        return $command;
    }

    /**
     * Create a safety backup before restore.
     */
    private function createSafetyBackup()
    {
        $timestamp = Carbon::now()->format('Y-m-d_H-i-s');
        $filename = "safety_backup_before_restore_{$timestamp}.sql";
        
        $command = $this->generateBackupCommand($filename);
        $output = [];
        $returnCode = 0;
        
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0) {
            return $filename;
        }
        
        return null;
    }

    /**
     * Parse SQL statements from backup file.
     */
    private function parseSqlStatements($sql)
    {
        // Remove comments and split by semicolons
        $sql = preg_replace('/--.*$/m', '', $sql);
        $statements = explode(';', $sql);
        
        return array_filter(array_map('trim', $statements));
    }

    /**
     * Format bytes to human readable format.
     */
    private function formatBytes($size, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, $precision) . ' ' . $units[$i];
    }

    /**
     * Log audit actions.
     */
    private function logAction($action, $recordId, $tableName, $data = [])
    {
        try {
            \App\Models\AuditLog::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'table_name' => $tableName,
                'record_id' => $recordId,
                'old_values' => null,
                'new_values' => json_encode($data),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Silent fail for logging
        }
    }
}