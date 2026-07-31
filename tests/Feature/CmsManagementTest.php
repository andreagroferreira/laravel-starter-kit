<?php

declare(strict_types=1);

use App\Models\Page;
use App\Models\Post;
use App\Models\Site;
use App\Models\Tenant;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create(['current_tenant_id' => $this->tenant->id]);
    $this->tenant->users()->attach($this->user);
    $this->site = Site::factory()->for($this->tenant)->create();
});

it('renders the site detail with pages and versions', function (): void {
    Page::factory()->for($this->site)->create();
    $this->site->versions()->create(['schema' => [], 'published_at' => now()]);

    $this->actingAs($this->user)
        ->get('/sites/'.$this->site->id)
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Sites/Show')
            ->has('pages', 1)
            ->has('versions', 1)
        );
});

it('deletes a site', function (): void {
    $this->actingAs($this->user)
        ->delete('/sites/'.$this->site->id)
        ->assertRedirect();

    expect(Site::query()->find($this->site->id))->toBeNull();
});

it('renders the page editor', function (): void {
    $page = Page::factory()->for($this->site)->create();

    $this->actingAs($this->user)
        ->get(sprintf('/sites/%s/pages/%s', $this->site->id, $page->id))
        ->assertOk()
        ->assertInertia(fn (Assert $p): Assert => $p
            ->component('Pages/Edit')
            ->has('blockTypes')
        );
});

it('deletes a page and redirects to the site', function (): void {
    $page = Page::factory()->for($this->site)->create();

    $this->actingAs($this->user)
        ->delete(sprintf('/sites/%s/pages/%s', $this->site->id, $page->id))
        ->assertRedirect('/sites/'.$this->site->id);

    expect($this->site->pages()->count())->toBe(0);
});

it('updates and deletes posts', function (): void {
    $post = Post::factory()->for($this->site)->create(['title' => 'Antigo']);

    $this->actingAs($this->user)
        ->put(sprintf('/sites/%s/posts/%s', $this->site->id, $post->id), ['title' => 'Novo'])
        ->assertRedirect();

    expect($post->refresh()->title)->toBe('Novo');

    $this->actingAs($this->user)
        ->delete(sprintf('/sites/%s/posts/%s', $this->site->id, $post->id));

    expect($this->site->posts()->count())->toBe(0);
});

it('404s when the post belongs to another site', function (): void {
    $post = Post::factory()->for(Site::factory()->for($this->tenant))->create();

    $this->actingAs($this->user)
        ->put(sprintf('/sites/%s/posts/%s', $this->site->id, $post->id), ['title' => 'x'])
        ->assertNotFound();
});
