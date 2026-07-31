<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin Model
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder): void {
            if (! app()->bound(Tenant::class)) {
                return;
            }

            $builder->where($builder->getModel()->qualifyColumn('tenant_id'), resolve(Tenant::class)->getKey());
        });

        static::creating(function (Model $model): void {
            if ($model->getAttribute('tenant_id') === null && app()->bound(Tenant::class)) {
                $model->setAttribute('tenant_id', resolve(Tenant::class)->getKey());
            }
        });
    }

    /**
     * @return BelongsTo<Tenant, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
