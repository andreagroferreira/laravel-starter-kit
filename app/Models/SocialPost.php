<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Carbon\CarbonImmutable;
use Database\Factories\SocialPostFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A post scheduled for a social network.
 *
 * @property-read string $id
 * @property-read string $site_id
 * @property-read string $network
 * @property-read string $content
 * @property-read array<int, string>|null $media
 * @property-read CarbonImmutable|null $scheduled_at
 * @property-read CarbonImmutable|null $published_at
 * @property-read string $status
 * @property-read string|null $external_id
 * @property-read string|null $error
 */
final class SocialPost extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<SocialPostFactory> */
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
     * @param  Builder<self>  $query
     */
    public function scopeDue(Builder $query): void
    {
        $query->where('status', 'scheduled')->where('scheduled_at', '<=', now());
    }

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'media' => 'array',
            'scheduled_at' => 'immutable_datetime',
            'published_at' => 'immutable_datetime',
        ];
    }
}
