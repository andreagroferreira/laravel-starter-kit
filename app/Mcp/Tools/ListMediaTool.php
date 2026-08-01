<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\Concerns\EnforcesAbilities;
use App\Models\MediaAsset;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('List media assets of the tenant so blocks can reference real image URLs. Requires the read token ability.')]
final class ListMediaTool extends Tool
{
    use EnforcesAbilities;

    public function handle(Request $request): Response
    {
        if (($denied = $this->deniedFor($request, 'read', 'media.manage')) instanceof Response) {
            return $denied;
        }

        /** @var array{search?: string} $data */
        $data = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $search = $data['search'] ?? '';

        $assets = MediaAsset::query()
            ->when($search !== '', fn (Builder $query) => $query->whereLike('filename', '%'.$search.'%'))
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn (MediaAsset $asset): array => [
                'id' => $asset->id,
                'filename' => $asset->filename,
                'url' => $asset->url(),
                'alt' => $asset->alt,
                'mime_type' => $asset->mime_type,
            ])->all();

        return Response::text((string) json_encode(['media' => $assets], JSON_PRETTY_PRINT));
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'search' => $schema->string()->description('Filter by filename.'),
        ];
    }
}
