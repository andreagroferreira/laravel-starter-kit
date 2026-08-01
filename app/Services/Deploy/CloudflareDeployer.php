<?php

declare(strict_types=1);

namespace App\Services\Deploy;

use App\Contracts\EdgeDeployer;
use App\Models\Site;
use App\Support\SiteUrl;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Cloudflare edge: purge by URL (available on every plan, unlike purge by
 * tag) and, on first publish, a proxied CNAME plus the custom domain on
 * the shared Pages project — Pages has no wildcard domains.
 */
final class CloudflareDeployer implements EdgeDeployer
{
    private const int PURGE_BATCH = 30;

    /**
     * @param  list<string>  $urls
     */
    public function purge(Site $site, array $urls): void
    {
        $zoneId = config()->string('services.cloudflare.zone_id');

        if ($zoneId === '' || $urls === []) {
            return;
        }

        foreach (array_chunk($urls, self::PURGE_BATCH) as $batch) {
            $this->request()
                ->post(sprintf('zones/%s/purge_cache', $zoneId), ['files' => $batch])
                ->throw();
        }
    }

    public function provisionDomain(Site $site): string
    {
        $host = SiteUrl::host($site);

        $zoneId = config()->string('services.cloudflare.zone_id');
        $account = config()->string('services.cloudflare.account_id');
        $project = config()->string('services.cloudflare.pages_project');

        if ($zoneId === '' || $account === '' || $project === '') {
            return SiteUrl::for($site);
        }

        $this->request()->post(sprintf('zones/%s/dns_records', $zoneId), [
            'type' => 'CNAME',
            'name' => $host,
            'content' => $project.'.pages.dev',
            'proxied' => true,
        ]);

        $this->request()->post(
            sprintf('accounts/%s/pages/projects/%s/domains', $account, $project),
            ['name' => $host],
        );

        return SiteUrl::for($site);
    }

    private function request(): PendingRequest
    {
        return Http::withToken(config()->string('services.cloudflare.token'))
            ->baseUrl('https://api.cloudflare.com/client/v4')
            ->acceptJson()
            ->timeout(20);
    }
}
