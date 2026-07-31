<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Site;
use App\Support\CurrentTenant;
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
            $current = resolve(CurrentTenant::class);

            abort_unless($current->has() && $site->tenant_id === $current->id(), 404);
        }

        return $next($request);
    }
}
