<?php

declare(strict_types=1);

use App\Models\Page;
use App\Models\Post;
use App\Models\Site;
use App\Models\Tenant;
use App\Models\User;

beforeEach(function (): void {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create(['current_tenant_id' => $this->tenant->id]);
    $this->tenant->users()->attach($this->user);
    grantRole($this->tenant, $this->user);
});

it('soft deletes sites and frees the slug for live rows', function (): void {
    $site = Site::factory()->for($this->tenant)->create(['slug' => 'empresa']);

    $this->actingAs($this->user)
        ->delete('/sites/'.$site->id)
        ->assertRedirect();

    expect(Site::query()->count())->toBe(0)
        ->and(Site::withTrashed()->count())->toBe(1);

    // O índice parcial permite reutilizar o slug num site vivo.
    $again = Site::factory()->for($this->tenant)->create(['slug' => 'empresa']);

    expect($again->slug)->toBe('empresa');
});

it('soft deletes pages and posts through the controllers', function (): void {
    $site = Site::factory()->for($this->tenant)->create();
    $page = Page::factory()->for($site)->create();
    $post = Post::factory()->for($site)->create();

    $this->actingAs($this->user)->delete(sprintf('/sites/%s/pages/%s', $site->id, $page->id))->assertRedirect();
    $this->actingAs($this->user)->delete(sprintf('/sites/%s/posts/%s', $site->id, $post->id))->assertRedirect();

    expect(Page::withTrashed()->whereKey($page->id)->sole()->trashed())->toBeTrue()
        ->and(Post::withTrashed()->whereKey($post->id)->sole()->trashed())->toBeTrue();
});

it('prunes soft-deleted content after 30 days', function (): void {
    $site = Site::factory()->for($this->tenant)->create();
    $oldPost = Post::factory()->for($site)->create(['deleted_at' => now()->subDays(31)]);
    $recentPost = Post::factory()->for($site)->create(['deleted_at' => now()->subDays(5)]);
    $oldPage = Page::factory()->for($site)->create(['deleted_at' => now()->subDays(31)]);
    $oldSite = Site::factory()->for($this->tenant)->create(['deleted_at' => now()->subDays(31)]);

    $this->artisan('model:prune')->assertSuccessful();

    expect(Post::withTrashed()->whereKey($oldPost->id)->exists())->toBeFalse()
        ->and(Post::withTrashed()->whereKey($recentPost->id)->exists())->toBeTrue()
        ->and(Page::withTrashed()->whereKey($oldPage->id)->exists())->toBeFalse()
        ->and(Site::withTrashed()->whereKey($oldSite->id)->exists())->toBeFalse();
});
