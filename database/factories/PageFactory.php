<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\Page;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Page>
 */
final class PageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(3);

        return [
            'site_id' => Site::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(4)),
            'status' => ContentStatus::Draft,
            'seo' => null,
            'sort_order' => 0,
            'published_at' => null,
        ];
    }

    public function published(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ContentStatus::Published,
            'published_at' => now(),
        ]);
    }
}
