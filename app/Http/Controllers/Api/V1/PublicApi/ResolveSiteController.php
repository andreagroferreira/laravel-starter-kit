<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\PublicApi;

use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Resolves the site for a public hostname and returns its published
 * schema. This is the renderer's entry point: exact domain match first,
 * then {slug}.{suffix} on the configured public/local suffixes.
 */
final class ResolveSiteController
{
    public function __invoke(Request $request): JsonResponse
    {
        $host = Str::lower((string) $request->query('host', ''));

        abort_if($host === '', 422, 'The host parameter is required.');

        $site = $this->resolve($host);

        abort_if(! $site instanceof Site, 404, 'No site is bound to this host.');

        $version = $site->publishedVersion;

        abort_if($version === null, 404, 'This site has no published version.');

        return response()->json([
            'site' => [
                'id' => $site->id,
                'name' => $site->name,
                'slug' => $site->slug,
                'type' => $site->type->value,
            ],
            'version' => [
                'id' => $version->id,
                'published_at' => $version->published_at?->toIso8601String(),
            ],
            'schema' => $version->schema,
        ], 200, ['Cache-Control' => 'public, max-age=60']);
    }

    private function resolve(string $host): ?Site
    {
        $site = Site::query()->where('domain', $host)->first();

        if ($site !== null) {
            return $site;
        }

        $suffixes = [
            config()->string('renderer.public_suffix'),
            ...config()->array('renderer.local_suffixes'),
        ];

        foreach ($suffixes as $suffix) {
            if (is_string($suffix) && $suffix !== '' && str_ends_with($host, $suffix)) {
                $slug = Str::before($host, $suffix);

                if ($slug !== '' && ! str_contains($slug, '.')) {
                    return Site::query()->where('slug', $slug)->first();
                }
            }
        }

        return null;
    }
}
