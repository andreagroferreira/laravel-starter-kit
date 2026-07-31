<?php

declare(strict_types=1);

use App\Mcp\Servers\WizardServer;
use App\Mcp\Tools\CreatePageTool;
use App\Mcp\Tools\GetPageTool;
use App\Mcp\Tools\ListSitesTool;
use App\Mcp\Tools\UpdateBlocksTool;
use App\Models\AuditLog;
use App\Models\Page;
use App\Models\Site;
use App\Models\Tenant;
use App\Models\User;

beforeEach(function (): void {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create(['current_tenant_id' => $this->tenant->id]);
    $this->tenant->users()->attach($this->user);

    // What SetCurrentTenant does on the HTTP route.
    app()->instance(Tenant::class, $this->tenant);
});

it('lists the tenant sites', function (): void {
    Site::factory()->for($this->tenant)->create(['name' => 'Portal']);
    Site::factory()->for(Tenant::factory())->create(['name' => 'Hidden']);

    WizardServer::actingAs($this->user)
        ->tool(ListSitesTool::class)
        ->assertOk()
        ->assertSee('Portal')
        ->assertDontSee('Hidden');
});

it('gets a page with blocks', function (): void {
    $site = Site::factory()->for($this->tenant)->create();
    $page = Page::factory()->for($site)->create(['slug' => 'sobre']);
    $page->blocks()->create(['type' => 'hero', 'content' => ['heading' => 'Olá'], 'sort_order' => 0]);

    WizardServer::actingAs($this->user)
        ->tool(GetPageTool::class, ['site_slug' => $site->slug, 'page_slug' => 'sobre'])
        ->assertOk()
        ->assertSee('hero')
        ->assertSee('heading');
});

it('errors when the site does not exist', function (): void {
    $response = WizardServer::actingAs($this->user)
        ->tool(GetPageTool::class, ['site_slug' => 'nope', 'page_slug' => 'x']);

    $response->assertSee('not found');
});

it('creates a draft page and writes to the audit log', function (): void {
    $site = Site::factory()->for($this->tenant)->create();

    WizardServer::actingAs($this->user)
        ->tool(CreatePageTool::class, [
            'site_slug' => $site->slug,
            'title' => 'Landing X',
            'slug' => 'landing-x',
            'blocks' => [['type' => 'hero', 'content' => ['heading' => 'Hi']]],
        ])
        ->assertOk()
        ->assertSee('draft');

    $page = $site->pages()->where('slug', 'landing-x')->sole();

    expect($page->status->value)->toBe('draft')
        ->and($page->blocks)->toHaveCount(1);

    $log = AuditLog::query()->where('action', 'page.created')->sole();

    expect($log->actor_type)->toBe('agent')
        ->and($log->subject_id)->toBe($page->id)
        ->and($log->user_id)->toBe($this->user->id);
});

it('refuses duplicate slugs', function (): void {
    $site = Site::factory()->for($this->tenant)->create();
    Page::factory()->for($site)->create(['slug' => 'sobre']);

    WizardServer::actingAs($this->user)
        ->tool(CreatePageTool::class, [
            'site_slug' => $site->slug,
            'title' => 'Dup',
            'slug' => 'sobre',
        ])
        ->assertSee('already exists');
});

it('replaces page blocks and writes to the audit log', function (): void {
    $site = Site::factory()->for($this->tenant)->create();
    $page = Page::factory()->for($site)->create(['slug' => 'sobre']);
    $page->blocks()->create(['type' => 'hero', 'content' => [], 'sort_order' => 0]);

    WizardServer::actingAs($this->user)
        ->tool(UpdateBlocksTool::class, [
            'site_slug' => $site->slug,
            'page_slug' => 'sobre',
            'blocks' => [
                ['type' => 'rich_text', 'content' => ['html' => '<p>Novo</p>']],
                ['type' => 'cta', 'content' => ['label' => 'Comprar']],
            ],
        ])
        ->assertOk()
        ->assertSee('2');

    expect($page->blocks()->count())->toBe(2)
        ->and($page->blocks()->first()->type)->toBe('rich_text');

    expect(AuditLog::query()->where('action', 'page.blocks_updated')->where('actor_type', 'agent')->exists())->toBeTrue();
});

it('does not leak sites from other tenants through tools', function (): void {
    $site = Site::factory()->for(Tenant::factory())->create(['slug' => 'secret']);

    WizardServer::actingAs($this->user)
        ->tool(GetPageTool::class, ['site_slug' => 'secret', 'page_slug' => 'x'])
        ->assertSee('not found');
});

it('requires authentication on the mcp endpoint', function (): void {
    $this->postJson('/mcp/wizard', [
        'jsonrpc' => '2.0',
        'id' => 1,
        'method' => 'tools/list',
        'params' => [],
    ])->assertUnauthorized();
});
