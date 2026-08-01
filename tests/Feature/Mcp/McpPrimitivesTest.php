<?php

declare(strict_types=1);

use App\Enums\ContentStatus;
use App\Enums\TenantRole;
use App\Mcp\Prompts\ImproveCopyPrompt;
use App\Mcp\Prompts\SeoAuditPrompt;
use App\Mcp\Prompts\WriteArticlePrompt;
use App\Mcp\Resources\BrandVoiceResource;
use App\Mcp\Resources\SiteSchemaResource;
use App\Mcp\Resources\SitesListResource;
use App\Mcp\Servers\WizardServer;
use App\Mcp\Tools\CreatePostDraftTool;
use App\Mcp\Tools\ListMediaTool;
use App\Mcp\Tools\ListPostsTool;
use App\Mcp\Tools\PublishSiteTool;
use App\Mcp\Tools\UpdatePostDraftTool;
use App\Models\AuditLog;
use App\Models\BrandProfile;
use App\Models\MediaAsset;
use App\Models\Post;
use App\Models\Site;
use App\Models\SiteVersion;
use App\Models\Tenant;
use App\Models\User;
use App\Support\CurrentTenant;
use Laravel\Mcp\Request;
use Laravel\Sanctum\Sanctum;
use Laravel\Sanctum\TransientToken;

beforeEach(function (): void {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create(['current_tenant_id' => $this->tenant->id]);
    $this->tenant->users()->attach($this->user);
    grantRole($this->tenant, $this->user);

    $this->user->withAccessToken(new TransientToken);
    resolve(CurrentTenant::class)->set($this->tenant);

    $this->site = Site::factory()->for($this->tenant)->create(['slug' => 'portal']);
});

it('lists posts filtered by status', function (): void {
    Post::factory()->for($this->site)->create(['title' => 'Publicado', 'status' => ContentStatus::Published]);
    Post::factory()->for($this->site)->create(['title' => 'Rascunho', 'status' => ContentStatus::Draft]);

    WizardServer::actingAs($this->user)
        ->tool(ListPostsTool::class, ['site_slug' => 'portal', 'status' => 'draft'])
        ->assertOk()
        ->assertSee('Rascunho')
        ->assertDontSee('Publicado');
});

it('creates a draft post with a unique slug and audit trail', function (): void {
    Post::factory()->for($this->site)->create(['slug' => 'guia-de-cms']);

    WizardServer::actingAs($this->user)
        ->tool(CreatePostDraftTool::class, [
            'site_slug' => 'portal',
            'title' => 'Guia de CMS',
            'body' => '<p>Conteúdo</p>',
            'seo_title' => 'Guia',
        ])
        ->assertOk()
        ->assertSee('guia-de-cms-2');

    $post = $this->site->posts()->where('slug', 'guia-de-cms-2')->sole();

    expect($post->status)->toBe(ContentStatus::Draft)
        ->and($post->seo['title'])->toBe('Guia')
        ->and(AuditLog::query()->where('action', 'post.created')->where('actor_type', 'agent')->exists())->toBeTrue();
});

it('updates drafts but refuses published posts', function (): void {
    $draft = Post::factory()->for($this->site)->create(['slug' => 'rascunho', 'status' => ContentStatus::Draft]);
    Post::factory()->for($this->site)->create(['slug' => 'publicado', 'status' => ContentStatus::Published]);

    WizardServer::actingAs($this->user)
        ->tool(UpdatePostDraftTool::class, ['site_slug' => 'portal', 'post_slug' => 'rascunho', 'title' => 'Novo título'])
        ->assertOk();

    expect($draft->refresh()->title)->toBe('Novo título');

    WizardServer::actingAs($this->user)
        ->tool(UpdatePostDraftTool::class, ['site_slug' => 'portal', 'post_slug' => 'publicado', 'title' => 'Hack'])
        ->assertHasErrors();

    WizardServer::actingAs($this->user)
        ->tool(UpdatePostDraftTool::class, ['site_slug' => 'portal', 'post_slug' => 'inexistente'])
        ->assertHasErrors();
});

it('lists media of the tenant only', function (): void {
    MediaAsset::factory()->for($this->tenant)->create(['filename' => 'logo.png']);
    MediaAsset::factory()->create(['filename' => 'alheio.png']);

    WizardServer::actingAs($this->user)
        ->tool(ListMediaTool::class, ['search' => 'logo'])
        ->assertOk()
        ->assertSee('logo.png')
        ->assertDontSee('alheio.png');
});

