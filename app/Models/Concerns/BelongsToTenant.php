<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Models\Tenant;
use App\Support\CurrentTenant;
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
            $current = resolve(CurrentTenant::class);

            if (! $current->has()) {
                return;
            }

            $builder->where($builder->getModel()->qualifyColumn('tenant_id'), $current->id());
        });

        static::creating(function (Model $model): void {
            $current = resolve(CurrentTenant::class);

            if ($model->getAttribute('tenant_id') === null && $current->has()) {
                $model->setAttribute('tenant_id', $current->id());
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
