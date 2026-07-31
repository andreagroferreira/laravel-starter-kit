<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ContentStatus;
use Carbon\CarbonInterface;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property-read string $id
 * @property-read string $site_id
 * @property-read Site $site
 * @property-read string|null $author_id
 * @property-read string $title
 * @property-read string $slug
 * @property-read ContentStatus $status
 * @property-read string|null $excerpt
 * @property-read string|null $body
 * @property-read string|null $cover_image_path
 * @property-read array<string, mixed>|null $seo
 * @property-read CarbonInterface|null $published_at
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    use HasUuids;
    use Prunable;
    use SoftDeletes;

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'string',
            'site_id' => 'string',
            'author_id' => 'string',
            'title' => 'string',
            'slug' => 'string',
            'status' => ContentStatus::class,
            'excerpt' => 'string',
            'body' => 'string',
            'cover_image_path' => 'string',
            'seo' => 'array',
            'published_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * @return BelongsToMany<Category, $this>
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopeLive(Builder $query): Builder
    {
        return $query->where('status', ContentStatus::Published)
            ->where(fn (Builder $q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    /**
     * Soft-deleted content is permanently removed after 30 days.
     *
     * @return Builder<static>
     */
    public function prunable(): Builder
    {
        return self::query()->withoutGlobalScopes()->onlyTrashed()->where('deleted_at', '<=', now()->subDays(30));
    }
}
