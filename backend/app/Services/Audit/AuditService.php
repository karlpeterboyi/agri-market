<?php

namespace App\Services\Audit;

use App\Models\AuditLog;

class AuditService
{
    public static function log(
        $model,
        string $event,
        string $action,
        array $old = [],
        array $new = [],
        array $metadata = []
    ): void {

        AuditLog::create([

            'organisation_id' => $model->organisation_id ?? null,

            'user_id' => auth()->id(),

            'auditable_type' => get_class($model),

            'auditable_id' => $model->id,

            'event' => $event,

            'action' => $action,

            'old_values' => $old,

            'new_values' => $new,

            'metadata' => $metadata,

            'ip_address' => request()->ip(),

            'user_agent' => request()->userAgent(),

        ]);

    }
}