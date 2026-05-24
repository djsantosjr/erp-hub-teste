<?php

namespace App\Traits;

use App\Models\AuditLog;

trait AuditsChanges
{
    public static function bootAuditsChanges(): void
    {
        static::created(function ($model) {
            AuditLog::record(
                entity: class_basename($model),
                entityId: $model->id,
                action: 'criado',
                metadata: $model->toArray()
            );
        });

        static::updated(function ($model) {
            AuditLog::record(
                entity: class_basename($model),
                entityId: $model->id,
                action: 'atualizado',
                metadata: $model->getDirty()
            );
        });
    }
}