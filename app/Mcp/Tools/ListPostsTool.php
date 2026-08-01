<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\Concerns\EnforcesAbilities;
use App\Models\Post;
use App\Models\Site;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('List the posts of a site, optionally filtered by status. Requires the read token ability.')]
final class ListPostsTool extends Tool
{
    use EnforcesAbilities;

    public function handle(Request $request): Response
    {
        if (($denied = $this->deniedFor($request, 'read', 'sites.view')) instanceof Response) {
            return $denied;
        }

        /** @var array{site_slug: string, status?: string} $data */
        $data = $request->validate([
            'site_slug' => ['required', 'string'],
            'status' => ['nullable', 'in:draft,review,approved,scheduled,published'],
        ]);

        $site = Site::query()->where('slug', $data['site_slug'])->first();

        if ($site === null) {
            return Response::error(sprintf('Site [%s] not found.', $data['site_slug']));
        }

        $status = $data['status'] ?? '';

        $posts = $site->posts()
            ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn (Post $post): array => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'status' => $post->status->value,
                'published_at' => $post->published_at?->toIso8601String(),
            ])->all();

        return Response::text((string) json_encode(['posts' => $posts], JSON_PRETTY_PRINT));
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'site_slug' => $schema->string()->description('Slug of the site.')->required(),
            'status' => $schema->string()->description('Filter by content status.'),
        ];
    }
}
