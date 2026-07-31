<?php

declare(strict_types=1);

use App\Enums\ContentStatus;
use App\Models\Page;
use App\Models\Site;
use App\Models\Tenant;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create(['current_tenant_id' => $this->tenant->id]);
    $this->tenant->users()->attach($this->user);
});

it('requires authentication for site routes', function (): void {
    $this->get('/sites')->assertRedirect('/login');
});

it('lists sites scoped to the current tenant', function (): void {
    Site::factory()->for($this->tenant)->create(['name' => 'My Site']);

    $otherTenant = Tenant::factory()->create();
    Site::factory()->for($otherTenant)->create(['name' => 'Other Site']);

    $this->actingAs($this->user)
        ->get('/sites')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Sites/Index')
            ->has('sites', 1)
            ->where('sites.0.name', 'My Site')
        );
});

it('creates a site with a homepage and a default menu', function (): void {
    $this->actingAs($this->user)
        ->post('/sites', [
            'name' => 'Empresa',
            'slug' => 'empresa',
            'type' => 'site',
        ])
        ->assertRedirect();

    $site = Site::query()->where('slug', 'empresa')->sole();

    expect($site->tenant_id)->toBe($this->tenant->id)
        ->and($site->pages)->toHaveCount(1)
        ->and($site->pages->first()->slug)->toBe('/')
        ->and($site->menus)->toHaveCount(1);
});

it('validates site creation', function (): void {
    $this->actingAs($this->user)
        ->post('/sites', ['name' => '', 'slug' => 'bad slug!', 'type' => 'unknown'])
        ->assertSessionHasErrors(['name', 'slug', 'type']);
});

it('creates pages inside a site', function (): void {
    $site = Site::factory()->for($this->tenant)->create();

    $this->actingAs($this->user)
        ->post(sprintf('/sites/%s/pages', $site->id), ['title' => 'Sobre', 'slug' => 'sobre'])
        ->assertRedirect();

    expect($site->pages()->where('slug', 'sobre')->exists())->toBeTrue();
});

it('updates a page with blocks', function (): void {
    $site = Site::factory()->for($this->tenant)->create();
    $page = Page::factory()->for($site)->create();

    $this->actingAs($this->user)
        ->put(sprintf('/sites/%s/pages/%s', $site->id, $page->id), [
            'title' => 'Novo título',
            'blocks' => [
                ['type' => 'hero', 'content' => ['heading' => 'Olá']],
                ['type' => 'rich_text', 'content' => ['html' => '<p>Texto</p>']],
            ],
        ])
        ->assertRedirect();

    expect($page->refresh()->title)->toBe('Novo título')
        ->and($page->blocks)->toHaveCount(2)
        ->and($page->blocks->first()->type)->toBe('hero')
        ->and($page->blocks->last()->sort_order)->toBe(1);
});

it('rejects invalid block types', function (): void {
    $site = Site::factory()->for($this->tenant)->create();
    $page = Page::factory()->for($site)->create();

    $this->actingAs($this->user)
        ->put(sprintf('/sites/%s/pages/%s', $site->id, $page->id), [
            'blocks' => [['type' => 'malware', 'content' => []]],
        ])
        ->assertSessionHasErrors('blocks.0.type');
});

it('publishes and unpublishes a page', function (): void {
    $site = Site::factory()->for($this->tenant)->create();
    $page = Page::factory()->for($site)->create();

    $this->actingAs($this->user)->post(sprintf('/sites/%s/pages/%s/publish', $site->id, $page->id));

    expect($page->refresh()->status)->toBe(ContentStatus::Published)
        ->and($page->published_at)->not->toBeNull();

    $this->actingAs($this->user)->post(sprintf('/sites/%s/pages/%s/publish', $site->id, $page->id));

    expect($page->refresh()->status)->toBe(ContentStatus::Draft);
});

it('publishes a site version with the full schema', function (): void {
    $site = Site::factory()->for($this->tenant)->create();
    $site->menus()->create(['name' => 'main', 'items' => [['label' => 'Home', 'url' => '/']]]);
    $page = Page::factory()->for($site)->published()->create(['slug' => 'sobre']);
    $page->blocks()->create(['type' => 'hero', 'content' => ['heading' => 'Hi'], 'sort_order' => 0]);

    $this->actingAs($this->user)->post(sprintf('/sites/%s/publish', $site->id));

    $version = $site->versions()->sole();

    expect($version->published_at)->not->toBeNull()
        ->and($version->origin)->toBe('human')
        ->and($version->schema['site']['slug'])->toBe($site->slug)
        ->and($version->schema['pages'])->toHaveCount(1)
        ->and($version->schema['pages'][0]['blocks'][0]['type'])->toBe('hero')
        ->and($version->schema['menus'])->toHaveKey('main')
        ->and($site->refresh()->status)->toBe('published');
});

it('forbids managing pages of another tenant', function (): void {
    $otherTenant = Tenant::factory()->create();
    $site = Site::factory()->for($otherTenant)->create();
    $page = Page::factory()->for($site)->create();

    $this->actingAs($this->user)
        ->put(sprintf('/sites/%s/pages/%s', $site->id, $page->id), ['title' => 'hack'])
        ->assertNotFound();
});
