<?php

declare(strict_types=1);

use App\Ai\Agents\SocialCopyAgent;
use App\Enums\IntegrationProvider;
use App\Enums\TenantRole;
use App\Jobs\Integrations\PublishSocialPost;
use App\Jobs\Integrations\SyncGoogleAnalytics;
use App\Jobs\Integrations\SyncSearchConsole;
use App\Models\BrandProfile;
use App\Models\Integration;
use App\Models\MetricSnapshot;
use App\Models\Site;
use App\Models\SocialPost;
use App\Models\Tenant;
use App\Models\User;
use App\Queue\Middleware\TenantAware;
use App\Services\Integrations\GoogleTokenRefresher;
use App\Services\SiteService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;

beforeEach(function (): void {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create(['current_tenant_id' => $this->tenant->id]);
    $this->tenant->users()->attach($this->user);
    grantRole($this->tenant, $this->user);
    $this->site = Site::factory()->for($this->tenant)->create();
});

it('lists integrations, providers and recent metrics', function (): void {
    config()->set('services.google.client_id', 'google-id');
    config()->set('services.facebook.client_id', '');

    Integration::factory()->for($this->tenant)->create([
        'site_id' => $this->site->id,
        'provider' => IntegrationProvider::GoogleAnalytics,
        'external_id' => '123456',
    ]);

    MetricSnapshot::factory()->for($this->tenant)->create(['site_id' => $this->site->id]);

    $this->actingAs($this->user)
        ->get('/settings/integrations')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Settings/Integrations')
            ->has('integrations', 1)
            ->where('integrations.0.external_id', '123456')
            ->has('metrics', 1)
            // Google está configurado, Meta ainda não — a UI mostra pendente.
            ->where('providers.0.configured', true)
            ->where('providers.2.configured', false)
        );
});

it('lets owners configure and disconnect an integration', function (): void {
    $integration = Integration::factory()->for($this->tenant)->create(['site_id' => null]);

    $this->actingAs($this->user)
        ->put('/settings/integrations/'.$integration->id, [
            'site_id' => $this->site->id,
            'external_id' => 'G-ABC123',
        ])
        ->assertRedirect();

    expect($integration->refresh()->external_id)->toBe('G-ABC123')
        ->and($integration->site_id)->toBe($this->site->id);

    $this->actingAs($this->user)
        ->delete('/settings/integrations/'.$integration->id)
        ->assertRedirect();

    expect(Integration::query()->count())->toBe(0);
});

it('restricts integrations to owners', function (): void {
    $editor = User::factory()->create(['current_tenant_id' => $this->tenant->id]);
    $this->tenant->users()->attach($editor);
    grantRole($this->tenant, $editor, TenantRole::Editor);

    $this->actingAs($editor)->get('/settings/integrations')->assertForbidden();
    $this->actingAs($editor)->get('/settings/integrations/google_analytics/redirect')->assertForbidden();
});

it('redirects to the provider and stores the tokens on callback', function (): void {
    $redirect = Mockery::mock(GoogleProvider::class);
    $redirect->shouldReceive('scopes')->once()->andReturnSelf();
    $redirect->shouldReceive('with')->once()->andReturnSelf();
    $redirect->shouldReceive('redirect')->once()->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));

    Socialite::shouldReceive('driver')->with('google')->once()->andReturn($redirect);

    $this->actingAs($this->user)
        ->get('/settings/integrations/google_analytics/redirect')
        ->assertRedirectContains('accounts.google.com');

    $oauthUser = new Laravel\Socialite\Two\User;
    $oauthUser->token = 'access-123';
    $oauthUser->refreshToken = 'refresh-123';
    $oauthUser->expiresIn = 3600;
    $oauthUser->map(['email' => 'dono@exemplo.pt']);

    $callback = Mockery::mock(Provider::class);
    $callback->shouldReceive('user')->once()->andReturn($oauthUser);

    Socialite::shouldReceive('driver')->with('google')->once()->andReturn($callback);

    $this->actingAs($this->user)
        ->get('/settings/integrations/google_analytics/callback')
        ->assertRedirect('/settings/integrations');

    $integration = Integration::query()->sole();

    expect($integration->access_token)->toBe('access-123')
        ->and($integration->refresh_token)->toBe('refresh-123')
        ->and($integration->status)->toBe('connected')
        ->and($integration->meta['email'])->toBe('dono@exemplo.pt');
});

