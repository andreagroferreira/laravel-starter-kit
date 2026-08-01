<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Enums\ContentStatus;
use App\Mcp\Concerns\EnforcesAbilities;
use App\Models\Site;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\CurrentTenant;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Update an existing draft post. Published posts are never modified by agents. Requires the write:draft token ability.')]
final class UpdatePostDraftTool extends Tool
{
    use EnforcesAbilities;

    public function handle(Request $request): Response
    {
        if (($denied = $this->deniedFor($request, 'write:draft', 'posts.create')) instanceof Response) {
            return $denied;
        }

        /** @var array{site_slug: string, post_slug: string, title?: string, body?: string, excerpt?: string} $data */
        $data = $request->validate([
            'site_slug' => ['required', 'string'],
            'post_slug' => ['required', 'string'],
            'title' => ['nullable', 'string', 'max:200'],
            'body' => ['nullable', 'string'],
            'excerpt' => ['nullable', 'string', 'max:500'],
        ]);

        $site = Site::query()->where('slug', $data['site_slug'])->first();

        if ($site === null) {
            return Response::error(sprintf('Site [%s] not found.', $data['site_slug']));
        }

        $post = $site->posts()->where('slug', $data['post_slug'])->first();

        if ($post === null) {
            return Response::error(sprintf('Post [%s] not found in site [%s].', $data['post_slug'], $data['site_slug']));
        }

        if ($post->status !== ContentStatus::Draft) {
            return Response::error('Only draft posts can be updated by agents. Ask a human to unpublish it first.');
        }

        $post->update(array_filter([
            'title' => $data['title'] ?? null,
            'body' => $data['body'] ?? null,
            'excerpt' => $data['excerpt'] ?? null,
        ], fn (mixed $value): bool => $value !== null));

        $user = $request->user();

        AuditLogger::record(
            resolve(CurrentTenant::class)->getOrFail(),
            'post.updated',
            $user instanceof User ? $user : null,
            'agent',
            'post',
            $post->id,
            ['slug' => $post->slug],
        );

        return Response::text((string) json_encode([
            'id' => $post->id,
            'slug' => $post->slug,
            'status' => $post->status->value,
        ], JSON_PRETTY_PRINT));
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'site_slug' => $schema->string()->description('Slug of the site.')->required(),
            'post_slug' => $schema->string()->description('Slug of the draft post.')->required(),
            'title' => $schema->string()->description('New title.'),
            'body' => $schema->string()->description('New body as HTML.'),
            'excerpt' => $schema->string()->description('New excerpt.'),
        ];
    }
}
