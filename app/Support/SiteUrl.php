<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Site;

final class SiteUrl
{
    /**
     * Public base URL of a site: its custom domain when set, otherwise
     * {slug}{suffix} on the platform domain.
     */
    public static function for(Site $site): string
    {
        return config()->string('renderer.scheme', 'https').'://'.self::host($site);
    }

    /**
     * Public hostname of a site.
     */
    public static function host(Site $site): string
    {
        if (is_string($site->domain) && $site->domain !== '') {
            return $site->domain;
        }

        return $site->slug.config()->string('renderer.public_suffix');
    }

    /**
     * URLs worth purging after a publish: the home page plus every
     * published page of the site.
     *
     * @return list<string>
     */
    public static function purgeable(Site $site): array
    {
        $base = self::for($site);

        $urls = [$base.'/', $base.'/sitemap.xml', $base.'/feed.rss'];

        /** @var list<string> $slugs */
        $slugs = $site->pages()->published()->pluck('slug')->all();

        foreach ($slugs as $slug) {
            $urls[] = $base.'/'.mb_ltrim($slug, '/');
        }

        return array_values(array_unique($urls));
    }
}
