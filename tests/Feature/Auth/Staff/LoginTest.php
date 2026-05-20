<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\post;

uses(RefreshDatabase::class);

it('rate limits login attempts (5/min)', function (): void {
    User::factory()->create([
        'email' => 'test@wc.io',
        'password' => Hash::make('correct-password'),
    ]);

    foreach (range(1, 5) as $_) {
        post('/admin/login', [
            'email' => 'test@wc.io',
            'password' => 'wrong',
        ])->assertStatus(302);
    }

    post('/admin/login', [
        'email' => 'test@wc.io',
        'password' => 'wrong',
    ])->assertStatus(429);
});

it('authenticates a staff user with correct credentials', function (): void {
    User::factory()->create([
        'email' => 'staff@wc.io',
        'password' => Hash::make('Wc!Strong#1'),
    ]);

    post('/admin/login', [
        'email' => 'staff@wc.io',
        'password' => 'Wc!Strong#1',
    ])->assertRedirect('/admin');
});
