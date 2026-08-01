<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\IntegrationProvider;
use App\Models\MetricSnapshot;
use App\Models\Site;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MetricSnapshot>
 */
final class MetricSnapshotFactory extends Factory
{
    protected $model = MetricSnapshot::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'site_id' => Site::factory(),
            'provider' => IntegrationProvider::GoogleAnalytics,
            'metric_date' => now()->subDay()->toDateString(),
            'metrics' => ['sessions' => 120, 'users' => 90, 'pageviews' => 310],
        ];
    }
}
