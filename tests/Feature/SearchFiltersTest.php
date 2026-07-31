<?php

declare(strict_types=1);

use App\Enums\ContentStatus;
use App\Models\Category;
use App\Models\MediaAsset;
use App\Models\Post;
use App\Models\Site;
use App\Models\Tenant;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create(['current_tenant_id' => $this->tenant->id]);
    $this->tenant->users()->attach($this->user);
    grantRole($this->tenant, $this->user);
    $this->site = Site::factory()->for($this->tenant)->create();
});

it('filters posts server-side by search, status and category', function (): void {
    $category = Category::factory()->for($this->site)->create();

    $match = Post::factory()->for($this->site)->create(['title' => 'Guia de jardinagem', 'status' => ContentStatus::Published]);
    $match->categories()->attach($category);
    Post::factory()->for($this->site)->create(['title' => 'Outro artigo', 'status' => ContentStatus::Draft]);

    $this->actingAs($this->user)
        ->get(sprintf('/sites/%s/posts?search=jardinagem&status=published&category=%s', $this->site->id, $category->id))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Posts/Index')
            ->has('posts.data', 1)
            ->where('posts.data.0.title', 'Guia de jardinagem')
            ->where('filters.search', 'jardinagem')
        );
});

it('keeps the query string on the posts paginator links', function (): void {
    Post::factory()->for($this->site)->count(16)->create(['status' => ContentStatus::Draft]);

    $this->actingAs($this->user)
        ->get(sprintf('/sites/%s/posts?status=draft', $this->site->id))
        ->assertInertia(fn (Assert $page): Assert => $page
            ->where('posts.next_page_url', fn ($url): bool => is_string($url) && str_contains($url, 'status=draft'))
        );
});

it('filters media server-side by filename', function (): void {
    MediaAsset::factory()->for($this->tenant)->create(['filename' => 'logo-azul.png']);
    MediaAsset::factory()->for($this->tenant)->create(['filename' => 'banner.jpg']);

    $this->actingAs($this->user)
        ->get('/media?search=logo')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->has('assets.data', 1)
            ->where('assets.data.0.filename', 'logo-azul.png')
        );
});
