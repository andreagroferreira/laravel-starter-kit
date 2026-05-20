<?php

declare(strict_types=1);

use App\Enums\CustomerRole;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('hides password and remember_token in serialization', function (): void {
    $customer = Customer::factory()->create();
    $array = $customer->toArray();

    expect($array)->not->toHaveKey('password')
        ->and($array)->not->toHaveKey('remember_token');
});

it('casts role to CustomerRole enum', function (): void {
    $customer = Customer::factory()->pro()->create();

    expect($customer->role)->toBe(CustomerRole::Pro);
});

it('creates social-only customer with null password', function (): void {
    $customer = Customer::factory()->fromSocial()->create();

    expect($customer->password)->toBeNull()
        ->and($customer->avatar_url)->not->toBeNull();
});

it('supports soft deletes', function (): void {
    $customer = Customer::factory()->create();
    $customer->delete();

    expect(Customer::withTrashed()->find($customer->id))->not->toBeNull()
        ->and(Customer::find($customer->id))->toBeNull();
});

it('uses customer guard via Sanctum', function (): void {
    expect(config('auth.guards.customer.driver'))->toBe('sanctum')
        ->and(config('auth.guards.customer.provider'))->toBe('customers')
        ->and(config('auth.providers.customers.model'))->toBe(Customer::class);
});
