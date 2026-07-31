<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\BillingManager;
use App\Models\Tenant;
use App\Services\BillingService;
use App\Support\CurrentTenant;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BillingManager::class, BillingService::class);
        $this->app->scoped(CurrentTenant::class);
    }

    public function boot(): void
    {
        Cashier::useCustomerModel(Tenant::class);
    }
}
