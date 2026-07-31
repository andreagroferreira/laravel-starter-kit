<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SetCurrentTenant
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof User) {
            $tenant = $user->currentTenant ?? $user->tenants()->first();

            if ($tenant instanceof Tenant) {
                app()->instance(Tenant::class, $tenant);
                setPermissionsTeamId($tenant);
            }
        }

        return $next($request);
    }
}
