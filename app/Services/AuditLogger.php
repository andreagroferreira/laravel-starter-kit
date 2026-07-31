<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Tenant;
use App\Models\User;

final class AuditLogger
{
    /**
     * @param  array<string, mixed>|null  $payload
     */
    public static function record(
        Tenant $tenant,
        string $action,
        ?User $user = null,
        string $actorType = 'human',
        ?string $subjectType = null,
        ?string $subjectId = null,
        ?array $payload = null,
    ): AuditLog {
        return $tenant->auditLogs()->create([
            'user_id' => $user?->getKey(),
            'actor_type' => $actorType,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'payload' => $payload,
            'created_at' => now(),
        ]);
    }
}
