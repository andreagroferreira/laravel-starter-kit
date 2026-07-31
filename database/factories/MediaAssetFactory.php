<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\MediaAsset;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<MediaAsset>
 */
final class MediaAssetFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $filename = Str::lower(Str::random(10)).'.jpg';

        return [
            'tenant_id' => Tenant::factory(),
            'disk' => 'public',
            'path' => 'media/'.$filename,
            'filename' => $filename,
            'mime_type' => 'image/jpeg',
            'size' => fake()->numberBetween(10_000, 500_000),
            'alt' => null,
        ];
    }
}
