<?php

declare(strict_types=1);

use App\Models\BrandProfile;
use App\Models\Tenant;
use App\Models\User;

it('creates a tenant with members', function (): void {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create();

    $tenant->users()->attach($user);

    expect($tenant->users)->toHaveCount(1)
        ->and($user->tenants)->toHaveCount(1);
});

it('sets the current tenant for a user', function (): void {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(['current_tenant_id' => $tenant->id]);

    expect($user->currentTenant->id)->toBe($tenant->id);
});

it('creates a brand profile for a tenant', function (): void {
    $tenant = Tenant::factory()->create();
    $profile = BrandProfile::factory()->for($tenant)->create();

    expect($profile->tenant->id)->toBe($tenant->id)
        ->and($tenant->brandProfile->id)->toBe($profile->id);
});

it('uses uuids as primary keys', function (): void {
    $tenant = Tenant::factory()->create();

    expect($tenant->id)->toBeUuid();
});
