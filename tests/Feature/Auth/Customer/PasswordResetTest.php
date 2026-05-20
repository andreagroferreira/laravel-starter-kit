<?php

declare(strict_types=1);

use App\Models\Customer;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

use function Pest\Laravel\postJson;

it('sends a password reset link to a registered customer', function (): void {
    Notification::fake();
    $customer = Customer::factory()->create(['email' => 'cust@wc.io']);

    postJson('/api/v1/auth/password/forgot', ['email' => 'cust@wc.io'])
        ->assertOk()
        ->assertJsonStructure(['status']);

    Notification::assertSentTo($customer, ResetPassword::class);
});

it('rejects forgot-password for unknown emails', function (): void {
    postJson('/api/v1/auth/password/forgot', ['email' => 'ghost@wc.io'])
        ->assertUnprocessable()
        ->assertJsonValidationErrorFor('email');
});

it('resets the password with a valid token', function (): void {
    $customer = Customer::factory()->create([
        'email' => 'cust@wc.io',
        'password' => Hash::make('OldPass!123#abc'),
    ]);

    $token = Password::broker('customers')->createToken($customer);

    postJson('/api/v1/auth/password/reset', [
        'token' => $token,
        'email' => 'cust@wc.io',
        'password' => 'NewStrong!123#',
        'password_confirmation' => 'NewStrong!123#',
    ])->assertOk();

    $customer->refresh();
    expect(Hash::check('NewStrong!123#', (string) $customer->password))->toBeTrue();
});

it('rejects password reset with an invalid token', function (): void {
    Customer::factory()->create(['email' => 'cust@wc.io']);

    postJson('/api/v1/auth/password/reset', [
        'token' => 'invalid-token',
        'email' => 'cust@wc.io',
        'password' => 'NewStrong!123#',
        'password_confirmation' => 'NewStrong!123#',
    ])->assertStatus(400);
});

it('rate-limits forgot password to 3/min', function (): void {
    Customer::factory()->create(['email' => 'cust@wc.io']);
    Notification::fake();

    foreach (range(1, 3) as $_) {
        postJson('/api/v1/auth/password/forgot', ['email' => 'cust@wc.io'])->assertOk();
    }

    postJson('/api/v1/auth/password/forgot', ['email' => 'cust@wc.io'])->assertStatus(429);
});
