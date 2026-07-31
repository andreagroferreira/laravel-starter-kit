<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Models\Page;
use App\Models\Post;
use App\Models\Site;
use Illuminate\Http\Response;

final class SiteSitemapController
{
    public function __invoke(Site $site): Response
    {
        /** @var list<array{loc: string, lastmod: string}> $urls */
        $urls = [];

        $site->pages()->published()->get(['slug', 'updated_at'])
            ->each(function (Page $page) use (&$urls): void {
                $urls[] = [
                    'loc' => '/'.mb_ltrim($page->slug, '/'),
                    'lastmod' => $page->updated_at->toDateString(),
                ];
            });

        $site->posts()->live()->get(['slug', 'updated_at'])
            ->each(function (Post $post) use (&$urls): void {
                $urls[] = [
                    'loc' => '/posts/'.$post->slug,
                    'lastmod' => $post->updated_at->toDateString(),
                ];
            });

        $body = collect($urls)->map(fn (array $url): string => sprintf(
            '<url><loc>%s</loc><lastmod>%s</lastmod></url>',
            htmlspecialchars($url['loc']),
            $url['lastmod']
        ))->implode('');

        $xml = <<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">{$body}</urlset>
        XML;

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
