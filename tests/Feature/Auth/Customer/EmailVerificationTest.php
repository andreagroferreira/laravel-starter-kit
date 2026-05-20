<?php

declare(strict_types=1);

use App\Models\Customer;
use Illuminate\Support\Facades\URL;

use function Pest\Laravel\getJson;

it('verifies a customer email with a valid signed URL', function (): void {
    $customer = Customer::factory()->unverified()->create();

    $url = URL::temporarySignedRoute(
        'api.v1.auth.verify-email',
        now()->addMinutes(60),
        ['id' => $customer->id, 'hash' => sha1($customer->getEmailForVerification())],
    );

    getJson($url)->assertOk()->assertJson(['status' => 'verified']);

    expect($customer->refresh()->hasVerifiedEmail())->toBeTrue();
});

it('rejects email verification with an invalid hash', function (): void {
    $customer = Customer::factory()->unverified()->create();

    $url = URL::temporarySignedRoute(
        'api.v1.auth.verify-email',
        now()->addMinutes(60),
        ['id' => $customer->id, 'hash' => sha1('wrong-email@example.com')],
    );

    getJson($url)->assertStatus(403)->assertJson(['status' => 'invalid_link']);
});

it('rejects email verification with an invalid signature', function (): void {
    $customer = Customer::factory()->unverified()->create();

    $hash = sha1($customer->getEmailForVerification());

    getJson("/api/v1/auth/verify-email/{$customer->id}/{$hash}")->assertStatus(403);
});

it('returns already_verified when the email is already verified', function (): void {
    $customer = Customer::factory()->create();

    $url = URL::temporarySignedRoute(
        'api.v1.auth.verify-email',
        now()->addMinutes(60),
        ['id' => $customer->id, 'hash' => sha1($customer->getEmailForVerification())],
    );

    getJson($url)->assertOk()->assertJson(['status' => 'already_verified']);
});
