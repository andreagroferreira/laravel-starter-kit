<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

/**
 * @extends Factory<User>
 */
final class UserFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => 'password',
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): self
    {
        return $this->state(fn (array $attributes): array => [
            'email_verified_at' => null,
        ]);
    }

    public function withTwoFactorEnabled(): self
    {
        return $this->state(fn (array $attributes): array => [
            'two_factor_secret' => Crypt::encryptString('JBSWY3DPEHPK3PXP'),
            'two_factor_recovery_codes' => Crypt::encryptString((string) json_encode(
                collect(range(1, 8))->map(fn (): string => Str::random(10))->all(),
            )),
            'two_factor_confirmed_at' => now(),
        ]);
    }

    public function admin(): self
    {
        return $this->afterCreating(function (User $user): void {
            Role::findOrCreate('super-admin', 'web');
            $user->assignRole('super-admin');
        });
    }
}
