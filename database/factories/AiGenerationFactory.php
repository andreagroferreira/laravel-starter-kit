<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AiGenerationStatus;
use App\Models\AiGeneration;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiGeneration>
 */
final class AiGenerationFactory extends Factory
{
    protected $model = AiGeneration::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'user_id' => null,
            'site_id' => null,
            'agent' => 'copy',
            'status' => AiGenerationStatus::Queued,
            'input' => ['briefing' => fake()->sentence()],
            'output' => null,
            'error' => null,
        ];
    }
}
