<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Contracts\BillingManager;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;

final class BillingCheckoutController
{
    public function __invoke(BillingManager $billing, string $plan): RedirectResponse
    {
        /** @var Tenant $tenant */
        $tenant = resolve(Tenant::class);

        return $billing->checkout($tenant, $plan)->redirect();
    }
}
