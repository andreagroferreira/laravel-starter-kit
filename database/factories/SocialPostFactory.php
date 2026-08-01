<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Site;
use App\Models\SocialPost;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SocialPost>
 */
final class SocialPostFactory extends Factory
{
    protected $model = SocialPost::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'site_id' => Site::factory(),
            'network' => 'facebook',
            'content' => fake()->sentence(),
            'media' => null,
            'scheduled_at' => null,
            'published_at' => null,
            'status' => 'draft',
            'external_id' => null,
            'error' => null,
            'created_by' => null,
        ];
    }
}
