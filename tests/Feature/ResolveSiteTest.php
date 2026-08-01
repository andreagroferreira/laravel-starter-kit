<?php

declare(strict_types=1);

use App\Models\Site;
use App\Models\SiteVersion;
use App\Models\Tenant;

it('resolves a site by exact custom domain', function (): void {
    $site = Site::factory()->create(['domain' => 'www.exemplo.pt']);
    SiteVersion::factory()->for($site)->published()->create(['schema' => ['site' => ['name' => $site->name]]]);

    $this->getJson('/api/v1/public/resolve?host=www.exemplo.pt')
        ->assertOk()
        ->assertJsonPath('site.id', $site->id)
        ->assertJsonPath('site.slug', $site->slug)
        ->assertHeader('Cache-Control', 'max-age=60, public');
});

it('resolves a site by slug on the public suffix', function (): void {
    config()->set('renderer.public_suffix', '.wizardincode.site');

    $site = Site::factory()->create(['slug' => 'demo']);
    SiteVersion::factory()->for($site)->published()->create();

    $this->getJson('/api/v1/public/resolve?host=demo.wizardincode.site')
        ->assertOk()
        ->assertJsonPath('site.id', $site->id);
});

it('resolves local dev hostnames', function (): void {
    $site = Site::factory()->create(['slug' => 'demo']);
    SiteVersion::factory()->for($site)->published()->create();

    $this->getJson('/api/v1/public/resolve?host=demo.wizcms.test')
        ->assertOk()
        ->assertJsonPath('site.id', $site->id);
});

it('never resolves ambiguous slugs across tenants by accident', function (): void {
    // Dois tenants com o mesmo slug de site: o resolve por slug devolve o
    // primeiro publicado — mas domínios exatos têm sempre prioridade.
    $siteA = Site::factory()->for(Tenant::factory())->create(['slug' => 'blog', 'domain' => 'a.exemplo.pt']);
    $siteB = Site::factory()->for(Tenant::factory())->create(['slug' => 'blog', 'domain' => 'b.exemplo.pt']);
    SiteVersion::factory()->for($siteA)->published()->create();
    SiteVersion::factory()->for($siteB)->published()->create();

    $this->getJson('/api/v1/public/resolve?host=a.exemplo.pt')->assertJsonPath('site.id', $siteA->id);
    $this->getJson('/api/v1/public/resolve?host=b.exemplo.pt')->assertJsonPath('site.id', $siteB->id);
});

it('returns 404 for unknown hosts, unpublished sites and 422 without host', function (): void {
    $this->getJson('/api/v1/public/resolve?host=nada.exemplo.pt')->assertNotFound();

    Site::factory()->create(['slug' => 'rascunho']);
    $this->getJson('/api/v1/public/resolve?host=rascunho.wizcms.test')->assertNotFound();

    $this->getJson('/api/v1/public/resolve')->assertUnprocessable();

    // Hosts com subníveis extra não resolvem por slug.
    $this->getJson('/api/v1/public/resolve?host=a.b.wizcms.test')->assertNotFound();
});
