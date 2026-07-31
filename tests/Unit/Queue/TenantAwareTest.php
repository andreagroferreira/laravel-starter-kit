<?php

declare(strict_types=1);

use App\Listeners\FlushTenantState;
use App\Models\Tenant;
use App\Queue\Middleware\TenantAware;
use App\Support\CurrentTenant;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Spatie\Permission\PermissionRegistrar;

it('binds the tenant during the job and cleans up afterwards', function (): void {
    $tenant = Tenant::factory()->create();
    $middleware = new TenantAware($tenant->id);

    $result = $middleware->handle(new stdClass, function (object $job) use ($tenant): string {
        expect(resolve(CurrentTenant::class)->id())->toBe($tenant->id)
            ->and(getPermissionsTeamId())->toBe($tenant->id);

        return 'done';
    });

    expect($result)->toBe('done')
        ->and(resolve(CurrentTenant::class)->has())->toBeFalse()
        ->and(getPermissionsTeamId())->toBeNull();
});

it('cleans up even when the job throws', function (): void {
    $tenant = Tenant::factory()->create();
    $middleware = new TenantAware($tenant->id);

    expect(fn (): mixed => $middleware->handle(new stdClass, function (): void {
        throw new RuntimeException('job failed');
    }))->toThrow(RuntimeException::class, 'job failed')
        ->and(resolve(CurrentTenant::class)->has())->toBeFalse()
        ->and(getPermissionsTeamId())->toBeNull();
});

it('rejects jobs for tenants that no longer exist', function (): void {
    $middleware = new TenantAware('00000000-0000-0000-0000-000000000000');

    $middleware->handle(new stdClass, fn (object $job): null => null);
})->throws(ModelNotFoundException::class);

it('flushes the permission registrar team id between octane operations', function (): void {
    $tenant = Tenant::factory()->create();
    setPermissionsTeamId($tenant);

    expect(getPermissionsTeamId())->toBe($tenant->id);

    (new FlushTenantState)->handle();

    expect(resolve(PermissionRegistrar::class)->getPermissionsTeamId())->toBeNull();
});
