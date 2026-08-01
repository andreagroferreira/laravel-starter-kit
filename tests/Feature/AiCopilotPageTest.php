<?php

declare(strict_types=1);

use App\Enums\AiGenerationStatus;
use App\Models\AiGeneration;
use App\Models\AiUsage;
use App\Models\Site;
use App\Models\Tenant;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->tenant = Tenant::factory()->create(['ai_credits_monthly' => 100]);
    $this->user = User::factory()->create(['current_tenant_id' => $this->tenant->id]);
    $this->tenant->users()->attach($this->user);
    grantRole($this->tenant, $this->user);
});

it('renders the ai copilot page with sites, credits and history', function (): void {
    $site = Site::factory()->for($this->tenant)->create(['name' => 'Portal']);
    AiUsage::factory()->for($this->tenant)->count(4)->create(['credits' => 2]);

    AiGeneration::factory()->for($this->tenant)->create([
        'site_id' => $site->id,
        'agent' => 'article_writer',
        'status' => AiGenerationStatus::Completed,
        'output' => ['post_id' => 'abc'],
    ]);

    $this->actingAs($this->user)
        ->get('/ai')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Ai/Index')
            ->has('sites', 1)
            ->where('sites.0.name', 'Portal')
            ->where('credits.used', 8)
            ->where('credits.monthly', 100)
            ->has('generations', 1)
            ->where('generations.0.agent', 'article_writer')
            ->where('generations.0.site', 'Portal')
            ->where('generations.0.status', 'completed')
        );
});

it('does not leak generations from other tenants', function (): void {
    AiGeneration::factory()->create();

    $this->actingAs($this->user)
        ->get('/ai')
        ->assertInertia(fn (Assert $page): Assert => $page->has('generations', 0));
});
