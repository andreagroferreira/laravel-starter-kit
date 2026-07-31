<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Carbon\CarbonInterface;
use Database\Factories\AuditLogFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read string $id
 * @property-read string $tenant_id
 * @property-read string|null $user_id
 * @property-read string $actor_type
 * @property-read string $action
 * @property-read string|null $subject_type
 * @property-read string|null $subject_id
 * @property-read array<string, mixed>|null $payload
 * @property-read CarbonInterface $created_at
 */
final class AuditLog extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<AuditLogFactory> */
    use HasFactory;

    use HasUuids;

    public const UPDATED_AT = null;

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'string',
            'tenant_id' => 'string',
            'user_id' => 'string',
            'actor_type' => 'string',
            'action' => 'string',
            'subject_type' => 'string',
            'subject_id' => 'string',
            'payload' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
