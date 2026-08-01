<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\DeploymentStatus;
use App\Models\Deployment;
use App\Models\Site;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Deployment>
 */
final class DeploymentFactory extends Factory
{
    protected $model = Deployment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'site_id' => Site::factory(),
            'site_version_id' => null,
            'type' => 'content',
            'status' => DeploymentStatus::Queued,
            'url' => null,
            'error' => null,
            'meta' => null,
            'triggered_by' => null,
            'finished_at' => null,
        ];
    }
}
