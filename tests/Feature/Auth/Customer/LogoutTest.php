<?php

declare(strict_types=1);

use App\Models\Customer;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\postJson;

it('logs out an authenticated customer and revokes the current token', function (): void {
    $customer = Customer::factory()->create();
    Sanctum::actingAs($customer, ['*'], 'customer');

    postJson('/api/v1/auth/logout')->assertOk()->assertJson(['status' => 'ok']);
});

it('blocks unauthenticated logout', function (): void {
    postJson('/api/v1/auth/logout')->assertStatus(401);
});

it('deletes the personal access token used to call logout', function (): void {
    $customer = Customer::factory()->create();
    $plain = $customer->createToken('test')->plainTextToken;

    postJson('/api/v1/auth/logout', [], [
        'Authorization' => 'Bearer ' . $plain,
    ])->assertOk();

    expect(PersonalAccessToken::query()->where('tokenable_id', $customer->id)->count())->toBe(0);
});