it('recovers when the oauth callback fails', function (): void {
    $failing = Mockery::mock(Provider::class);
    $failing->shouldReceive('user')->once()->andThrow(new RuntimeException('state inválido'));

    Socialite::shouldReceive('driver')->with('facebook')->once()->andReturn($failing);

    $this->actingAs($this->user)
        ->get('/settings/integrations/meta/callback')
        ->assertRedirect('/settings/integrations');

    expect(Integration::query()->count())->toBe(0);
});

it('exposes provider metadata used by the ui and oauth flow', function (): void {
    expect(IntegrationProvider::values())->toBe(['google_analytics', 'search_console', 'meta'])
        ->and(IntegrationProvider::SearchConsole->driver())->toBe('google')
        ->and(IntegrationProvider::Meta->driver())->toBe('facebook')
        ->and(IntegrationProvider::Meta->label())->toContain('Meta')
        ->and(IntegrationProvider::SearchConsole->label())->toBe('Search Console')
        ->and(IntegrationProvider::Meta->scopes())->toContain('pages_manage_posts')
        ->and(IntegrationProvider::SearchConsole->scopes())->toHaveCount(2);
});

it('ignores syncs and publishes for integrations pointing nowhere', function (): void {
    Http::fake();

    // Sem external_id: o sync sai sem chamar a API.
    $noProperty = Integration::factory()->for($this->tenant)->create([
        'site_id' => $this->site->id,
        'external_id' => null,
        'expires_at' => now()->addHour(),
    ]);

    new SyncGoogleAnalytics($noProperty->id, $this->tenant->id)->handle(resolve(GoogleTokenRefresher::class));

    $noPropertyGsc = Integration::factory()->for($this->tenant)->create([
        'site_id' => $this->site->id,
        'provider' => IntegrationProvider::SearchConsole,
        'external_id' => null,
        'expires_at' => now()->addHour(),
    ]);

    new SyncSearchConsole($noPropertyGsc->id, $this->tenant->id)->handle(resolve(GoogleTokenRefresher::class));

    // Token irrecuperável: o job desiste antes de chamar a API.
    $broken = Integration::factory()->for($this->tenant)->create([
        'site_id' => Site::factory()->for($this->tenant)->create()->id,
        'external_id' => '1',
        'expires_at' => now()->subMinute(),
        'refresh_token' => null,
    ]);

    new SyncGoogleAnalytics($broken->id, $this->tenant->id)->handle(resolve(GoogleTokenRefresher::class));

    $brokenGsc = Integration::factory()->for($this->tenant)->create([
        'site_id' => $broken->site_id,
        'provider' => IntegrationProvider::SearchConsole,
        'external_id' => 'https://x.pt/',
        'expires_at' => now()->subMinute(),
        'refresh_token' => null,
    ]);

    new SyncSearchConsole($brokenGsc->id, $this->tenant->id)->handle(resolve(GoogleTokenRefresher::class));

    expect(MetricSnapshot::query()->count())->toBe(0);
    Http::assertNothingSent();
});

it('binds the tenant on every integration job and relates posts to sites', function (): void {
    $post = SocialPost::factory()->for($this->tenant)->create(['site_id' => $this->site->id]);
    $integration = Integration::factory()->for($this->tenant)->create(['site_id' => $this->site->id]);

    expect($post->site->id)->toBe($this->site->id)
        ->and($integration->site?->id)->toBe($this->site->id)
        ->and(new PublishSocialPost($post->id, $this->tenant->id)->middleware()[0])->toBeInstanceOf(TenantAware::class)
        ->and(new SyncGoogleAnalytics($integration->id, $this->tenant->id)->middleware()[0])->toBeInstanceOf(TenantAware::class)
        ->and(new SyncSearchConsole($integration->id, $this->tenant->id)->middleware()[0])->toBeInstanceOf(TenantAware::class);
});

it('treats a token response without an access token as an error', function (): void {
    Http::fake(['oauth2.googleapis.com/*' => Http::response(['expires_in' => 3600])]);

    $integration = Integration::factory()->for($this->tenant)->create([
        'expires_at' => now()->subMinute(),
        'refresh_token' => 'refresh-estranho',
    ]);

    expect(resolve(GoogleTokenRefresher::class)->accessToken($integration))->toBeNull()
        ->and($integration->refresh()->status)->toBe('error');
});

