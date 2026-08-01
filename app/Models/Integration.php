<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\IntegrationProvider;
use App\Models\Concerns\BelongsToTenant;
use Carbon\CarbonImmutable;
use Database\Factories\IntegrationFactory;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A third-party connection of a tenant (optionally scoped to one site).
 * Tokens are encrypted at rest.
 *
 * @property-read string $id
 * @property-read string $tenant_id
 * @property-read string|null $site_id
 * @property-read IntegrationProvider $provider
 * @property-read string|null $access_token
 * @property-read string|null $refresh_token
 * @property-read CarbonImmutable|null $expires_at
 * @property-read string|null $external_id
 * @property-read array<string, mixed>|null $meta
 * @property-read string $status
 * @property-read CarbonImmutable|null $connected_at
 */
#[Hidden(['access_token', 'refresh_token'])]
final class Integration extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<IntegrationFactory> */
    use HasFactory;

    use HasUuids;

    protected $guarded = [];

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'provider' => IntegrationProvider::class,
            'access_token' => 'encrypted',
            'refresh_token' => 'encrypted',
            'meta' => 'array',
            'expires_at' => 'immutable_datetime',
            'connected_at' => 'immutable_datetime',
        ];
    }
}
