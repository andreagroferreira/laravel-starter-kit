<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Site;
use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureSiteTenant
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $site = $request->route('site');

        if ($site instanceof Site) {
            abort_unless(app()->bound(Tenant::class) && $site->tenant_id === resolve(Tenant::class)->id, 404);
        }

        return $next($request);
    }
}
