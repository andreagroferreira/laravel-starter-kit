<?php

declare(strict_types=1);

use App\Enums\TenantRole;
use App\Mcp\Servers\WizardServer;
use App\Mcp\Tools\CreatePageTool;
use App\Mcp\Tools\CreatePostDraftTool;
use App\Mcp\Tools\GetPageTool;
use App\Mcp\Tools\ListMediaTool;
use App\Mcp\Tools\ListPostsTool;
use App\Mcp\Tools\ListSitesTool;
use App\Mcp\Tools\UpdateBlocksTool;
use App\Mcp\Tools\UpdatePostDraftTool;
use App\Models\Site;
use App\Models\Tenant;
use App\Models\User;
use App\Support\CurrentTenant;
use Laravel\Sanctum\Sanctum;

beforeEach(function (): void {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create(['current_tenant_id' => $this->tenant->id]);
    $this->tenant->users()->attach($this->user);
    grantRole($this->tenant, $this->user);
    $this->site = Site::factory()->for($this->tenant)->create();
});

it('rejects api requests from tokens without the read ability', function (): void {
    $token = $this->user->createToken('no-read', ['write:draft'])->plainTextToken;

    $this->getJson('/api/v1/sites', ['Authorization' => 'Bearer '.$token])
        ->assertForbidden();
});

it('accepts api requests from tokens with the read ability', function (): void {
    $token = $this->user->createToken('reader', ['read'])->plainTextToken;

    $this->getJson('/api/v1/sites', ['Authorization' => 'Bearer '.$token])
        ->assertOk();
});

it('blocks write tools for read-only tokens', function (): void {
    Sanctum::actingAs($this->user, ['read']);
    resolve(CurrentTenant::class)->set($this->tenant);

    WizardServer::actingAs($this->user)
        ->tool(CreatePageTool::class, ['site_slug' => $this->site->slug, 'title' => 'Nova', 'slug' => 'nova'])
        ->assertHasErrors();

    expect($this->site->pages()->count())->toBe(0);
});

it('allows write tools for write:draft tokens', function (): void {
    Sanctum::actingAs($this->user, ['write:draft']);
    resolve(CurrentTenant::class)->set($this->tenant);

    WizardServer::actingAs($this->user)
        ->tool(CreatePageTool::class, ['site_slug' => $this->site->slug, 'title' => 'Nova', 'slug' => 'nova'])
        ->assertOk();

    expect($this->site->pages()->count())->toBe(1);
});

it('blocks read tools for tokens without any granted ability', function (): void {
    Sanctum::actingAs($this->user, ['publish']);
    resolve(CurrentTenant::class)->set($this->tenant);

    WizardServer::actingAs($this->user)
        ->tool(ListSitesTool::class)
        ->assertHasErrors();
});

it('blocks tools when the user lacks the tenant permission even with a valid ability', function (): void {
    $journalist = User::factory()->create(['current_tenant_id' => $this->tenant->id]);
    $this->tenant->users()->attach($journalist);
    grantRole($this->tenant, $journalist, TenantRole::Journalist);

    Sanctum::actingAs($journalist, ['write:draft']);
    resolve(CurrentTenant::class)->set($this->tenant);

    WizardServer::actingAs($journalist)
        ->tool(CreatePageTool::class, ['site_slug' => $this->site->slug, 'title' => 'Nova', 'slug' => 'nova'])
        ->assertHasErrors();

    expect($this->site->pages()->count())->toBe(0);
});

it('blocks the remaining tools for tokens without the matching ability', function (): void {
    Sanctum::actingAs($this->user, ['publish']);
    resolve(CurrentTenant::class)->set($this->tenant);

    WizardServer::actingAs($this->user)
        ->tool(GetPageTool::class, ['site_slug' => $this->site->slug, 'page_slug' => 'x'])
        ->assertHasErrors();

    WizardServer::actingAs($this->user)
        ->tool(UpdateBlocksTool::class, ['site_slug' => $this->site->slug, 'page_slug' => 'x', 'blocks' => [['type' => 'hero']]])
        ->assertHasErrors();
});

it('blocks every write tool for tokens without write:draft', function (): void {
    Sanctum::actingAs($this->user, ['read']);
    resolve(CurrentTenant::class)->set($this->tenant);

    WizardServer::actingAs($this->user)
        ->tool(CreatePostDraftTool::class, ['site_slug' => $this->site->slug, 'title' => 'X', 'body' => 'Y'])
        ->assertHasErrors();

    WizardServer::actingAs($this->user)
        ->tool(UpdatePostDraftTool::class, ['site_slug' => $this->site->slug, 'post_slug' => 'x'])
        ->assertHasErrors();
});

it('blocks read tools for tokens without read', function (): void {
    Sanctum::actingAs($this->user, ['publish']);
    resolve(CurrentTenant::class)->set($this->tenant);

    WizardServer::actingAs($this->user)
        ->tool(ListPostsTool::class, ['site_slug' => $this->site->slug])
        ->assertHasErrors();

    WizardServer::actingAs($this->user)
        ->tool(ListMediaTool::class)
        ->assertHasErrors();
});

it('rejects unauthenticated tool calls', function (): void {
    resolve(CurrentTenant::class)->set($this->tenant);

    WizardServer::tool(ListSitesTool::class)->assertHasErrors();
});
