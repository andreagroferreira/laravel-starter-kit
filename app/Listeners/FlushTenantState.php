<?php

declare(strict_types=1);

namespace App\Listeners;

use Spatie\Permission\PermissionRegistrar;

/**
 * Defensive reset between Octane operations: scoped instances are flushed
 * by Octane itself, but the Spatie PermissionRegistrar team id lives in a
 * singleton and must be cleared explicitly.
 */
final class FlushTenantState
{
    public function handle(): void
    {
        resolve(PermissionRegistrar::class)->setPermissionsTeamId(null);
    }
}
