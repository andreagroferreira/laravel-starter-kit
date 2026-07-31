<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AiOverageReport;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiOverageReport>
 */
final class AiOverageReportFactory extends Factory
{
    protected $model = AiOverageReport::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'period' => now()->startOfMonth()->toDateString(),
            'credits_reported' => 0,
        ];
    }
}