it('skips integrations without an external id when building the schema', function (): void {
    Integration::factory()->for($this->tenant)->create([
        'site_id' => $this->site->id,
        'provider' => IntegrationProvider::GoogleAnalytics,
        'external_id' => null,
    ]);

    $schema = resolve(SiteService::class)->buildSchema($this->site);

    expect($schema['site']['integrations'])->toBe([]);
});

it('scopes due social posts to the ones already past their time', function (): void {
    $due = SocialPost::factory()->for($this->tenant)->create([
        'site_id' => $this->site->id,
        'status' => 'scheduled',
        'scheduled_at' => now()->subHour(),
    ]);

    SocialPost::factory()->for($this->tenant)->create([
        'site_id' => $this->site->id,
        'status' => 'draft',
        'scheduled_at' => now()->subHour(),
    ]);

    expect(SocialPost::query()->due()->pluck('id')->all())->toBe([$due->id]);
});

it('404s on unknown oauth providers', function (): void {
    $this->actingAs($this->user)
        ->get('/settings/integrations/tiktok/redirect')
        ->assertNotFound();

    $this->actingAs($this->user)
        ->get('/settings/integrations/tiktok/callback')
        ->assertNotFound();
});

it('refreshes expired google tokens before syncing', function (): void {
    Http::fake([
        'oauth2.googleapis.com/*' => Http::response(['access_token' => 'novo-token', 'expires_in' => 3600]),
    ]);

    $integration = Integration::factory()->for($this->tenant)->create([
        'expires_at' => now()->subMinute(),
        'refresh_token' => 'refresh-abc',
    ]);

    expect(resolve(GoogleTokenRefresher::class)->accessToken($integration))->toBe('novo-token')
        ->and($integration->refresh()->access_token)->toBe('novo-token');

    // Um token válido não gasta chamadas.
    Http::fake();
    $valid = Integration::factory()->for($this->tenant)->create(['expires_at' => now()->addHour(), 'access_token' => 'ainda-bom']);

    expect(resolve(GoogleTokenRefresher::class)->accessToken($valid))->toBe('ainda-bom');
    Http::assertNothingSent();
});

it('marks the integration as errored when the refresh fails', function (): void {
    Http::fake(['oauth2.googleapis.com/*' => Http::response([], 400)]);

    $integration = Integration::factory()->for($this->tenant)->create([
        'expires_at' => now()->subMinute(),
        'refresh_token' => 'refresh-invalido',
    ]);

    expect(resolve(GoogleTokenRefresher::class)->accessToken($integration))->toBeNull()
        ->and($integration->refresh()->status)->toBe('error');

    $noRefresh = Integration::factory()->for($this->tenant)->create([
        'expires_at' => now()->subMinute(),
        'refresh_token' => null,
    ]);

    expect(resolve(GoogleTokenRefresher::class)->accessToken($noRefresh))->toBeNull()
        ->and($noRefresh->refresh()->status)->toBe('error');
});

it('stores a ga4 snapshot for yesterday', function (): void {
    Http::fake([
        'analyticsdata.googleapis.com/*' => Http::response([
            'rows' => [['metricValues' => [['value' => '120'], ['value' => '90'], ['value' => '310']]]],
        ]),
    ]);

    $integration = Integration::factory()->for($this->tenant)->create([
        'site_id' => $this->site->id,
        'provider' => IntegrationProvider::GoogleAnalytics,
        'external_id' => '999',
        'expires_at' => now()->addHour(),
    ]);

    new SyncGoogleAnalytics($integration->id, $this->tenant->id)->handle(resolve(GoogleTokenRefresher::class));

    $snapshot = MetricSnapshot::query()->sole();

    expect($snapshot->metrics)->toBe(['sessions' => 120, 'users' => 90, 'pageviews' => 310])
        ->and($snapshot->metric_date->toDateString())->toBe(now()->subDay()->toDateString());

    // Re-correr o sync não duplica.
    new SyncGoogleAnalytics($integration->id, $this->tenant->id)->handle(resolve(GoogleTokenRefresher::class));

    expect(MetricSnapshot::query()->count())->toBe(1);
});

