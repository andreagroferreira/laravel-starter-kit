<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;

use Spatie\Activitylog\Models\Activity;

beforeEach(function (): void {
    (new RolePermissionSeeder)->run();
});

it('blocks non-admins from impersonating', function (): void {
    $viewer = User::factory()->create();
    $viewer->assignRole('viewer');
    $target = User::factory()->create();

    actingAs($viewer, 'web')
        ->post("/admin/impersonate/{$target->id}")
        ->assertForbidden();
});

it('blocks impersonation of self', function (): void {
    $admin = User::factory()->admin()->create();

    actingAs($admin, 'web')
        ->post("/admin/impersonate/{$admin->id}")
        ->assertForbidden();
});

it('blocks impersonation of super-admins', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $admin->givePermissionTo('impersonate users');

    $target = User::factory()->admin()->create();

    actingAs($admin, 'web')
        ->post("/admin/impersonate/{$target->id}")
        ->assertForbidden();
});

it('allows super-admin to impersonate non-super-admin', function (): void {
    $admin = User::factory()->admin()->create();
    $target = User::factory()->create();

    actingAs($admin, 'web')
        ->post("/admin/impersonate/{$target->id}")
        ->assertRedirect('/admin');

    expect(auth('web')->user()?->id)->toBe($target->id);
    expect(session('impersonator_id'))->toBe($admin->id);
});

it('logs impersonation start to activity log', function (): void {
    $admin = User::factory()->admin()->create();
    $target = User::factory()->create();

    actingAs($admin, 'web')->post("/admin/impersonate/{$target->id}");

    $activity = Activity::query()->where('log_name', 'staff-impersonate')->latest('id')->first();
    expect($activity)->not->toBeNull();
    expect($activity?->description)->toBe('Started impersonation');
});

it('allows the impersonated session to leave via DELETE', function (): void {
    $admin = User::factory()->admin()->create();
    $target = User::factory()->create();

    actingAs($admin, 'web')->post("/admin/impersonate/{$target->id}");

    delete('/admin/impersonate')->assertRedirect('/admin');
    expect(auth('web')->user()?->id)->toBe($admin->id);
    expect(session('impersonator_id'))->toBeNull();
});
