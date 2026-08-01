<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DeploymentStatus;
use App\Models\Concerns\BelongsToTenant;
use Carbon\CarbonImmutable;
use Database\Factories\DeploymentFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One attempt to make a published version reachable on the edge:
 * a cache purge for content, a domain provision for the first publish.
 *
 * @property-read string $id
 * @property-read string $tenant_id
 * @property-read string $site_id
 * @property-read string|null $site_version_id
 * @property-read string $type
 * @property-read DeploymentStatus $status
 * @property-read string|null $url
 * @property-read string|null $error
 * @property-read array<string, mixed>|null $meta
 * @property-read CarbonImmutable|null $finished_at
 * @property-read CarbonImmutable $created_at
 * @property-read Site $site
 */
final class Deployment extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<DeploymentFactory> */
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

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'status' => DeploymentStatus::class,
            'meta' => 'array',
            'finished_at' => 'immutable_datetime',
        ];
    }
}
