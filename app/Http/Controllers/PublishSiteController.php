<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Site;
use App\Services\SiteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class PublishSiteController
{
    public function __invoke(Request $request, Site $site, SiteService $sites): RedirectResponse
    {

        $sites->publish($site, $request->user());

        return back();
    }
}
