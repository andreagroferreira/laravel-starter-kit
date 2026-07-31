<?php

declare(strict_types=1);

use App\Models\Post;
use App\Models\Site;
use App\Support\UniqueSlug;

it('slugifies and deduplicates within the scope', function (): void {
    $site = Site::factory()->create();

    expect(UniqueSlug::make($site->posts()->getQuery(), 'Olá Mundo'))->toBe('ola-mundo');

    Post::factory()->for($site)->create(['slug' => 'ola-mundo']);
    Post::factory()->for($site)->create(['slug' => 'ola-mundo-2']);

    expect(UniqueSlug::make($site->posts()->getQuery(), 'Olá Mundo'))->toBe('ola-mundo-3');
});

it('falls back to a safe base for unslugifiable titles', function (): void {
    $site = Site::factory()->create();

    expect(UniqueSlug::make($site->posts()->getQuery(), '!!! ???'))->toBe('sem-titulo');
});
