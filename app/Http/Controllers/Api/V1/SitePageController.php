<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\PageResource;
use App\Models\Site;

final class SitePageController
{
    public function __invoke(Site $site, string $slug): PageResource
    {

        $page = $site->pages()->published()->where('slug', $slug)->firstOrFail();

        return new PageResource($page->load('blocks'));
    }
}
