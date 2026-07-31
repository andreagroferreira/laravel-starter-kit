<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Page;
use Illuminate\Support\Facades\DB;

final class PageBlockSynchronizer
{
    /**
     * Replace the page's blocks atomically, preserving order.
     * Shared by the backoffice editor and the MCP UpdateBlocks tool.
     *
     * @param  list<array{type: string, content?: array<string, mixed>|null}>  $blocks
     */
    public function sync(Page $page, array $blocks): void
    {
        DB::transaction(function () use ($page, $blocks): void {
            $page->blocks()->delete();

            foreach ($blocks as $index => $block) {
                $page->blocks()->create([
                    'type' => $block['type'],
                    'content' => $block['content'] ?? null,
                    'sort_order' => $index,
                ]);
            }
        });
    }
}
