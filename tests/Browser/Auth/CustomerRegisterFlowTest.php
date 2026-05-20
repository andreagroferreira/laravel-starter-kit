<?php

declare(strict_types=1);

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| Customer Register — Browser-suite shim.
|
| Customer auth in Plan 2 is API-only (mobile/SPA consumers). There is no
| Inertia register page for Customers, so this file exercises the JSON
| endpoint inside the Browser suite to keep the auth flow coverage map
| symmetrical with StaffLoginFlow. A real browser variant will land in
| Plan 3 if/when a Customer-facing web UI is introduced.
|--------------------------------------------------------------------------
*/

it('registers a customer via API and returns a Sanctum token', function (): void {
    $response = postJson('/api/v1/auth/register', [
        'name' => 'New Customer',
        'email' => 'new@customer.com',
        'password' => 'CustomerPass!1234',
        'password_confirmation' => 'CustomerPass!1234',
    ]);

    $response->assertCreated();
    $response->assertJsonStructure([
        'data' => [
            'customer' => ['id', 'name', 'email'],
            'token',
            'token_type',
        ],
    ]);

    expect($response->json('data.token'))->toBeString();
    expect(Customer::query()->where('email', 'new@customer.com')->exists())->toBeTrue();
});

it('rejects customer register with mismatched password confirmation', function (): void {
    postJson('/api/v1/auth/register', [
        'name' => 'New Customer',
        'email' => 'mismatch@customer.com',
        'password' => 'CustomerPass!1234',
        'password_confirmation' => 'Different!1234',
    ])->assertUnprocessable()->assertJsonValidationErrorFor('password');
});
