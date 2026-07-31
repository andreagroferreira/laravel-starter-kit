<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ContentStatus;
use App\Models\Page;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;

final class PublishPageController
{
    public function __invoke(Site $site, Page $page): RedirectResponse
    {
        abort_unless($page->site_id === $site->id, 404);

        $page->update([
            'status' => $page->status === ContentStatus::Published ? ContentStatus::Draft : ContentStatus::Published,
            'published_at' => $page->status === ContentStatus::Published ? $page->published_at : now(),
        ]);

        return back();
    }
}
