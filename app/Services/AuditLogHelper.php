<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogHelper
{
    /**
     * Create an audit log entry with consistent formatting
     *
     * @param string $tableName
     * @param int $recordId
     * @param string $action
     * @param array|null $oldValues
     * @param array|null $newValues
     * @param Request|null $request
     * @param array $metadata
     * @return AuditLog
     */
    public static function log(
        string $tableName,
        int $recordId,
        string $action,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?Request $request = null,
        array $metadata = []
    ): AuditLog {
        // Prepare the data for audit log
        $auditData = [
            'table_name' => $tableName,
            'record_id' => $recordId,
            'action' => strtoupper($action),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'user_id' => Auth::id(),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ];

        // Add metadata if provided
        if (!empty($metadata)) {
            $auditData['metadata'] = json_encode($metadata);
        }

        // Handle old_values - ensure it's properly formatted
        if ($oldValues !== null) {
            $auditData['old_values'] = self::sanitizeValues($oldValues);
        }

        // Handle new_values - ensure it's properly formatted
        if ($newValues !== null) {
            $auditData['new_values'] = self::sanitizeValues($newValues);
        }

        return AuditLog::create($auditData);
    }

    /**
     * Log a CREATE operation
     */
    public static function logCreate(
        string $tableName,
        int $recordId,
        array $newValues,
        ?Request $request = null,
        array $metadata = []
    ): AuditLog {
        return self::log($tableName, $recordId, 'CREATE', null, $newValues, $request, $metadata);
    }

    /**
     * Log an UPDATE operation
     */
    public static function logUpdate(
        string $tableName,
        int $recordId,
        array $oldValues,
        array $newValues,
        ?Request $request = null,
        array $metadata = []
    ): AuditLog {
        return self::log($tableName, $recordId, 'UPDATE', $oldValues, $newValues, $request, $metadata);
    }

    /**
     * Log a DELETE operation
     */
    public static function logDelete(
        string $tableName,
        int $recordId,
        array $oldValues,
        ?Request $request = null,
        array $metadata = []
    ): AuditLog {
        return self::log($tableName, $recordId, 'DELETE', $oldValues, null, $request, $metadata);
    }

    /**
     * Log a generic action (for custom actions like login, logout, etc.)
     */
    public static function logAction(
        string $tableName,
        int $recordId,
        string $action,
        ?array $values = null,
        ?Request $request = null,
        array $metadata = []
    ): AuditLog {
        $oldValues = null;
        $newValues = null;

        // For some actions, values go to new_values (like login)
        // For others, they might go to old_values or be split
        if ($values !== null) {
            if (in_array($action, ['LOGIN', 'LOGIN_FAILED', 'LOGOUT', 'backup'])) {
                $newValues = $values;
            } else {
                $newValues = $values;
            }
        }

        return self::log($tableName, $recordId, $action, $oldValues, $newValues, $request, $metadata);
    }

    /**
     * Extract old values from a model before update
     */
    public static function extractOldValues($model, array $updatedFields = null): array
    {
        $original = $model->getOriginal();
        
        // If specific fields are requested, filter them
        if ($updatedFields !== null) {
            $original = array_intersect_key($original, array_flip($updatedFields));
        }

        // Remove Laravel internal fields
        unset($original['created_at'], $original['updated_at']);
        
        return self::sanitizeValues($original);
    }

    /**
     * Extract new values from model or array
     */
    public static function extractNewValues($modelOrArray, array $updatedFields = null): array
    {
        if (is_array($modelOrArray)) {
            $newValues = $modelOrArray;
        } else {
            $newValues = $modelOrArray->toArray();
        }

        // If specific fields are requested, filter them
        if ($updatedFields !== null) {
            $newValues = array_intersect_key($newValues, array_flip($updatedFields));
        }

        // Remove Laravel internal fields
        unset($newValues['created_at'], $newValues['updated_at']);
        
        return self::sanitizeValues($newValues);
    }

    /**
     * Sanitize values for audit logging
     * Removes sensitive information and normalizes data
     */
    private static function sanitizeValues(array $values): array
    {
        $sanitized = $values;

        // Remove sensitive fields that shouldn't be logged
        $sensitiveFields = [
            'password', 
            'password_hash', 
            'remember_token', 
            'api_key', 
            'secret',
            'token'
        ];

        foreach ($sensitiveFields as $field) {
            if (array_key_exists($field, $sanitized)) {
                $sanitized[$field] = '[REDACTED]';
            }
        }

        // Convert null values to empty strings for consistency
        foreach ($sanitized as $key => $value) {
            if ($value === null) {
                $sanitized[$key] = '';
            }
        }

        return $sanitized;
    }

    /**
     * Get the difference between old and new values
     */
    public static function getChangedFields(array $oldValues, array $newValues): array
    {
        $changed = [];
        
        foreach ($newValues as $key => $newValue) {
            $oldValue = $oldValues[$key] ?? null;
            
            if ($oldValue != $newValue) {
                $changed[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue
                ];
            }
        }

        return $changed;
    }
}