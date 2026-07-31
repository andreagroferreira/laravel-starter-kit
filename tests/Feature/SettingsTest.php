<?php

declare(strict_types=1);

use App\Enums\TenantRole;
use App\Models\AuditLog;
use App\Models\BrandProfile;
use App\Models\Tenant;
use App\Models\User;
use App\Support\McpSnippets;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create(['current_tenant_id' => $this->tenant->id]);
    $this->tenant->users()->attach($this->user);
    grantRole($this->tenant, $this->user);
});

it('renders the brand voice page empty and saves a profile', function (): void {
    $this->actingAs($this->user)
        ->get('/settings/brand')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Settings/Brand')
            ->where('profile', null)
        );

    $this->actingAs($this->user)
        ->put('/settings/brand', [
            'name' => 'Acme',
            'tone_of_voice' => 'casual e direto',
            'glossary' => ['CMS' => 'gestor de conteúdos'],
            'examples' => ['Escrevemos assim.'],
        ])
        ->assertRedirect();

    $profile = BrandProfile::query()->sole();

    expect($profile->tone_of_voice)->toBe('casual e direto')
        ->and($profile->glossary)->toBe(['CMS' => 'gestor de conteúdos'])
        ->and($profile->examples)->toBe(['Escrevemos assim.']);
});

it('updates an existing brand profile', function (): void {
    BrandProfile::factory()->for($this->tenant)->create(['tone_of_voice' => 'old']);

    $this->actingAs($this->user)
        ->put('/settings/brand', ['name' => 'Acme', 'tone_of_voice' => 'new tone']);

    expect(BrandProfile::query()->count())->toBe(1)
        ->and(BrandProfile::query()->sole()->tone_of_voice)->toBe('new tone');
});

it('validates brand profile input', function (): void {
    $this->actingAs($this->user)
        ->put('/settings/brand', ['name' => ''])
        ->assertSessionHasErrors('name');
});

it('invites a member by email with a role', function (): void {
    $this->actingAs($this->user)
        ->post('/settings/members', [
            'email' => 'new@member.com',
            'role' => 'editor',
        ])
        ->assertRedirect();

    $member = User::query()->where('email', 'new@member.com')->sole();

    expect($this->tenant->users()->where('users.id', $member->id)->exists())->toBeTrue();

    setPermissionsTeamId($this->tenant->id);
    expect($member->hasRole(TenantRole::Editor->value))->toBeTrue();
});

it('attaches an existing user when inviting', function (): void {
    $existing = User::factory()->create(['email' => 'exists@member.com']);

    $this->actingAs($this->user)
        ->post('/settings/members', ['email' => 'exists@member.com', 'role' => 'marketeer']);

    expect(User::query()->where('email', 'exists@member.com')->count())->toBe(1)
        ->and($this->tenant->users()->where('users.id', $existing->id)->exists())->toBeTrue();
});

it('removes a member but not yourself', function (): void {
    $member = User::factory()->create();
    $this->tenant->users()->attach($member);

    $this->actingAs($this->user)
        ->delete('/settings/members/'.$member->id)
        ->assertRedirect();

    expect($this->tenant->users()->where('users.id', $member->id)->exists())->toBeFalse();

    $this->actingAs($this->user)
        ->delete('/settings/members/'.$this->user->id)
        ->assertStatus(422);
});

it('lists creates and revokes api tokens', function (): void {
    $this->actingAs($this->user)
        ->post('/settings/api/tokens', [
            'name' => 'Claude desktop',
            'abilities' => ['read', 'write:draft'],
        ])
        ->assertRedirect();

    $token = $this->user->tokens()->sole();

    expect($token->name)->toBe('Claude desktop')
        ->and($token->can('write:draft'))->toBeTrue()
        ->and($token->can('publish'))->toBeFalse();

    $this->actingAs($this->user)
        ->get('/settings/api')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Settings/Api')
            ->has('tokens', 1)
            ->where('tokens.0.name', 'Claude desktop')
            ->has('availableAbilities', 4)
            ->has('mcpEndpoint')
        );

    $this->actingAs($this->user)
        ->delete('/settings/api/tokens/'.$token->id);

    expect($this->user->tokens()->count())->toBe(0);
});

it('rejects invalid token abilities', function (): void {
    $this->actingAs($this->user)
        ->post('/settings/api/tokens', ['name' => 'x', 'abilities' => ['god-mode']])
        ->assertSessionHasErrors('abilities.0');
});

it('lists the audit log with actor filter', function (): void {
    AuditLog::factory()->for($this->tenant)->create(['actor_type' => 'human', 'action' => 'page.published']);
    AuditLog::factory()->for($this->tenant)->create(['actor_type' => 'agent', 'action' => 'page.created']);

    $this->actingAs($this->user)
        ->get('/settings/audit')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Settings/Audit')
            ->has('logs.data', 2)
        );

    $this->actingAs($this->user)
        ->get('/settings/audit?actor=agent')
        ->assertInertia(fn (Assert $page): Assert => $page
            ->has('logs.data', 1)
            ->where('logs.data.0.action', 'page.created')
        );
});

it('exposes mcp snippets', function (): void {
    $snippets = McpSnippets::make('https://app.test/mcp/wizard');

    expect($snippets['claude'])->toContain('claude mcp add')
        ->and($snippets['codex'])->toContain('mcp_servers.wizard');
});
