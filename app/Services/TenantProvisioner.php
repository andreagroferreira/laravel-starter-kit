<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TenantRole;
use App\Models\Tenant;
use App\Support\RoleMatrix;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

final class TenantProvisioner
{
    /**
     * Create the tenant's roles and sync the permission matrix onto them.
     * Idempotent: safe to call on every registration, invite or deploy.
     */
    public function provision(Tenant $tenant): void
    {
        $previousTeamId = getPermissionsTeamId();

        setPermissionsTeamId($tenant);

        foreach (RoleMatrix::permissions() as $permission) {
            Permission::findOrCreate($permission);
        }

        foreach (TenantRole::cases() as $role) {
            Role::findOrCreate($role->value)->syncPermissions(RoleMatrix::permissionsFor($role));
        }

        setPermissionsTeamId($previousTeamId);
    }
}
