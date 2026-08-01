<?php

declare(strict_types=1);

use App\Models\Form;
use App\Models\MediaAsset;
use App\Models\Page;
use App\Models\Site;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantProvisioner;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create(['current_tenant_id' => $this->tenant->id]);
    $this->tenant->users()->attach($this->user);
    grantRole($this->tenant, $this->user);

    $this->site = Site::factory()->for($this->tenant)->create([
        'settings' => ['design' => ['colors' => ['accent' => '#ff0000'], 'radius' => '1rem']],
    ]);
    $this->page = Page::factory()->for($this->site)->create();
});

it('feeds the editor with blocks in order, site design tokens and forms', function (): void {
    $form = Form::factory()->for($this->site)->create(['name' => 'contacto']);

    $this->page->blocks()->create(['type' => 'cta', 'content' => ['heading' => 'B'], 'sort_order' => 1]);
    $this->page->blocks()->create(['type' => 'hero', 'content' => ['heading' => 'A'], 'sort_order' => 0]);

    $this->actingAs($this->user)
        ->get(sprintf('/sites/%s/pages/%s', $this->site->id, $this->page->id))
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Pages/Edit')
            ->has('blocks', 2)
            ->where('blocks.0.type', 'hero')
            ->where('blocks.1.type', 'cta')
            ->where('site.settings.design.radius', '1rem')
            ->has('forms', 1)
            ->where('forms.0.name', 'contacto')
        );
});

it('persists the block list sent by the editor, replacing the previous one', function (): void {
    $this->page->blocks()->create(['type' => 'hero', 'content' => ['heading' => 'Antigo'], 'sort_order' => 0]);

    $this->actingAs($this->user)
        ->put(sprintf('/sites/%s/pages/%s', $this->site->id, $this->page->id), [
            'title' => 'Página editada',
            'slug' => $this->page->slug,
            'seo' => ['title' => 'SEO', 'description' => 'Descrição'],
            'blocks' => [
                ['type' => 'hero', 'content' => ['heading' => 'Novo', 'subheading' => 'Sub']],
                ['type' => 'faq', 'content' => ['heading' => 'FAQ', 'items' => [['question' => 'P?', 'answer' => 'R.']]]],
            ],
        ])
        ->assertRedirect();

    $blocks = $this->page->blocks()->orderBy('sort_order')->get();

    expect($blocks)->toHaveCount(2)
        ->and($blocks[0]->type)->toBe('hero')
        ->and($blocks[0]->content['heading'])->toBe('Novo')
        ->and($blocks[1]->type)->toBe('faq')
        ->and($this->page->refresh()->title)->toBe('Página editada');
});

it('serves the media picker feed scoped to the tenant', function (): void {
    MediaAsset::factory()->for($this->tenant)->create(['filename' => 'logo.png']);
    MediaAsset::factory()->create(['filename' => 'alheio.png']);

    $this->actingAs($this->user)
        ->getJson('/media/picker')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.filename', 'logo.png');

    $this->actingAs($this->user)
        ->getJson('/media/picker?search=inexistente')
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

it('rejects the media picker for roles without media permission', function (): void {
    $outsider = User::factory()->create(['current_tenant_id' => $this->tenant->id]);
    $this->tenant->users()->attach($outsider);
    resolve(TenantProvisioner::class)->provision($this->tenant);
    setPermissionsTeamId($this->tenant->id);
    $outsider->syncRoles([]);

    $this->actingAs($outsider)->getJson('/media/picker')->assertForbidden();
});
