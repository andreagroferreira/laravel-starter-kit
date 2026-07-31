<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Tenant;
use Laravel\Cashier\Checkout;

interface BillingManager
{
    public function checkout(Tenant $tenant, string $plan): Checkout;

    public function portalUrl(Tenant $tenant): string;

    public function syncPlan(Tenant $tenant): void;

    public function reportAiOverage(Tenant $tenant, int $credits): void;
}
