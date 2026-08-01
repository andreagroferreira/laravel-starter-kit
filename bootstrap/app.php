<?php

declare(strict_types=1);

use App\Exceptions\TenantNotResolved;
use App\Http\Middleware\EnsureAiCredits;
use App\Http\Middleware\EnsureSiteTenant;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SetCurrentTenant;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            SetCurrentTenant::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
        $middleware->api(append: [
            SetCurrentTenant::class,
        ]);
        $middleware->alias([
            'site.tenant' => EnsureSiteTenant::class,
            'ai.credits' => EnsureAiCredits::class,
            'abilities' => CheckAbilities::class,
            'ability' => CheckForAnyAbility::class,
        ]);

        // The tenant must be resolved BEFORE route model binding, otherwise
        // implicit bindings run without the tenant global scope and can
        // resolve another tenant's records.
        $middleware->prependToPriorityList(SubstituteBindings::class, SetCurrentTenant::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(fn (TenantNotResolved $e, Request $request) => abort(403, 'You do not belong to any workspace.'));
    })->create();
