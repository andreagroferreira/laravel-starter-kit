<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\IntegrationProvider;
use App\Models\Concerns\BelongsToTenant;
use Carbon\CarbonImmutable;
use Database\Factories\MetricSnapshotFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One day of metrics for a site and provider.
 *
 * @property-read string $id
 * @property-read string $site_id
 * @property-read Site $site
 * @property-read IntegrationProvider $provider
 * @property-read CarbonImmutable $metric_date
 * @property-read array<string, mixed> $metrics
 */
final class MetricSnapshot extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<MetricSnapshotFactory> */
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
            'provider' => IntegrationProvider::class,
            'metric_date' => 'immutable_date',
            'metrics' => 'array',
        ];
    }
}
