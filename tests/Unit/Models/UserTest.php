<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('to array', function (): void {
    $user = User::factory()->create()->refresh();

    // Note: Fortify (Laravel\Fortify) appends two_factor_secret, two_factor_recovery_codes,
    // and two_factor_confirmed_at to the User attributes once two-factor is enabled in
    // config/fortify.php. They remain serialized until Plan 2 wires the dual auth UI.
    expect(array_keys($user->toArray()))
        ->toBe([
            'id',
            'name',
            'email',
            'email_verified_at',
            'created_at',
            'updated_at',
            'two_factor_secret',
            'two_factor_recovery_codes',
            'two_factor_confirmed_at',
        ]);
});
