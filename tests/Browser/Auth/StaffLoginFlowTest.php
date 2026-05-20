<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('renders the staff login page', function (): void {
    $page = visit('/admin/login');

    $page->assertSee('Sign in')
        ->assertSee('Email')
        ->assertSee('Password');
});

it('logs in staff with valid credentials', function (): void {
    User::factory()->create([
        'email' => 'staff@wc.io',
        'password' => Hash::make('Wc!Strong#1'),
    ]);

    $page = visit('/admin/login');

    $page->fill('input[name="email"]', 'staff@wc.io')
        ->fill('input[name="password"]', 'Wc!Strong#1')
        ->click('Sign in')
        ->assertPathIs('/admin');
});
