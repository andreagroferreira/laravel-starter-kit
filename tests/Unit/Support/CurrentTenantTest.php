<?php

declare(strict_types=1);

use App\Exceptions\TenantNotResolved;
use App\Models\Tenant;
use App\Support\CurrentTenant;

it('holds and clears the tenant for the current lifecycle', function (): void {
    $tenant = Tenant::factory()->create();
    $current = new CurrentTenant;

    expect($current->has())->toBeFalse()
        ->and($current->get())->toBeNull()
        ->and($current->id())->toBeNull();

    $current->set($tenant);

    expect($current->has())->toBeTrue()
        ->and($current->get())->toBe($tenant)
        ->and($current->getOrFail())->toBe($tenant)
        ->and($current->id())->toBe($tenant->id);

    $current->forget();

    expect($current->has())->toBeFalse()
        ->and($current->get())->toBeNull();
});

it('fails loudly when no tenant is bound', function (): void {
    (new CurrentTenant)->getOrFail();
})->throws(TenantNotResolved::class, 'No tenant is bound to the current lifecycle.');

it('does not leak the tenant across container lifecycles', function (): void {
    $tenant = Tenant::factory()->create();

    resolve(CurrentTenant::class)->set($tenant);

    expect(resolve(CurrentTenant::class)->has())->toBeTrue();

    // Octane flushes scoped instances between requests; a new lifecycle
    // must start without any tenant bound.
    app()->forgetScopedInstances();

    expect(resolve(CurrentTenant::class)->has())->toBeFalse();
});
