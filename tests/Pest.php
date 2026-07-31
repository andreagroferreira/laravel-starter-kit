<?php

declare(strict_types=1);

use App\Enums\TenantRole;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantProvisioner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Sleep;
use Illuminate\Support\Str;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(function (): void {
        Str::createRandomStringsNormally();
        Str::createUuidsNormally();
        Http::preventStrayRequests();
        Process::preventStrayProcesses();
        Sleep::fake();

        $this->freezeTime();
    })
    ->in('Browser', 'Feature', 'Unit');

expect()->extend('toBeOne', fn () => $this->toBe(1));

function something(): void
{
    // ..
}

/**
 * Provision the tenant's roles/permissions and grant the user a role.
 */
function grantRole(Tenant $tenant, User $user, TenantRole $role = TenantRole::Owner): void
{
    resolve(TenantProvisioner::class)->provision($tenant);
    setPermissionsTeamId($tenant->id);
    $user->syncRoles([$role]);
}
