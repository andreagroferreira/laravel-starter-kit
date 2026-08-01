<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\IntegrationProvider;
use App\Models\Integration;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Integration>
 */
final class IntegrationFactory extends Factory
{
    protected $model = Integration::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'site_id' => null,
            'provider' => IntegrationProvider::GoogleAnalytics,
            'access_token' => 'token-'.fake()->uuid(),
            'refresh_token' => 'refresh-'.fake()->uuid(),
            'expires_at' => now()->addHour(),
            'external_id' => (string) fake()->randomNumber(9),
            'meta' => [],
            'status' => 'connected',
            'connected_at' => now(),
        ];
    }
}
