<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Enums\BlockType;
use App\Mcp\Concerns\EnforcesAbilities;
use App\Models\Site;
use App\Models\User;
use App\Services\AuditLogger;
use App\Support\CurrentTenant;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\DB;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;

#[Description('Replace the blocks of a page (content stays in its current status — never publishes). Requires the write:draft token ability.')]
final class UpdateBlocksTool extends Tool
{
    use EnforcesAbilities;

    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        if (($denied = $this->deniedFor($request, 'write:draft', 'pages.manage')) instanceof Response) {
            return $denied;
        }

        /** @var array{site_slug: string, page_slug: string, blocks: list<array{type: string, content?: array<string, mixed>}>} $data */
        $data = $request->validate([
            'site_slug' => ['required', 'string'],
            'page_slug' => ['required', 'string'],
            'blocks' => ['required', 'array'],
            'blocks.*.type' => ['required', 'in:'.implode(',', BlockType::values())],
            'blocks.*.content' => ['nullable', 'array'],
        ]);

        $site = Site::query()->where('slug', $data['site_slug'])->first();

        if ($site === null) {
            return Response::error(sprintf('Site [%s] not found.', $data['site_slug']));
        }

        $page = $site->pages()->where('slug', $data['page_slug'])->first();

        if ($page === null) {
            return Response::error(sprintf('Page [%s] not found in site [%s].', $data['page_slug'], $data['site_slug']));
        }

        DB::transaction(function () use ($page, $data): void {
            $page->blocks()->delete();

            foreach ($data['blocks'] as $index => $block) {
                $page->blocks()->create([
                    'type' => $block['type'],
                    'content' => $block['content'] ?? null,
                    'sort_order' => $index,
                ]);
            }
        });

        $user = $request->user();

        AuditLogger::record(
            resolve(CurrentTenant::class)->getOrFail(),
            'page.blocks_updated',
            $user instanceof User ? $user : null,
            'agent',
            'page',
            $page->id,
            ['blocks' => count($data['blocks'])],
        );

        return Response::text((string) json_encode([
            'id' => $page->id,
            'blocks' => $page->blocks()->count(),
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
            'blocks' => $schema->array()->description('List of blocks ({type, content})')->required(),
        ];
    }
}
