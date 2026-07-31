<?php

declare(strict_types=1);

use App\Enums\TenantRole;
use App\Models\Tenant;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;

it('renders the login page for guests', function (): void {
    $this->get('/login')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page->component('Auth/Login'));
});

it('renders the register page for guests', function (): void {
    $this->get('/register')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page->component('Auth/Register'));
});

it('redirects guests to login when hitting the backoffice', function (): void {
    $this->get('/')->assertRedirect('/login');
});

it('registers a user with a personal tenant and owner role', function (): void {
    $response = $this->post('/register', [
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $user = User::query()->where('email', 'ada@example.com')->firstOrFail();
    $tenant = $user->tenants()->sole();

    expect($tenant)->toBeInstanceOf(Tenant::class)
        ->and($user->current_tenant_id)->toBe($tenant->id);

    setPermissionsTeamId($tenant->id);
    expect($user->hasRole(TenantRole::Owner->value))->toBeTrue();

    foreach (TenantRole::cases() as $role) {
        expect(Role::query()->where('name', $role->value)->where('tenant_id', $tenant->id)->exists())->toBeTrue();
    }

    $response->assertRedirect();
});

it('logs in an existing user', function (): void {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect();

    $this->assertAuthenticatedAs($user);
});

it('rejects invalid credentials', function (): void {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('logs out', function (): void {
    $this->actingAs(User::factory()->create());

    $this->post('/logout')->assertRedirect('/');

    $this->assertGuest();
});

it('blocks authenticated users that belong to no workspace', function (): void {
    $this->actingAs(User::factory()->create())
        ->get('/')
        ->assertForbidden();
});

it('shares the authenticated user with inertia', function (): void {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(['current_tenant_id' => $tenant->id]);
    $tenant->users()->attach($user);

    $this->actingAs($user)
        ->get('/')
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('auth.user.id', $user->id)
            ->where('auth.user.email', $user->email)
        );
});