it('publishes a site only with the publish ability', function (): void {
    Sanctum::actingAs($this->user, ['write:draft']);
    resolve(CurrentTenant::class)->set($this->tenant);

    WizardServer::actingAs($this->user)
        ->tool(PublishSiteTool::class, ['site_slug' => 'portal'])
        ->assertHasErrors();

    expect(SiteVersion::query()->count())->toBe(0);

    Sanctum::actingAs($this->user, ['publish']);
    resolve(CurrentTenant::class)->set($this->tenant);

    WizardServer::actingAs($this->user)
        ->tool(PublishSiteTool::class, ['site_slug' => 'portal'])
        ->assertOk();

    $version = SiteVersion::query()->sole();

    expect($version->origin)->toBe('agent')
        ->and($this->site->refresh()->status)->toBe('published');
});

it('refuses publishing for users without the sites.publish permission', function (): void {
    $journalist = User::factory()->create(['current_tenant_id' => $this->tenant->id]);
    $this->tenant->users()->attach($journalist);
    grantRole($this->tenant, $journalist, TenantRole::Journalist);

    Sanctum::actingAs($journalist, ['publish']);
    resolve(CurrentTenant::class)->set($this->tenant);

    WizardServer::actingAs($journalist)
        ->tool(PublishSiteTool::class, ['site_slug' => 'portal'])
        ->assertHasErrors();
});

it('errors on unknown sites for every tool', function (): void {
    foreach ([ListPostsTool::class, PublishSiteTool::class] as $tool) {
        Sanctum::actingAs($this->user, ['read', 'publish']);
        resolve(CurrentTenant::class)->set($this->tenant);

        WizardServer::actingAs($this->user)
            ->tool($tool, ['site_slug' => 'inexistente'])
            ->assertHasErrors();
    }

    Sanctum::actingAs($this->user, ['write:draft']);
    resolve(CurrentTenant::class)->set($this->tenant);

    WizardServer::actingAs($this->user)
        ->tool(CreatePostDraftTool::class, ['site_slug' => 'inexistente', 'title' => 'X', 'body' => 'Y'])
        ->assertHasErrors();

    WizardServer::actingAs($this->user)
        ->tool(UpdatePostDraftTool::class, ['site_slug' => 'inexistente', 'post_slug' => 'x'])
        ->assertHasErrors();
});

it('exposes the sites list, published schema and brand voice as resources', function (): void {
    SiteVersion::factory()->for($this->site)->published()->create(['schema' => ['site' => ['name' => 'Portal']]]);
    BrandProfile::factory()->for($this->tenant)->create(['tone_of_voice' => 'direto e claro']);

    WizardServer::actingAs($this->user)
        ->resource(SitesListResource::class)
        ->assertOk()
        ->assertSee('portal');

    // Templated resources are addressed by resolved URI, so the slug
    // arrives through the request instead of the testing helper.
    $schema = (new SiteSchemaResource)->handle(new Request(['slug' => 'portal']));

    expect((string) $schema->content())->toContain('Portal');

    WizardServer::actingAs($this->user)
        ->resource(BrandVoiceResource::class)
        ->assertOk()
        ->assertSee('direto e claro');
});

it('degrades gracefully when the brand voice or schema are missing', function (): void {
    WizardServer::actingAs($this->user)
        ->resource(BrandVoiceResource::class)
        ->assertOk()
        ->assertSee('No brand voice configured');

    expect((new SiteSchemaResource)->handle(new Request(['slug' => 'portal']))->isError())->toBeTrue()
        ->and((new SiteSchemaResource)->handle(new Request(['slug' => 'inexistente']))->isError())->toBeTrue()
        ->and((new SiteSchemaResource)->uriTemplate()->__toString())->toBe('site://{slug}/schema');
});

it('serves the editorial prompts with their arguments', function (): void {
    WizardServer::actingAs($this->user)
        ->prompt(WriteArticlePrompt::class, ['site_slug' => 'portal', 'topic' => 'CMS agent-native'])
        ->assertOk()
        ->assertSee('tenant://brand-voice')
        ->assertSee('CMS agent-native');

    WizardServer::actingAs($this->user)
        ->prompt(ImproveCopyPrompt::class, ['site_slug' => 'portal', 'page_slug' => 'sobre'])
        ->assertOk()
        ->assertSee('update_blocks');

    WizardServer::actingAs($this->user)
        ->prompt(SeoAuditPrompt::class, ['site_slug' => 'portal'])
        ->assertOk()
        ->assertSee('meta title');

    expect((new WriteArticlePrompt)->arguments())->toHaveCount(3)
        ->and((new ImproveCopyPrompt)->arguments())->toHaveCount(3)
        ->and((new SeoAuditPrompt)->arguments())->toHaveCount(1);
});
