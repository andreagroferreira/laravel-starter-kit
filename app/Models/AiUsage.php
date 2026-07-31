<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Carbon\CarbonInterface;
use Database\Factories\AiUsageFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read string $id
 * @property-read string $tenant_id
 * @property-read string|null $user_id
 * @property-read string $agent
 * @property-read string|null $provider
 * @property-read string|null $model
 * @property-read int $prompt_tokens
 * @property-read int $completion_tokens
 * @property-read int $credits
 * @property-read string|null $resource_type
 * @property-read string|null $resource_id
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class AiUsage extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<AiUsageFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'string',
            'tenant_id' => 'string',
            'user_id' => 'string',
            'agent' => 'string',
            'provider' => 'string',
            'model' => 'string',
            'prompt_tokens' => 'integer',
            'completion_tokens' => 'integer',
            'credits' => 'integer',
            'resource_type' => 'string',
            'resource_id' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
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
