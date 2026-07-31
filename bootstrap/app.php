<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureAiCredits;
use App\Http\Middleware\EnsureSiteTenant;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\SetCurrentTenant;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

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
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
