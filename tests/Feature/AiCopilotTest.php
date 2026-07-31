<?php

declare(strict_types=1);

use App\Ai\Agents\ArticleWriterAgent;
use App\Ai\Agents\CopywriterAgent;
use App\Ai\Agents\SeoAgent;
use App\Models\AiUsage;
use App\Models\BrandProfile;
use App\Models\Site;
use App\Models\Tenant;
use App\Models\User;
use App\Services\AiCreditService;

beforeEach(function (): void {
    $this->tenant = Tenant::factory()->create(['ai_credits_monthly' => 10]);
    $this->user = User::factory()->create(['current_tenant_id' => $this->tenant->id]);
    $this->tenant->users()->attach($this->user);
    $this->site = Site::factory()->for($this->tenant)->create();
});

it('generates block copy and records usage', function (): void {
    CopywriterAgent::fake(['Buy now and save 20%']);

    $this->actingAs($this->user)
        ->postJson(sprintf('/sites/%s/ai/copy', $this->site->id), [
            'block_type' => 'cta',
            'briefing' => 'Call to action for a summer sale',
        ])
        ->assertOk()
        ->assertJsonPath('copy', 'Buy now and save 20%');

    CopywriterAgent::assertPrompted(fn ($prompt): bool => $prompt->contains('summer sale'));

    expect(AiUsage::query()->where('agent', 'copywriter')->where('resource_id', $this->site->id)->exists())->toBeTrue();
});

it('injects the brand voice into agent instructions', function (): void {
    CopywriterAgent::fake(['copy']);

    BrandProfile::factory()->for($this->tenant)->create(['tone_of_voice' => 'pirate speak']);

    $this->actingAs($this->user)
        ->postJson(sprintf('/sites/%s/ai/copy', $this->site->id), [
            'block_type' => 'hero',
            'briefing' => 'Hero heading',
        ])
        ->assertOk();

    $agent = new CopywriterAgent($this->tenant->brandProfile);

    expect($agent->instructions())->toContain('pirate speak');
});

it('creates an article draft from a briefing', function (): void {
    ArticleWriterAgent::fake([[
        'title' => 'O futuro dos CMS',
        'excerpt' => 'Um resumo curto.',
        'body' => '<p>Artigo completo.</p>',
        'seo_title' => 'Futuro CMS',
        'seo_description' => 'Descrição SEO.',
    ]]);

    $this->actingAs($this->user)
        ->postJson(sprintf('/sites/%s/ai/article', $this->site->id), [
            'briefing' => 'Artigo sobre o futuro dos CMS com AI',
        ])
        ->assertCreated()
        ->assertJsonPath('article.title', 'O futuro dos CMS')
        ->assertJsonPath('status', 'draft');

    $post = $this->site->posts()->sole();

    expect($post->title)->toBe('O futuro dos CMS')
        ->and($post->seo['title'])->toBe('Futuro CMS')
        ->and($post->author_id)->toBe($this->user->id);

    expect(AiUsage::query()->where('agent', 'article_writer')->exists())->toBeTrue();
});

it('generates seo meta', function (): void {
    SeoAgent::fake([['meta_title' => 'Título', 'meta_description' => 'Descrição']]);

    $this->actingAs($this->user)
        ->postJson(sprintf('/sites/%s/ai/seo', $this->site->id), [
            'block_type' => 'rich_text',
            'briefing' => 'Conteúdo da página sobre jardinagem',
        ])
        ->assertOk()
        ->assertJsonPath('meta_title', 'Título');
});

it('blocks generation when the tenant is out of credits', function (): void {
    CopywriterAgent::fake(['copy']);

    AiUsage::factory()->for($this->tenant)->count(10)->create();

    $this->actingAs($this->user)
        ->postJson(sprintf('/sites/%s/ai/copy', $this->site->id), [
            'block_type' => 'cta',
            'briefing' => 'More copy please',
        ])
        ->assertStatus(402);
});

it('tracks monthly credit usage per tenant', function (): void {
    $service = new AiCreditService;

    AiUsage::factory()->for($this->tenant)->count(3)->create(['credits' => 2]);

    expect($service->usedThisMonth($this->tenant))->toBe(6)
        ->and($service->hasCredits($this->tenant, 4))->toBeTrue()
        ->and($service->hasCredits($this->tenant, 5))->toBeFalse();
});

it('ignores usage from previous months', function (): void {
    $service = new AiCreditService;

    AiUsage::factory()->for($this->tenant)->create([
        'credits' => 50,
        'created_at' => now()->subMonths(2),
    ]);

    expect($service->usedThisMonth($this->tenant))->toBe(0);
});
