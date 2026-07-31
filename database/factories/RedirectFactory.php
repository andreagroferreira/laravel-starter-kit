<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Redirect;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Redirect>
 */
final class RedirectFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'from_path' => '/old-'.fake()->slug(),
            'to_path' => '/new-'.fake()->slug(),
            'status_code' => 301,
        ];
    }
}
