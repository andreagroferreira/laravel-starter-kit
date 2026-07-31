<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ContentStatus;
use App\Models\Post;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;

final class PublishPostController
{
    public function __invoke(Site $site, Post $post): RedirectResponse
    {
        abort_unless($post->site_id === $site->id, 404);

        if ($post->status === ContentStatus::Published) {
            $post->update(['status' => ContentStatus::Draft]);
        } else {
            $post->update([
                'status' => $post->published_at !== null && $post->published_at->isFuture()
                    ? ContentStatus::Scheduled
                    : ContentStatus::Published,
                'published_at' => $post->published_at ?? now(),
            ]);
        }

        return back();
    }
}
