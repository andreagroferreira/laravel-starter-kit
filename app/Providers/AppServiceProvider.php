<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\BillingManager;
use App\Contracts\EdgeDeployer;
use App\Models\Tenant;
use App\Services\BillingService;
use App\Services\Deploy\CloudflareDeployer;
use App\Services\Deploy\NullDeployer;
use App\Support\CurrentTenant;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BillingManager::class, BillingService::class);
        $this->app->scoped(CurrentTenant::class);
        $this->app->bind(
            EdgeDeployer::class,
            fn (): EdgeDeployer => config()->string('services.cloudflare.token') === ''
                ? new NullDeployer
                : new CloudflareDeployer,
        );
    }

    public function boot(): void
    {
        Cashier::useCustomerModel(Tenant::class);

        RateLimiter::for(
            'form-submissions',
            fn (Request $request) => Limit::perMinute(10)->by((string) $request->ip()),
        );
    }
}
