<?php

declare(strict_types=1);

namespace App\Services\Deploy;

use App\Contracts\EdgeDeployer;
use App\Models\Site;
use App\Support\SiteUrl;

/**
 * Default deployer: no network calls. Local development and CI publish
 * exactly like production, minus the edge.
 */
final class NullDeployer implements EdgeDeployer
{
    /**
     * @param  list<string>  $urls
     */
    public function purge(Site $site, array $urls): void
    {
        //
    }

    public function provisionDomain(Site $site): string
    {
        return SiteUrl::for($site);
    }
}