it('stores a search console snapshot with top queries', function (): void {
    Http::fake([
        'searchconsole.googleapis.com/*' => Http::response([
            'rows' => [
                ['keys' => ['cms headless'], 'clicks' => 10.0, 'impressions' => 100.0, 'position' => 4.25],
                ['keys' => ['cms com ai'], 'clicks' => 5.0, 'impressions' => 50.0, 'position' => 8.5],
            ],
        ]),
    ]);

    $integration = Integration::factory()->for($this->tenant)->create([
        'site_id' => $this->site->id,
        'provider' => IntegrationProvider::SearchConsole,
        'external_id' => 'https://exemplo.pt/',
        'expires_at' => now()->addHour(),
    ]);

    new SyncSearchConsole($integration->id, $this->tenant->id)->handle(resolve(GoogleTokenRefresher::class));

    $metrics = MetricSnapshot::query()->sole()->metrics;

    expect($metrics['clicks'])->toBe(15)
        ->and($metrics['impressions'])->toBe(150)
        ->and($metrics['queries'][0]['query'])->toBe('cms headless');
});

it('skips syncs for integrations without a site or property', function (): void {
    Http::fake();

    $incomplete = Integration::factory()->for($this->tenant)->create(['site_id' => null]);

    new SyncGoogleAnalytics($incomplete->id, $this->tenant->id)->handle(resolve(GoogleTokenRefresher::class));
    new SyncSearchConsole($incomplete->id, $this->tenant->id)->handle(resolve(GoogleTokenRefresher::class));

    expect(MetricSnapshot::query()->count())->toBe(0);
    Http::assertNothingSent();
});

it('marks the integration as errored when a sync call fails', function (): void {
    Http::fake([
        'analyticsdata.googleapis.com/*' => Http::response([], 500),
        'searchconsole.googleapis.com/*' => Http::response([], 500),
    ]);

    $ga = Integration::factory()->for($this->tenant)->create([
        'site_id' => $this->site->id,
        'external_id' => '1',
        'expires_at' => now()->addHour(),
    ]);
    $gsc = Integration::factory()->for($this->tenant)->create([
        'site_id' => $this->site->id,
        'provider' => IntegrationProvider::SearchConsole,
        'external_id' => 'https://x.pt/',
        'expires_at' => now()->addHour(),
    ]);

    new SyncGoogleAnalytics($ga->id, $this->tenant->id)->handle(resolve(GoogleTokenRefresher::class));
    new SyncSearchConsole($gsc->id, $this->tenant->id)->handle(resolve(GoogleTokenRefresher::class));

    expect($ga->refresh()->status)->toBe('error')
        ->and($gsc->refresh()->status)->toBe('error')
        ->and(MetricSnapshot::query()->count())->toBe(0);
});

it('queues the daily sync only for complete integrations', function (): void {
    Queue::fake();

    Integration::factory()->for($this->tenant)->create(['site_id' => $this->site->id, 'provider' => IntegrationProvider::GoogleAnalytics]);
    Integration::factory()->for($this->tenant)->create(['site_id' => $this->site->id, 'provider' => IntegrationProvider::SearchConsole]);
    Integration::factory()->for($this->tenant)->create(['site_id' => $this->site->id, 'provider' => IntegrationProvider::Meta]);
    Integration::factory()->for($this->tenant)->create(['site_id' => null]);

    $this->artisan('integrations:sync')
        ->expectsOutputToContain('Queued 2 metric sync job(s).')
        ->assertSuccessful();

    Queue::assertPushed(SyncGoogleAnalytics::class, 1);
    Queue::assertPushed(SyncSearchConsole::class, 1);
});

it('claims and publishes due social posts exactly once', function (): void {
    Queue::fake();

    $due = SocialPost::factory()->for($this->tenant)->create([
        'site_id' => $this->site->id,
        'status' => 'scheduled',
        'scheduled_at' => now()->subMinute(),
    ]);

    SocialPost::factory()->for($this->tenant)->create([
        'site_id' => $this->site->id,
        'status' => 'scheduled',
        'scheduled_at' => now()->addHour(),
    ]);

    $this->artisan('social:publish-scheduled')
        ->expectsOutputToContain('Queued 1 social post(s).')
        ->assertSuccessful();

    expect($due->refresh()->status)->toBe('publishing');

    Queue::assertPushed(PublishSocialPost::class, 1);

    // Segunda passagem não volta a apanhar o mesmo post.
    $this->artisan('social:publish-scheduled')->expectsOutputToContain('Queued 0 social post(s).');
});

