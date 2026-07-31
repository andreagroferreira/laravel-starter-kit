<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Site;
use App\Services\SiteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class PublishSiteController
{
    public function __invoke(Request $request, Site $site, SiteService $sites): RedirectResponse
    {
        Gate::authorize('publish', $site);

        $sites->publish($site, $request->user());

        return back();
    }
}
