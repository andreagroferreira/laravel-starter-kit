<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\seed;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    seed(RolePermissionSeeder::class);

    User::factory()->admin()->create([
        'email' => 'admin@wc.io',
        'password' => Hash::make('Wc!Strong#1'),
    ]);
});

it('shows the staff login page on mobile viewport', function (): void {
    visit('/admin/login')
        ->resize(375, 800)
        ->assertSee('Sign in')
        ->assertSee('Email')
        ->assertSee('Password');
})->skip('Pest browser requires Playwright; CI in Plan 5 enables.');

it('shows the staff login page on tablet viewport', function (): void {
    visit('/admin/login')
        ->resize(820, 1180)
        ->assertSee('Sign in');
})->skip('Pest browser requires Playwright; CI in Plan 5 enables.');

it('shows the staff login page on desktop viewport', function (): void {
    visit('/admin/login')
        ->resize(1440, 900)
        ->assertSee('Sign in');
})->skip('Pest browser requires Playwright; CI in Plan 5 enables.');

it('logs in a staff user with valid credentials (desktop)', function (): void {
    visit('/admin/login')
        ->resize(1440, 900)
        ->fill('input[name="email"]', 'admin@wc.io')
        ->fill('input[name="password"]', 'Wc!Strong#1')
        ->click('button[type="submit"]')
        ->wait(0.5)
        ->assertPathIs('/admin');
})->skip('Pest browser requires Playwright; CI in Plan 5 enables.');

it('shows an error message on invalid credentials (desktop)', function (): void {
    visit('/admin/login')
        ->resize(1440, 900)
        ->fill('input[name="email"]', 'admin@wc.io')
        ->fill('input[name="password"]', 'wrong-password')
        ->click('button[type="submit"]')
        ->wait(0.3)
        ->assertSee('credentials do not match');
})->skip('Pest browser requires Playwright; CI in Plan 5 enables.');
