<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SiteType;
use App\Models\Concerns\BelongsToTenant;
use Carbon\CarbonInterface;
use Database\Factories\SiteFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property-read string $id
 * @property-read string $tenant_id
 * @property-read Tenant $tenant
 * @property-read string $name
 * @property-read string $slug
 * @property-read SiteType $type
 * @property-read string|null $domain
 * @property-read string $status
 * @property-read string|null $renderer_version
 * @property-read array<string, mixed>|null $settings
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class Site extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<SiteFactory> */
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
            'tenant_id' => 'string',
            'name' => 'string',
            'slug' => 'string',
            'type' => SiteType::class,
            'domain' => 'string',
            'status' => 'string',
            'renderer_version' => 'string',
            'settings' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<Page, $this>
     */
    public function pages(): HasMany
    {
        return $this->hasMany(Page::class);
    }

    /**
     * @return HasMany<SiteVersion, $this>
     */
    public function versions(): HasMany
    {
        return $this->hasMany(SiteVersion::class);
    }

    /**
     * @return HasOne<SiteVersion, $this>
     */
    public function publishedVersion(): HasOne
    {
        return $this->hasOne(SiteVersion::class)->whereNotNull('published_at')->latest('published_at');
    }

    /**
     * @return HasMany<Menu, $this>
     */
    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }

    /**
     * @return HasMany<Form, $this>
     */
    public function forms(): HasMany
    {
        return $this->hasMany(Form::class);
    }

    /**
     * @return HasMany<Redirect, $this>
     */
    public function redirects(): HasMany
    {
        return $this->hasMany(Redirect::class);
    }

    /**
     * @return HasMany<Post, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * @return HasMany<Category, $this>
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
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
