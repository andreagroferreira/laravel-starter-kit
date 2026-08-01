<?php

declare(strict_types=1);

use App\Enums\ContentStatus;
use App\Models\Category;
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

it('renders the post editor', function (): void {
    $category = Category::factory()->for($this->site)->create();
    $post = Post::factory()->for($this->site)->create(['title' => 'Edit me']);
    $post->categories()->attach($category);

    $this->actingAs($this->user)
        ->get(sprintf('/sites/%s/posts/%s', $this->site->id, $post->id))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Posts/Edit')
            ->where('post.title', 'Edit me')
            ->where('post.status', 'draft')
            ->has('categories', 1)
            ->has('selectedCategories', 1)
        );
});

it('lists posts of a site', function (): void {
    Post::factory()->for($this->site)->create(['title' => 'Primeiro post']);

    $this->actingAs($this->user)
        ->get(sprintf('/sites/%s/posts', $this->site->id))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Posts/Index')
            ->has('posts.data', 1)
            ->where('posts.data.0.title', 'Primeiro post')
        );
});

it('creates a post with categories and author', function (): void {
    $category = Category::factory()->for($this->site)->create();

    $this->actingAs($this->user)
        ->post(sprintf('/sites/%s/posts', $this->site->id), [
            'title' => 'Novo artigo',
            'slug' => 'novo-artigo',
            'body' => '<p>Conteúdo</p>',
            'categories' => [$category->id],
        ])
        ->assertRedirect();

    $post = Post::query()->where('slug', 'novo-artigo')->sole();

    expect($post->author_id)->toBe($this->user->id)
        ->and($post->categories)->toHaveCount(1)
        ->and($post->status)->toBe(ContentStatus::Draft);
});

it('publishes immediately when no future date is set', function (): void {
    $post = Post::factory()->for($this->site)->create();

    $this->actingAs($this->user)->post(sprintf('/sites/%s/posts/%s/publish', $this->site->id, $post->id));

    expect($post->refresh()->status)->toBe(ContentStatus::Published);
});

it('schedules a post with a future date', function (): void {
    $post = Post::factory()->for($this->site)->create(['published_at' => now()->addWeek()]);

    $this->actingAs($this->user)->post(sprintf('/sites/%s/posts/%s/publish', $this->site->id, $post->id));

    expect($post->refresh()->status)->toBe(ContentStatus::Scheduled);
});

it('unpublishes a published post', function (): void {
    $post = Post::factory()->for($this->site)->published()->create();

    $this->actingAs($this->user)->post(sprintf('/sites/%s/posts/%s/publish', $this->site->id, $post->id));

    expect($post->refresh()->status)->toBe(ContentStatus::Draft);
});

it('serves the rss feed with live posts only', function (): void {
    Post::factory()->for($this->site)->published()->create(['title' => 'Live post']);
    Post::factory()->for($this->site)->create(['title' => 'Draft post']);
    Post::factory()->for($this->site)->scheduled()->create(['title' => 'Future post']);

    $response = $this->get(sprintf('/api/v1/public/sites/%s/feed.rss', $this->site->id));

    $response->assertOk()
        ->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8');

    expect($response->getContent())
        ->toContain('Live post')
        ->not->toContain('Draft post')
        ->not->toContain('Future post');
});

it('serves the sitemap with published pages and live posts', function (): void {
    $this->site->pages()->create(['title' => 'Home', 'slug' => '/', 'status' => ContentStatus::Published, 'published_at' => now()]);
    Post::factory()->for($this->site)->published()->create(['slug' => 'hello']);

    $response = $this->get(sprintf('/api/v1/public/sites/%s/sitemap.xml', $this->site->id));

    $response->assertOk();

    expect($response->getContent())
        ->toContain('<urlset')
        ->toContain('/posts/hello');
});

it('does not leak posts management across tenants', function (): void {
    $otherSite = Site::factory()->for(Tenant::factory())->create();

    $this->actingAs($this->user)
        ->post(sprintf('/sites/%s/posts', $otherSite->id), ['title' => 'hack', 'slug' => 'hack'])
        ->assertNotFound();
});
