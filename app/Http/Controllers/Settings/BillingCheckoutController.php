<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Contracts\BillingManager;
use App\Models\Tenant;
use App\Services\Plans;
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

        abort_unless(
            in_array($plan, Plans::keys(), true) && Plans::priceId($plan) !== null,
            404,
        );

        return $billing->checkout($tenant, $plan)->redirect();
    }
}
