<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\Concerns\EnforcesAbilities;
use App\Models\Site;
use App\Models\User;
use App\Services\SiteService;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Publish a site, creating an immutable schema version. Requires the publish token ability — the deliberate exception to draft-first.')]
final class PublishSiteTool extends Tool
{
    use EnforcesAbilities;

    public function handle(Request $request, SiteService $sites): Response
    {
        if (($denied = $this->deniedFor($request, 'publish', 'sites.publish')) instanceof Response) {
            return $denied;
        }

        /** @var array{site_slug: string} $data */
        $data = $request->validate([
            'site_slug' => ['required', 'string'],
        ]);

        $site = Site::query()->where('slug', $data['site_slug'])->first();

        if ($site === null) {
            return Response::error(sprintf('Site [%s] not found.', $data['site_slug']));
        }

        $user = $request->user();

        $version = $sites->publish($site, $user instanceof User ? $user : null, 'agent');

        return Response::text((string) json_encode([
            'site' => $site->slug,
            'version_id' => $version->id,
            'published_at' => $version->published_at?->toIso8601String(),
        ], JSON_PRETTY_PRINT));
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'site_slug' => $schema->string()->description('Slug of the site to publish.')->required(),
        ];
    }
}
