<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Models\Site;
use Illuminate\Http\JsonResponse;

final class SiteSchemaController
{
    public function __invoke(Site $site): JsonResponse
    {

        $version = $site->publishedVersion;

        abort_if($version === null, 404, 'Site has no published version.');

        return response()->json([
            'data' => [
                'site' => $site->slug,
                'version' => $version->id,
                'renderer_version' => $version->renderer_version,
                'published_at' => $version->published_at?->toIso8601String(),
                'schema' => $version->schema,
            ],
        ]);
    }
}
