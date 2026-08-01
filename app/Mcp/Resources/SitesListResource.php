<?php

declare(strict_types=1);

namespace App\Mcp\Resources;

use App\Models\Site;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Resource;

#[Uri('site://list')]
#[MimeType('application/json')]
#[Description('All sites of the current tenant with slug, type and status.')]
final class SitesListResource extends Resource
{
    public function handle(Request $request): Response
    {
        $sites = Site::query()->latest()->get()
            ->map(fn (Site $site): array => [
                'id' => $site->id,
                'name' => $site->name,
                'slug' => $site->slug,
                'type' => $site->type->value,
                'status' => $site->status,
                'domain' => $site->domain,
            ])->all();

        return Response::text((string) json_encode(['sites' => $sites], JSON_PRETTY_PRINT));
    }
}
