<?php

declare(strict_types=1);

use App\Models\Customer;
use App\Models\CustomerSocialAccount;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

beforeEach(function (): void {
    config([
        'services.google.client_id' => 'fake-client-id',
        'services.google.client_secret' => 'fake-secret',
        'services.google.redirect' => 'http://localhost/api/v1/auth/social/google/callback',
    ]);
});

function makeSocialiteUser(string $id, string $email, string $name = 'Test User'): SocialiteUser
{
    $user = new SocialiteUser;
    $user->id = $id;
    $user->name = $name;
    $user->email = $email;
    $user->nickname = $name;
    $user->avatar = 'https://lh3.googleusercontent.com/a/' . $id;
    $user->token = 'access-token-' . $id;
    $user->refreshToken = 'refresh-token-' . $id;
    $user->expiresIn = 3600;

    return $user;
}

it('returns Google redirect URL', function (): void {
    Socialite::fake('google', makeSocialiteUser('123', 'redirect@google.com'));

    $response = getJson('/api/v1/auth/social/google');

    $response->assertOk()->assertJsonStructure(['redirect_url']);
});

it('rejects unknown provider on redirect', function (): void {
    getJson('/api/v1/auth/social/twitter')->assertUnprocessable();
});

it('rejects unknown provider on callback', function (): void {
    postJson('/api/v1/auth/social/twitter/callback')->assertUnprocessable();
});

it('creates a new customer on first social callback', function (): void {
    Socialite::fake('google', makeSocialiteUser('123', 'test@google.com'));

    $response = postJson('/api/v1/auth/social/google/callback');

    $response->assertCreated();
    $response->assertJsonStructure([
        'data' => [
            'customer' => ['id', 'name', 'email', 'role'],
            'token',
            'token_type',
        ],
    ]);

    expect(Customer::query()->where('email', 'test@google.com')->exists())->toBeTrue();
    expect(
        CustomerSocialAccount::query()
            ->where('provider', 'google')
            ->where('provider_id', '123')
            ->exists(),
    )->toBeTrue();
});

it('reuses existing customer on subsequent social callback', function (): void {
    $existing = Customer::factory()->fromSocial()->create(['email' => 'reuse@google.com']);
    $existing->socialAccounts()->create([
        'provider' => 'google',
        'provider_id' => '999',
    ]);

    Socialite::fake('google', makeSocialiteUser('999', 'reuse@google.com', 'Existing'));

    $response = postJson('/api/v1/auth/social/google/callback');

    $response->assertOk();
    expect(Customer::query()->where('email', 'reuse@google.com')->count())->toBe(1);
    expect(
        CustomerSocialAccount::query()
            ->where('provider', 'google')
            ->where('provider_id', '999')
            ->count(),
    )->toBe(1);
});

it('links a new social account to an existing customer matched by email', function (): void {
    Customer::factory()->create([
        'email' => 'existing@google.com',
        'password' => bcrypt('password'),
    ]);

    Socialite::fake('google', makeSocialiteUser('555', 'existing@google.com'));

    $response = postJson('/api/v1/auth/social/google/callback');

    $response->assertOk();
    expect(Customer::query()->where('email', 'existing@google.com')->count())->toBe(1);
    expect(
        CustomerSocialAccount::query()
            ->where('provider', 'google')
            ->where('provider_id', '555')
            ->exists(),
    )->toBeTrue();
});
