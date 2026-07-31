<?php

declare(strict_types=1);

use App\Ai\Agents\ArticleWriterAgent;
use App\Ai\Agents\CopywriterAgent;
use App\Ai\Agents\SeoAgent;
use App\Enums\AiGenerationStatus;
use App\Events\AiGenerationUpdated;
use App\Jobs\Ai\GenerateArticle;
use App\Jobs\Ai\GenerateCopy;
use App\Jobs\Ai\GenerateSeo;
use App\Models\AiGeneration;
use App\Models\AiUsage;
use App\Models\BrandProfile;
use App\Models\Site;
use App\Models\Tenant;
use App\Models\User;
use App\Queue\Middleware\TenantAware;
use App\Services\AiCreditService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;

beforeEach(function (): void {
    $this->tenant = Tenant::factory()->create(['ai_credits_monthly' => 10]);
    $this->user = User::factory()->create(['current_tenant_id' => $this->tenant->id]);
    $this->tenant->users()->attach($this->user);
    grantRole($this->tenant, $this->user);
    $this->site = Site::factory()->for($this->tenant)->create();
});

it('queues a copy generation and returns 202', function (): void {
    Queue::fake();

    $this->actingAs($this->user)
        ->postJson(sprintf('/sites/%s/ai/copy', $this->site->id), [
            'block_type' => 'cta',
            'briefing' => 'Call to action for a summer sale',
        ])
        ->assertAccepted()
        ->assertJsonStructure(['generation_id']);

    Queue::assertPushedOn('ai', GenerateCopy::class);

    $generation = AiGeneration::query()->sole();

    expect($generation->status)->toBe(AiGenerationStatus::Queued)
        ->and($generation->agent)->toBe('copywriter')
        ->and($generation->site_id)->toBe($this->site->id);
});

it('queues article and seo generations with tenant-aware jobs', function (): void {
    Queue::fake();

    $this->actingAs($this->user)
        ->postJson(sprintf('/sites/%s/ai/article', $this->site->id), [
            'briefing' => 'Artigo sobre CMS',
            'language' => 'pt-PT',
        ])
        ->assertAccepted();

    $this->actingAs($this->user)
        ->postJson(sprintf('/sites/%s/ai/seo', $this->site->id), [
            'briefing' => 'Conteúdo da página',
        ])
        ->assertAccepted();

    Queue::assertPushedOn('ai', GenerateArticle::class);
    Queue::assertPushedOn('ai', GenerateSeo::class);
    Queue::assertPushed(GenerateArticle::class, fn (GenerateArticle $job): bool => $job->middleware()[0] instanceof TenantAware);

    expect(AiGeneration::query()->count())->toBe(2);
});

it('completes a seo generation with structured output', function (): void {
    SeoAgent::fake([['meta_title' => 'Título', 'meta_description' => 'Descrição']]);

    $generation = AiGeneration::factory()->for($this->tenant)->create([
        'site_id' => $this->site->id,
        'agent' => 'seo',
        'input' => ['briefing' => 'Conteúdo sobre jardinagem'],
    ]);

    new GenerateSeo($generation->id, $this->tenant->id)->handle(resolve(AiCreditService::class));

    $generation->refresh();

    expect($generation->status)->toBe(AiGenerationStatus::Completed)
        ->and($generation->output)->toMatchArray(['meta_title' => 'Título']);
});

it('runs the copy job, records real token usage and broadcasts', function (): void {
    Event::fake([AiGenerationUpdated::class]);
    CopywriterAgent::fake(['Buy now and save 20%']);

    $generation = AiGeneration::factory()->for($this->tenant)->create([
        'user_id' => $this->user->id,
        'site_id' => $this->site->id,
        'agent' => 'copywriter',
        'input' => ['block_type' => 'cta', 'briefing' => 'summer sale', 'current_content' => 'old copy'],
    ]);

    new GenerateCopy($generation->id, $this->tenant->id)->handle(resolve(AiCreditService::class));

    CopywriterAgent::assertPrompted(fn ($prompt): bool => $prompt->contains('summer sale'));

    $generation->refresh();

    expect($generation->status)->toBe(AiGenerationStatus::Completed)
        ->and($generation->output)->toBe(['copy' => 'Buy now and save 20%'])
        ->and(AiUsage::query()->where('agent', 'copywriter')->count())->toBe(1);

    Event::assertDispatchedTimes(AiGenerationUpdated::class, 2);
});

