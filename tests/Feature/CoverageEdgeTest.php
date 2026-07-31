<?php

declare(strict_types=1);

use App\Ai\Agents\CopywriterAgent;
use App\Mcp\Servers\WizardServer;
use App\Mcp\Tools\CreatePageTool;
use App\Mcp\Tools\GetPageTool;
use App\Mcp\Tools\UpdateBlocksTool;
use App\Models\BrandProfile;
use App\Models\Site;
use App\Models\Tenant;
use App\Models\User;

beforeEach(function (): void {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create(['current_tenant_id' => $this->tenant->id]);
    $this->tenant->users()->attach($this->user);
    app()->instance(Tenant::class, $this->tenant);
});

it('covers the copy controller current_content branch', function (): void {
    CopywriterAgent::fake(['improved copy']);

    $site = Site::factory()->for($this->tenant)->create();

    $this->actingAs($this->user)
        ->postJson(sprintf('/sites/%s/ai/copy', $site->id), [
            'block_type' => 'hero',
            'briefing' => 'Make it punchier',
            'current_content' => 'Old boring heading',
        ])
        ->assertOk()
        ->assertJsonPath('copy', 'improved copy');
});

it('errors creating a page on a missing site', function (): void {
    WizardServer::actingAs($this->user)
        ->tool(CreatePageTool::class, [
            'site_slug' => 'missing',
            'title' => 'X',
            'slug' => 'x',
        ])
        ->assertSee('not found');
});

it('errors updating blocks on a missing site or page', function (): void {
    WizardServer::actingAs($this->user)
        ->tool(UpdateBlocksTool::class, [
            'site_slug' => 'missing',
            'page_slug' => 'x',
            'blocks' => [['type' => 'hero']],
        ])
        ->assertSee('not found');

    $site = Site::factory()->for($this->tenant)->create();

    WizardServer::actingAs($this->user)
        ->tool(UpdateBlocksTool::class, [
            'site_slug' => $site->slug,
            'page_slug' => 'missing',
            'blocks' => [['type' => 'hero']],
        ])
        ->assertSee('not found');
});

it('errors getting a missing page on an existing site', function (): void {
    $site = Site::factory()->for($this->tenant)->create();

    WizardServer::actingAs($this->user)
        ->tool(GetPageTool::class, ['site_slug' => $site->slug, 'page_slug' => 'missing'])
        ->assertSee('not found');
});

it('exposes the tenant brand profile has-one relation', function (): void {
    $profile = BrandProfile::factory()->for($this->tenant)->create();

    expect($this->tenant->refresh()->brandProfile->id)->toBe($profile->id)
        ->and($this->tenant->brandProfiles)->toHaveCount(1);
});
