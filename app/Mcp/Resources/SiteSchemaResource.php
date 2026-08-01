<?php

declare(strict_types=1);

namespace App\Mcp\Resources;

use App\Models\Site;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Contracts\HasUriTemplate;
use Laravel\Mcp\Server\Resource;
use Laravel\Mcp\Support\UriTemplate;

#[Uri('site://{slug}/schema')]
#[MimeType('application/json')]
#[Description('Published schema of a site: pages, blocks, menus and design tokens.')]
final class SiteSchemaResource extends Resource implements HasUriTemplate
{
    public function uriTemplate(): UriTemplate
    {
        return new UriTemplate('site://{slug}/schema');
    }

    public function handle(Request $request): Response
    {
        $slug = $request->string('slug')->value();

        $site = Site::query()->where('slug', $slug)->first();

        if ($site === null) {
            return Response::error(sprintf('Site [%s] not found.', $slug));
        }

        $version = $site->publishedVersion;

        if ($version === null) {
            return Response::error(sprintf('Site [%s] has no published version yet.', $slug));
        }

        return Response::text((string) json_encode([
            'site' => $site->slug,
            'published_at' => $version->published_at?->toIso8601String(),
            'schema' => $version->schema,
        ], JSON_PRETTY_PRINT));
    }
}
