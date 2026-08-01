<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Site;

/**
 * Whatever makes a published site reachable on the edge. The Cloudflare
 * implementation purges the CDN and provisions domains; the null one lets
 * local and CI runs publish without any network call.
 */
interface EdgeDeployer
{
    /**
     * Invalidate cached URLs of the site (content publish).
     *
     * @param  list<string>  $urls
     */
    public function purge(Site $site, array $urls): void;

    /**
     * Ensure the site's hostname resolves and is attached to the renderer.
     * Idempotent: called on every publish, does work only the first time.
     */
    public function provisionDomain(Site $site): string;
}
