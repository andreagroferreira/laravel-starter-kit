<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\MediaAsset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * JSON feed for the editor's media picker modal.
 */
final class MediaPickerController
{
    public function __invoke(Request $request): JsonResponse
    {
        Gate::authorize('create', MediaAsset::class);

        $search = (string) $request->query('search', '');

        return response()->json(
            MediaAsset::query()
                ->when($search !== '', fn (Builder $query) => $query->whereLike('filename', '%'.$search.'%'))
                ->latest()
                ->paginate(24)
                ->withQueryString()
                ->through(fn (MediaAsset $asset): array => [
                    'id' => $asset->id,
                    'url' => $asset->url(),
                    'filename' => $asset->filename,
                    'alt' => $asset->alt,
                    'mime_type' => $asset->mime_type,
                ])
        );
    }
}
