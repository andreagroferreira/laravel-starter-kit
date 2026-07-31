<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Contracts\BillingManager;
use App\Models\Tenant;
use App\Support\CurrentTenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

final class BillingCheckoutController
{
    public function __invoke(BillingManager $billing, string $plan): RedirectResponse
    {
        /** @var Tenant $tenant */
        $tenant = resolve(CurrentTenant::class)->getOrFail();

        Gate::authorize('manageBilling', $tenant);

        return $billing->checkout($tenant, $plan)->redirect();
    }
}
