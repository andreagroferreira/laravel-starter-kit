<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\BrandProfile;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BrandProfile>
 */
final class BrandProfileFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->words(2, true),
            'tone_of_voice' => fake()->sentence(),
            'glossary' => null,
            'examples' => null,
            'guardrails' => null,
        ];
    }
}
