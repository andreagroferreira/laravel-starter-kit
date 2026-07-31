<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Site;
use App\Models\SiteVersion;
use Illuminate\Database\Eloquent\Factories\Factory;
use stdClass;

/**
 * @extends Factory<SiteVersion>
 */
final class SiteVersionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'schema' => ['pages' => [], 'theme' => ['tokens' => new stdClass]],
            'origin' => 'human',
            'published_at' => null,
        ];
    }

    public function published(): self
    {
        return $this->state(fn (array $attributes): array => [
            'published_at' => now(),
        ]);
    }
}
