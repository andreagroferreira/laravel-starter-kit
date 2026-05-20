<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CustomerRole;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Customer>
 */
final class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
            'avatar_url' => null,
            'role' => CustomerRole::Free->value,
            'last_login_at' => null,
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ];
    }

    public function fromSocial(string $provider = 'google'): self
    {
        return $this->state(fn (array $attributes): array => [
            'password' => null,
            'avatar_url' => 'https://lh3.googleusercontent.com/a/' . fake()->uuid(),
            'email_verified_at' => now(),
        ]);
    }

    public function pro(): self
    {
        return $this->state(fn (array $attributes): array => [
            'role' => CustomerRole::Pro->value,
        ]);
    }

    public function enterprise(): self
    {
        return $this->state(fn (array $attributes): array => [
            'role' => CustomerRole::Enterprise->value,
        ]);
    }

    public function unverified(): self
    {
        return $this->state(fn (array $attributes): array => [
            'email_verified_at' => null,
        ]);
    }
}
