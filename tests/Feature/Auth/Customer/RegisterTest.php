<?php

declare(strict_types=1);

use App\Models\Customer;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;

use function Pest\Laravel\postJson;

it('registers a customer and returns a Sanctum token', function (): void {
    Event::fake([Registered::class]);

    $response = postJson('/api/v1/auth/register', [
        'name' => 'Test User',
        'email' => 'test@wc.io',
        'password' => 'StrongPass!123#',
        'password_confirmation' => 'StrongPass!123#',
    ]);

    $response->assertCreated();
    $response->assertJsonStructure([
        'data' => [
            'customer' => ['id', 'name', 'email', 'role'],
            'token',
            'token_type',
        ],
    ]);

    expect(Customer::query()->where('email', 'test@wc.io')->exists())->toBeTrue();

    Event::assertDispatched(Registered::class);
});

it('rejects weak passwords on register', function (): void {
    postJson('/api/v1/auth/register', [
        'name' => 'Test User',
        'email' => 'test@wc.io',
        'password' => 'weak',
        'password_confirmation' => 'weak',
    ])->assertUnprocessable()->assertJsonValidationErrorFor('password');
});

it('rejects duplicate emails on register', function (): void {
    Customer::factory()->create(['email' => 'dup@wc.io']);

    postJson('/api/v1/auth/register', [
        'name' => 'Test User',
        'email' => 'dup@wc.io',
        'password' => 'StrongPass!123#',
        'password_confirmation' => 'StrongPass!123#',
    ])->assertUnprocessable()->assertJsonValidationErrorFor('email');
});

it('rate-limits registration to 5/min', function (): void {
    foreach (range(1, 5) as $i) {
        postJson('/api/v1/auth/register', [
            'name' => "User {$i}",
            'email' => "test{$i}@wc.io",
            'password' => 'StrongPass!123#',
            'password_confirmation' => 'StrongPass!123#',
        ])->assertCreated();
    }

    postJson('/api/v1/auth/register', [
        'name' => 'Sixth',
        'email' => 'sixth@wc.io',
        'password' => 'StrongPass!123#',
        'password_confirmation' => 'StrongPass!123#',
    ])->assertStatus(429);
});