it('publishes to meta and records the external id', function (): void {
    Http::fake(['graph.facebook.com/*' => Http::response(['id' => 'fb_123'])]);

    Integration::factory()->for($this->tenant)->create([
        'site_id' => $this->site->id,
        'provider' => IntegrationProvider::Meta,
        'external_id' => 'page_1',
    ]);

    $post = SocialPost::factory()->for($this->tenant)->create([
        'site_id' => $this->site->id,
        'status' => 'publishing',
        'content' => 'Olá mundo',
    ]);

    new PublishSocialPost($post->id, $this->tenant->id)->handle();

    expect($post->refresh()->status)->toBe('published')
        ->and($post->external_id)->toBe('fb_123')
        ->and($post->published_at)->not->toBeNull();
});

it('fails the social post gracefully without meta or on api errors', function (): void {
    $orphan = SocialPost::factory()->for($this->tenant)->create([
        'site_id' => $this->site->id,
        'status' => 'publishing',
    ]);

    new PublishSocialPost($orphan->id, $this->tenant->id)->handle();

    expect($orphan->refresh()->status)->toBe('failed')
        ->and($orphan->error)->toContain('Meta não está ligado');

    Http::fake(['graph.facebook.com/*' => Http::response([], 500)]);

    Integration::factory()->for($this->tenant)->create([
        'site_id' => $this->site->id,
        'provider' => IntegrationProvider::Meta,
        'external_id' => 'page_1',
    ]);

    $failing = SocialPost::factory()->for($this->tenant)->create([
        'site_id' => $this->site->id,
        'status' => 'publishing',
    ]);

    new PublishSocialPost($failing->id, $this->tenant->id)->handle();

    expect($failing->refresh()->status)->toBe('failed');

    // Posts que já não estão "publishing" são ignorados (idempotência).
    $published = SocialPost::factory()->for($this->tenant)->create([
        'site_id' => $this->site->id,
        'status' => 'published',
    ]);

    new PublishSocialPost($published->id, $this->tenant->id)->handle();

    expect($published->refresh()->status)->toBe('published');
});

it('publishes ga4 and search console ids in the site schema', function (): void {
    Integration::factory()->for($this->tenant)->create([
        'site_id' => $this->site->id,
        'provider' => IntegrationProvider::GoogleAnalytics,
        'external_id' => 'G-XYZ',
    ]);
    Integration::factory()->for($this->tenant)->create([
        'site_id' => $this->site->id,
        'provider' => IntegrationProvider::SearchConsole,
        'external_id' => 'verify-token',
    ]);
    Integration::factory()->for($this->tenant)->create([
        'site_id' => $this->site->id,
        'provider' => IntegrationProvider::Meta,
        'external_id' => 'page_1',
    ]);
    // Ligação de outro site não contamina este schema; sem external_id
    // também não entra.
    Integration::factory()->for($this->tenant)->create([
        'site_id' => Site::factory()->for($this->tenant)->create()->id,
        'provider' => IntegrationProvider::GoogleAnalytics,
        'external_id' => 'G-OUTRO',
    ]);

    $schema = resolve(SiteService::class)->buildSchema($this->site);

    expect($schema['site']['integrations'])->toBe([
        'ga4_measurement_id' => 'G-XYZ',
        'gsc_verification' => 'verify-token',
    ]);
});

it('adapts copy per network with the brand voice', function (): void {
    $profile = BrandProfile::factory()->for($this->tenant)->create([
        'tone_of_voice' => 'direto e caloroso',
        'glossary' => ['CMS' => 'plataforma'],
    ]);

    $instagram = new SocialCopyAgent($profile, 'instagram');
    $linkedin = new SocialCopyAgent($profile, 'linkedin');
    $fallback = new SocialCopyAgent(null, 'rede-desconhecida');

    expect($instagram->instructions())->toContain('hashtags')
        ->toContain('direto e caloroso')
        ->toContain('plataforma')
        ->and($linkedin->instructions())->toContain('professional')
        ->and($fallback->instructions())->toContain('conversational');
});
