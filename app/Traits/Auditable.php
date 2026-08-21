<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(fn (Model $model) => static::writeAuditLog($model, 'created', null, $model->getAttributes()));

        static::updated(function (Model $model) {
            $before = array_intersect_key($model->getOriginal(), $model->getChanges());
            $after = $model->getChanges();

            if (empty($after)) {
                return;
            }

            static::writeAuditLog($model, 'updated', $before, $after);
        });

        static::deleted(fn (Model $model) => static::writeAuditLog($model, 'deleted', $model->getAttributes(), null));
    }

    protected static function writeAuditLog(Model $model, string $action, ?array $before, ?array $after): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'entity_type' => $model->getMorphClass(),
            'entity_id' => $model->getKey(),
            'before' => $before,
            'after' => $after,
        ]);
    }
}
