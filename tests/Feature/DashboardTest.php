<?php

declare(strict_types=1);

use App\Models\AiUsage;
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
});

it('renders the dashboard with real tenant stats', function (): void {
    $site = Site::factory()->for($this->tenant)->create();
    $site->pages()->create(['title' => 'Home', 'slug' => '/', 'status' => 'draft']);
    Post::factory()->for($site)->create();
    AiUsage::factory()->for($this->tenant)->count(2)->create(['credits' => 3]);

    $this->actingAs($this->user)
        ->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Dashboard/Index')
            ->where('stats.sites', 1)
            ->where('stats.pages', 1)
            ->where('stats.posts', 1)
            ->where('stats.ai_credits_used', 6)
            ->where('stats.ai_credits_monthly', $this->tenant->ai_credits_monthly)
            ->has('sites', 1)
        );
});

it('renders settings profile', function (): void {
    $this->actingAs($this->user)
        ->get('/settings')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page->component('Settings/Profile'));
});

it('renders settings members with real tenant members', function (): void {
    $this->actingAs($this->user)
        ->get('/settings/members')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Settings/Members')
            ->has('members', 1)
            ->where('members.0.email', $this->user->email)
        );
});

it('renders settings notifications', function (): void {
    $this->actingAs($this->user)
        ->get('/settings/notifications')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page->component('Settings/Notifications'));
});

it('renders settings security', function (): void {
    $this->actingAs($this->user)
        ->get('/settings/security')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page->component('Settings/Security'));
});
