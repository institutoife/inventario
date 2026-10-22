<?php

namespace App\Services;

use App\Models\AuditLog;

class Audit
{
    public static function record(string $action, string $entity, ?int $id, array $old = [], array $new = [], ?string $reason = null, ?int $actorId = null): void
    {
        $redact = fn ($values) => array_diff_key($values, array_flip(['password', 'remember_token', 'password_confirmation']));
        AuditLog::create([
            'user_id' => $actorId ?? auth()->id(), 'action' => $action, 'entity_type' => $entity, 'entity_id' => $id,
            'old_values' => $redact($old), 'new_values' => $redact($new), 'reason' => $reason,
            'ip_address' => request()->ip(), 'user_agent' => substr((string) request()->userAgent(), 0, 2000),
        ]);
    }
}
