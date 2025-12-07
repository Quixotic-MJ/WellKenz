<?php

namespace App\Traits;

use App\Services\AuditLogHelper;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    public static function bootAuditable()
    {
        // Log Creates
        static::created(function ($model) {
            if (Auth::check()) {
                AuditLogHelper::logCreate(
                    $model->getTable(),
                    $model->getKey(),
                    $model->getAttributes()
                );
            }
        });

        // Log Updates (Automatic Diff)
        static::updated(function ($model) {
            if (Auth::check()) {
                // key = field name, value = new value
                $newValues = $model->getChanges(); 
                
                // Retrieve original values for changed fields only
                $oldValues = [];
                foreach (array_keys($newValues) as $key) {
                    $oldValues[$key] = $model->getOriginal($key);
                }

                // Ignore timestamp updates and redundant user tracking
                unset($newValues['updated_at'], $oldValues['updated_at']);
                unset($newValues['updated_by'], $oldValues['updated_by']);

                if (!empty($newValues)) {
                    AuditLogHelper::logUpdate(
                        $model->getTable(),
                        $model->getKey(),
                        $oldValues,
                        $newValues
                    );
                }
            }
        });

        // Log Deletes
        static::deleted(function ($model) {
            if (Auth::check()) {
                AuditLogHelper::logDelete(
                    $model->getTable(),
                    $model->getKey(),
                    $model->getAttributes()
                );
            }
        });
    }
}