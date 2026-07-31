<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Mcp\Concerns\EnforcesAbilities;
use App\Models\PageBlock;
use App\Models\Site;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Get a page of a site with its blocks.')]
final class GetPageTool extends Tool
{
    use EnforcesAbilities;

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        if (($denied = $this->deniedFor($request, 'read', 'sites.view')) instanceof Response) {
            return $denied;
        }

        /** @var array{site_slug: string, page_slug: string} $data */
        $data = $request->validate([
            'site_slug' => ['required', 'string'],
            'page_slug' => ['required', 'string'],
        ]);

        $site = Site::query()->where('slug', $data['site_slug'])->first();

        if ($site === null) {
            return Response::error(sprintf('Site [%s] not found.', $data['site_slug']));
        }

        $page = $site->pages()->where('slug', $data['page_slug'])->with('blocks')->first();

        if ($page === null) {
            return Response::error(sprintf('Page [%s] not found in site [%s].', $data['page_slug'], $data['site_slug']));
        }

        return Response::text((string) json_encode([
            'id' => $page->id,
            'title' => $page->title,
            'slug' => $page->slug,
            'status' => $page->status->value,
            'seo' => $page->seo,
            'blocks' => $page->blocks->map(fn (PageBlock $block): array => [
                'type' => $block->type,
                'content' => $block->content,
            ])->all(),
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
            'page_slug' => $schema->string()->description('Slug of the page')->required(),
        ];
    }
}
