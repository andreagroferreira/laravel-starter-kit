<?php

declare(strict_types=1);

namespace App\Queue\Middleware;

use App\Models\Tenant;
use App\Support\CurrentTenant;
use Closure;
use Spatie\Permission\PermissionRegistrar;

/**
 * Binds the tenant to the job's lifecycle and guarantees cleanup, keeping
 * long-running queue workers (Horizon/Octane) free of leaked tenant state.
 */
final readonly class TenantAware
{
    public function __construct(private string $tenantId) {}

    public function handle(object $job, Closure $next): mixed
    {
        $tenant = Tenant::query()->findOrFail($this->tenantId);
        $current = resolve(CurrentTenant::class);
        $current->set($tenant);
        setPermissionsTeamId($tenant);

        try {
            return $next($job);
        } finally {
            $current->forget();
            resolve(PermissionRegistrar::class)->setPermissionsTeamId(null);
        }
    }
}
