<?php

declare(strict_types=1);

use App\Contracts\EdgeDeployer;
use App\Enums\DeploymentStatus;
use App\Events\DeploymentUpdated;
use App\Jobs\DeploySite;
use App\Models\Deployment;
use App\Models\Form;
use App\Models\Page;
use App\Models\Site;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Deploy\CloudflareDeployer;
use App\Services\Deploy\NullDeployer;
use App\Services\SiteService;
use App\Support\SiteUrl;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;

beforeEach(function (): void {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create(['current_tenant_id' => $this->tenant->id]);
    $this->tenant->users()->attach($this->user);
    grantRole($this->tenant, $this->user);
    $this->site = Site::factory()->for($this->tenant)->create(['slug' => 'demo', 'domain' => null]);
});

it('queues a deployment when a site is published', function (): void {
    Queue::fake();

    resolve(SiteService::class)->publish($this->site, $this->user);

    $deployment = Deployment::query()->sole();

    expect($deployment->status)->toBe(DeploymentStatus::Queued)
        ->and($deployment->type)->toBe('content')
        ->and($deployment->triggered_by)->toBe($this->user->id);

    Queue::assertPushed(DeploySite::class);
});

it('runs the deployment and broadcasts the outcome', function (): void {
    Event::fake([DeploymentUpdated::class]);

    $deployment = Deployment::factory()->for($this->tenant)->create(['site_id' => $this->site->id]);

    new DeploySite($deployment->id, $this->tenant->id)->handle(new NullDeployer);

    $deployment->refresh();

    expect($deployment->status)->toBe(DeploymentStatus::Deployed)
        ->and($deployment->url)->toBe('https://demo.wizardincode.site')
        ->and($deployment->finished_at)->not->toBeNull();

    Event::assertDispatchedTimes(DeploymentUpdated::class, 2);
});

it('records the failure instead of crashing the queue', function (): void {
    $deployment = Deployment::factory()->for($this->tenant)->create(['site_id' => $this->site->id]);

    $broken = new class implements EdgeDeployer
    {
        /** @param  list<string>  $urls */
        public function purge(Site $site, array $urls): void {}

        public function provisionDomain(Site $site): string
        {
            throw new RuntimeException('Cloudflare está em baixo');
        }
    };

    new DeploySite($deployment->id, $this->tenant->id)->handle($broken);

    expect($deployment->refresh()->status)->toBe(DeploymentStatus::Failed)
        ->and($deployment->error)->toContain('Cloudflare está em baixo');
});

it('derives public urls and the purge list from the site', function (): void {
    Page::factory()->for($this->site)->create(['slug' => 'sobre', 'status' => 'published']);
    Page::factory()->for($this->site)->create(['slug' => 'rascunho', 'status' => 'draft']);

    expect(SiteUrl::for($this->site))->toBe('https://demo.wizardincode.site')
        ->and(SiteUrl::purgeable($this->site))->toContain('https://demo.wizardincode.site/sobre')
        ->and(SiteUrl::purgeable($this->site))->not->toContain('https://demo.wizardincode.site/rascunho');

    $custom = Site::factory()->for($this->tenant)->create(['domain' => 'www.exemplo.pt']);

    expect(SiteUrl::for($custom))->toBe('https://www.exemplo.pt');
});

it('calls cloudflare to provision the domain and purge in batches', function (): void {
    config()->set('services.cloudflare', [
        'token' => 'cf-token',
        'account_id' => 'acc',
        'zone_id' => 'zone',
        'pages_project' => 'wizcms-renderer',
    ]);

    Http::fake(['api.cloudflare.com/*' => Http::response(['success' => true])]);

    $deployer = new CloudflareDeployer;

    expect($deployer->provisionDomain($this->site))->toBe('https://demo.wizardincode.site');

    $deployer->purge($this->site, array_map(fn (int $i): string => 'https://demo.wizardincode.site/p'.$i, range(1, 45)));

    Http::assertSentCount(4); // 1 DNS + 1 domain + 2 purge batches (30 + 15)
});

it('skips cloudflare calls when the integration is not configured', function (): void {
    config()->set('services.cloudflare', ['token' => '', 'account_id' => '', 'zone_id' => '', 'pages_project' => '']);

    Http::fake();

    $deployer = new CloudflareDeployer;
    $deployer->purge($this->site, ['https://demo.wizardincode.site/']);

    expect($deployer->provisionDomain($this->site))->toBe('https://demo.wizardincode.site');

    Http::assertNothingSent();
});

it('publishes the form definitions in the schema for the renderer', function (): void {
    Form::factory()->for($this->site)->create([
        'name' => 'contacto',
        'fields' => [['name' => 'email', 'type' => 'email', 'required' => true]],
    ]);

    $schema = resolve(SiteService::class)->buildSchema($this->site);

    expect($schema['forms'])->toHaveCount(1)
        ->and($schema['forms'][0]['name'])->toBe('contacto')
        ->and($schema['forms'][0]['fields'][0]['name'])->toBe('email');
});
