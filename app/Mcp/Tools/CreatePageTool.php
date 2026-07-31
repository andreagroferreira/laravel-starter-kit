<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Enums\BlockType;
use App\Enums\ContentStatus;
use App\Models\Site;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\CurrentTenant;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Create a new draft page in a site (draft-first: never publishes). Requires the write:draft token ability.')]
final class CreatePageTool extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        /** @var array{site_slug: string, title: string, slug: string, blocks?: list<array{type: string, content?: array<string, mixed>}>} $data */
        $data = $request->validate([
            'site_slug' => ['required', 'string'],
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['required', 'string', 'max:200'],
            'blocks' => ['nullable', 'array'],
            'blocks.*.type' => ['required_with:blocks', 'in:'.implode(',', BlockType::values())],
            'blocks.*.content' => ['nullable', 'array'],
        ]);

        $site = Site::query()->where('slug', $data['site_slug'])->first();

        if ($site === null) {
            return Response::error(sprintf('Site [%s] not found.', $data['site_slug']));
        }

        if ($site->pages()->where('slug', $data['slug'])->exists()) {
            return Response::error(sprintf('Page [%s] already exists in this site.', $data['slug']));
        }

        $page = $site->pages()->create([
            'title' => $data['title'],
            'slug' => $data['slug'],
            'status' => ContentStatus::Draft,
        ]);

        foreach ($data['blocks'] ?? [] as $index => $block) {
            $page->blocks()->create([
                'type' => $block['type'],
                'content' => $block['content'] ?? null,
                'sort_order' => $index,
            ]);
        }

        $user = $request->user();

        AuditLogger::record(
            resolve(CurrentTenant::class)->getOrFail(),
            'page.created',
            $user instanceof User ? $user : null,
            'agent',
            'page',
            $page->id,
            ['title' => $page->title, 'slug' => $page->slug],
        );

        return Response::text((string) json_encode([
            'id' => $page->id,
            'slug' => $page->slug,
            'status' => $page->status->value,
        ], JSON_PRETTY_PRINT));
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'site_slug' => $schema->string()->description('Slug of the site')->required(),
            'title' => $schema->string()->description('Page title')->required(),
            'slug' => $schema->string()->description('Page slug')->required(),
            'blocks' => $schema->array()->description('Optional list of blocks ({type, content})'),
        ];
    }
}
