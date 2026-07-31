<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Models\Page;
use App\Models\PageBlock;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Page
 */
final class PageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'seo' => $this->seo,
            'blocks' => $this->blocks->map(fn (PageBlock $block): array => [
                'type' => $block->type,
                'content' => $block->content,
            ])->all(),
            'published_at' => $this->published_at?->toIso8601String(),
        ];
    }
}
