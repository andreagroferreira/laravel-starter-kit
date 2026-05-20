<?php

declare(strict_types=1);

use App\Models\Customer;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\postJson;

it('logs in a customer with valid credentials and returns a token', function (): void {
    Customer::factory()->create([
        'email' => 'cust@wc.io',
        'password' => Hash::make('StrongPass!123#'),
    ]);

    $response = postJson('/api/v1/auth/login', [
        'email' => 'cust@wc.io',
        'password' => 'StrongPass!123#',
        'device_name' => 'iPhone 16',
    ]);

    $response->assertOk();
    $response->assertJsonStructure([
        'data' => [
            'customer' => ['id', 'name', 'email', 'role'],
            'token',
            'token_type',
        ],
    ]);
});

it('rejects invalid credentials with problem+json 401', function (): void {
    Customer::factory()->create([
        'email' => 'cust@wc.io',
        'password' => Hash::make('StrongPass!123#'),
    ]);

    $response = postJson('/api/v1/auth/login', [
        'email' => 'cust@wc.io',
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(401);
    expect($response->headers->get('Content-Type'))->toContain('application/problem+json');
    $response->assertJson([
        'type' => 'https://wizardingcode.io/errors/auth',
        'title' => 'Invalid Credentials',
        'status' => 401,
    ]);
});

it('validates required email and password on login', function (): void {
    postJson('/api/v1/auth/login', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email', 'password']);
});

it('rate-limits login to 5/min', function (): void {
    Customer::factory()->create([
        'email' => 'cust@wc.io',
        'password' => Hash::make('StrongPass!123#'),
    ]);

    foreach (range(1, 5) as $_) {
        postJson('/api/v1/auth/login', [
            'email' => 'cust@wc.io',
            'password' => 'wrong',
        ])->assertStatus(401);
    }

    postJson('/api/v1/auth/login', [
        'email' => 'cust@wc.io',
        'password' => 'wrong',
    ])->assertStatus(429);
});
