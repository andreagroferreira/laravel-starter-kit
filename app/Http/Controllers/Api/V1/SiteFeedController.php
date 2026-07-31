<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Models\Post;
use App\Models\Site;
use Illuminate\Http\Response;

final class SiteFeedController
{
    public function __invoke(Site $site): Response
    {
        $posts = $site->posts()->live()->latest('published_at')->limit(50)->get();

        $items = $posts->map(fn (Post $post): string => sprintf(
            '<item><title>%s</title><link>%s</link><guid isPermaLink="false">%s</guid><pubDate>%s</pubDate><description>%s</description></item>',
            htmlspecialchars($post->title),
            htmlspecialchars('/'.$post->slug),
            $post->id,
            $post->published_at?->toRssString() ?? '',
            htmlspecialchars($post->excerpt ?? '')
        ))->implode('');

        $rss = <<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <rss version="2.0"><channel>
        <title>{$this->escape($site->name)}</title>
        <link>/</link>
        <description>Latest posts from {$this->escape($site->name)}</description>
        {$items}
        </channel></rss>
        XML;

        return response($rss, 200, ['Content-Type' => 'application/rss+xml; charset=UTF-8']);
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value);
    }
}
