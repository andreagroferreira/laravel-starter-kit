<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\SiteResource;
use App\Models\Site;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class SitesController
{
    public function __invoke(): AnonymousResourceCollection
    {
        return SiteResource::collection(
            Site::query()->latest()->paginate(20)
        );
    }
}
