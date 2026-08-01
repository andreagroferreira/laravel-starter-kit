<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Enums\ContentStatus;
use App\Mcp\Concerns\EnforcesAbilities;
use App\Models\Post;
use App\Models\Site;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\CurrentTenant;
use App\Support\UniqueSlug;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Create a draft post (never published). Requires the write:draft token ability.')]
final class CreatePostDraftTool extends Tool
{
    use EnforcesAbilities;

    public function handle(Request $request): Response
    {
        if (($denied = $this->deniedFor($request, 'write:draft', 'posts.create')) instanceof Response) {
            return $denied;
        }

        /** @var array{site_slug: string, title: string, body: string, excerpt?: string, seo_title?: string, seo_description?: string} $data */
        $data = $request->validate([
            'site_slug' => ['required', 'string'],
            'title' => ['required', 'string', 'max:200'],
            'body' => ['required', 'string'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'seo_title' => ['nullable', 'string', 'max:70'],
            'seo_description' => ['nullable', 'string', 'max:160'],
        ]);

        $site = Site::query()->where('slug', $data['site_slug'])->first();

        if ($site === null) {
            return Response::error(sprintf('Site [%s] not found.', $data['site_slug']));
        }

        $user = $request->user();

        /** @var Post $post */
        $post = $site->posts()->create([
            'author_id' => $user instanceof User ? $user->getKey() : null,
            'title' => $data['title'],
            'slug' => UniqueSlug::make($site->posts()->getQuery(), $data['title']),
            'status' => ContentStatus::Draft,
            'body' => $data['body'],
            'excerpt' => $data['excerpt'] ?? null,
            'seo' => array_filter([
                'title' => $data['seo_title'] ?? null,
                'description' => $data['seo_description'] ?? null,
            ]) ?: null,
        ]);

        AuditLogger::record(
            resolve(CurrentTenant::class)->getOrFail(),
            'post.created',
            $user instanceof User ? $user : null,
            'agent',
            'post',
            $post->id,
            ['title' => $post->title, 'slug' => $post->slug],
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
            'title' => $schema->string()->description('Post title.')->required(),
            'body' => $schema->string()->description('Post body as HTML.')->required(),
            'excerpt' => $schema->string()->description('Short summary.'),
            'seo_title' => $schema->string()->description('Meta title (max 70 chars).'),
            'seo_description' => $schema->string()->description('Meta description (max 160 chars).'),
        ];
    }
}
