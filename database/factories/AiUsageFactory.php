<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AiUsage;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiUsage>
 */
final class AiUsageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'agent' => 'copywriter',
            'provider' => 'openai',
            'model' => 'gpt-5-mini',
            'prompt_tokens' => fake()->numberBetween(50, 500),
            'completion_tokens' => fake()->numberBetween(50, 500),
            'credits' => 1,
        ];
    }
}
