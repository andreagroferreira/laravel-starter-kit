<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Laravel\Cashier\Billable;

/**
 * @property-read string $id
 * @property-read string $name
 * @property-read string $slug
 * @property-read string $plan
 * @property-read int $ai_credits_monthly
 * @property-read array<string, mixed>|null $settings
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class Tenant extends Model
{
    use Billable;

    /** @use HasFactory<TenantFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'string',
            'name' => 'string',
            'slug' => 'string',
            'plan' => 'string',
            'ai_credits_monthly' => 'integer',
            'settings' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * @return HasOne<BrandProfile, $this>
     */
    public function brandProfile(): HasOne
    {
        return $this->hasOne(BrandProfile::class);
    }

    /**
     * @return HasMany<BrandProfile, $this>
     */
    public function brandProfiles(): HasMany
    {
        return $this->hasMany(BrandProfile::class);
    }

    /**
     * @return HasMany<AiUsage, $this>
     */
    public function aiUsages(): HasMany
    {
        return $this->hasMany(AiUsage::class);
    }

    /**
     * @return HasMany<AuditLog, $this>
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * @return HasMany<Site, $this>
     */
    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }
}
