<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AiGenerationStatus;
use App\Models\Concerns\BelongsToTenant;
use Carbon\CarbonImmutable;
use Database\Factories\AiGenerationFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read string $id
 * @property-read string $tenant_id
 * @property-read string|null $user_id
 * @property-read string|null $site_id
 * @property-read string $agent
 * @property-read AiGenerationStatus $status
 * @property-read array<string, mixed> $input
 * @property-read array<string, mixed>|null $output
 * @property-read string|null $error
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 */
final class AiGeneration extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<AiGenerationFactory> */
    use HasFactory;

    use HasUuids;

    protected $guarded = [];

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'status' => AiGenerationStatus::class,
            'input' => 'array',
            'output' => 'array',
        ];
    }
}
