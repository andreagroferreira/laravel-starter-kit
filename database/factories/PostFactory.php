<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ContentStatus;
use App\Models\Post;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
final class PostFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(4);

        return [
            'site_id' => Site::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.Str::lower(Str::random(4)),
            'status' => ContentStatus::Draft,
            'excerpt' => fake()->paragraph(),
            'body' => '<p>'.fake()->paragraph().'</p><p>'.fake()->paragraph().'</p><p>'.fake()->paragraph().'</p>',
            'seo' => null,
            'published_at' => null,
        ];
    }

    public function published(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ContentStatus::Published,
            'published_at' => now()->subHour(),
        ]);
    }

    public function scheduled(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => ContentStatus::Scheduled,
            'published_at' => now()->addDay(),
        ]);
    }
}
