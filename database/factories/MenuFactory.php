<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Menu;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Menu>
 */
final class MenuFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'name' => 'main',
            'items' => [
                ['label' => 'Home', 'url' => '/'],
            ],
        ];
    }
}
