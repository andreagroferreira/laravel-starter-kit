<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('to array', function (): void {
    $user = User::factory()->create()->refresh();

    // Two-factor fields (two_factor_secret, two_factor_recovery_codes) are hidden;
    // two_factor_confirmed_at remains visible so the UI can render confirmation state.
    expect(array_keys($user->toArray()))
        ->toBe([
            'id',
            'name',
            'email',
            'email_verified_at',
            'created_at',
            'updated_at',
            'two_factor_confirmed_at',
        ]);
});

it('has fillable attributes', function (): void {
    expect((new User)->getFillable())->toBe(['name', 'email', 'password']);
});

it('hides 2FA secret and recovery codes when serialized', function (): void {
    $user = User::factory()->withTwoFactorEnabled()->create();

    $array = $user->toArray();

    expect($array)->not->toHaveKey('two_factor_secret')
        ->and($array)->not->toHaveKey('two_factor_recovery_codes')
        ->and($array)->not->toHaveKey('password')
        ->and($array)->not->toHaveKey('remember_token');
});

it('admin state assigns super-admin role', function (): void {
    (new RolePermissionSeeder)->run();

    $user = User::factory()->admin()->create();

    expect($user->hasRole('super-admin'))->toBeTrue();
});