it('injects the brand voice into agent instructions', function (): void {
    BrandProfile::factory()->for($this->tenant)->create(['tone_of_voice' => 'pirate speak']);

    $agent = new CopywriterAgent($this->tenant->brandProfile);

    expect($agent->instructions())->toContain('pirate speak');
});

it('creates an article draft with a unique slug from the job', function (): void {
    ArticleWriterAgent::fake([
        [
            'title' => 'O futuro dos CMS',
            'excerpt' => 'Um resumo curto.',
            'body' => '<p>Artigo completo.</p>',
            'seo_title' => 'Futuro CMS',
            'seo_description' => 'Descrição SEO.',
        ],
        [
            'title' => 'O futuro dos CMS',
            'excerpt' => 'Outro resumo.',
            'body' => '<p>Outro artigo.</p>',
        ],
    ]);

    foreach (range(1, 2) as $round) {
        $generation = AiGeneration::factory()->for($this->tenant)->create([
            'user_id' => $this->user->id,
            'site_id' => $this->site->id,
            'agent' => 'article_writer',
            'input' => ['briefing' => 'Artigo sobre o futuro dos CMS '.$round],
        ]);

        new GenerateArticle($generation->id, $this->tenant->id)->handle(resolve(AiCreditService::class));

        expect($generation->refresh()->status)->toBe(AiGenerationStatus::Completed);
    }

    $slugs = $this->site->posts()->pluck('slug')->sort()->values()->all();

    expect($slugs)->toBe(['o-futuro-dos-cms', 'o-futuro-dos-cms-2'])
        ->and($this->site->posts()->first()?->author_id)->toBe($this->user->id);
});

it('marks the generation as failed and refunds on unstructured responses', function (): void {
    SeoAgent::fake(['plain text, not structured']);

    $generation = AiGeneration::factory()->for($this->tenant)->create([
        'site_id' => $this->site->id,
        'agent' => 'seo',
        'input' => ['briefing' => 'Conteúdo'],
    ]);

    new GenerateSeo($generation->id, $this->tenant->id)->handle(resolve(AiCreditService::class));

    $generation->refresh();

    expect($generation->status)->toBe(AiGenerationStatus::Failed)
        ->and($generation->error)->not->toBeNull()
        ->and(AiUsage::query()->count())->toBe(0);
});

it('fails the generation without charging when the tenant is out of credits', function (): void {
    AiUsage::factory()->for($this->tenant)->count(10)->create();

    $generation = AiGeneration::factory()->for($this->tenant)->create([
        'site_id' => $this->site->id,
        'agent' => 'seo',
        'input' => ['briefing' => 'Conteúdo'],
    ]);

    new GenerateSeo($generation->id, $this->tenant->id)->handle(resolve(AiCreditService::class));

    expect($generation->refresh()->status)->toBe(AiGenerationStatus::Failed)
        ->and($generation->error)->toContain('Out of AI credits')
        ->and(AiUsage::query()->count())->toBe(10);
});

it('blocks new generations at the http layer when out of credits', function (): void {
    AiUsage::factory()->for($this->tenant)->count(10)->create();

    $this->actingAs($this->user)
        ->postJson(sprintf('/sites/%s/ai/copy', $this->site->id), [
            'block_type' => 'cta',
            'briefing' => 'More copy please',
        ])
        ->assertStatus(402);
});

it('exposes generation state on the polling endpoint scoped to the tenant', function (): void {
    $generation = AiGeneration::factory()->for($this->tenant)->create([
        'agent' => 'seo',
        'status' => AiGenerationStatus::Completed,
        'output' => ['meta_title' => 'Título'],
    ]);

    $this->actingAs($this->user)
        ->getJson('/ai/generations/'.$generation->id)
        ->assertOk()
        ->assertJsonPath('status', 'completed')
        ->assertJsonPath('output.meta_title', 'Título');

    $foreign = AiGeneration::factory()->create();

    $this->actingAs($this->user)
        ->getJson('/ai/generations/'.$foreign->id)
        ->assertNotFound();
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
