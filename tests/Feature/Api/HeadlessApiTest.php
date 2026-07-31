<?php

declare(strict_types=1);

use App\Models\Page;
use App\Models\Site;
use App\Models\Tenant;
use App\Models\User;
use App\Services\SiteService;

beforeEach(function (): void {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create(['current_tenant_id' => $this->tenant->id]);
    $this->tenant->users()->attach($this->user);
    grantRole($this->tenant, $this->user);
    $this->token = $this->user->createToken('renderer')->plainTextToken;
});

it('requires a token for the headless api', function (): void {
    $this->getJson('/api/v1/sites')->assertUnauthorized();
});

it('lists the tenant sites', function (): void {
    Site::factory()->for($this->tenant)->create(['name' => 'Portal']);
    Site::factory()->for(Tenant::factory())->create(['name' => 'Hidden']);

    $this->withToken($this->token)
        ->getJson('/api/v1/sites')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Portal');
});

it('serves the published schema', function (): void {
    $site = Site::factory()->for($this->tenant)->create();
    $page = Page::factory()->for($site)->published()->create(['slug' => 'sobre']);
    $page->blocks()->create(['type' => 'hero', 'content' => ['heading' => 'Hi'], 'sort_order' => 0]);

    resolve(SiteService::class)->publish($site, $this->user);

    $this->withToken($this->token)
        ->getJson(sprintf('/api/v1/sites/%s/schema', $site->slug))
        ->assertOk()
        ->assertJsonPath('data.site', $site->slug)
        ->assertJsonPath('data.schema.pages.0.slug', 'sobre');
});

it('returns 404 for a site without a published version', function (): void {
    $site = Site::factory()->for($this->tenant)->create();

    $this->withToken($this->token)
        ->getJson(sprintf('/api/v1/sites/%s/schema', $site->slug))
        ->assertNotFound();
});

it('serves a single published page by slug', function (): void {
    $site = Site::factory()->for($this->tenant)->create();
    Page::factory()->for($site)->published()->create(['slug' => 'sobre', 'title' => 'Sobre nós']);
    Page::factory()->for($site)->create(['slug' => 'rascunho']);

    $this->withToken($this->token)
        ->getJson(sprintf('/api/v1/sites/%s/pages/sobre', $site->slug))
        ->assertOk()
        ->assertJsonPath('data.title', 'Sobre nós');

    $this->withToken($this->token)
        ->getJson(sprintf('/api/v1/sites/%s/pages/rascunho', $site->slug))
        ->assertNotFound();
});

it('does not leak sites from other tenants', function (): void {
    $site = Site::factory()->for(Tenant::factory())->create();

    $this->withToken($this->token)
        ->getJson(sprintf('/api/v1/sites/%s/schema', $site->slug))
        ->assertNotFound();
});
