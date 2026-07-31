<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Contracts\BillingManager;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;

final class BillingPortalController
{
    public function __invoke(BillingManager $billing): RedirectResponse
    {
        /** @var Tenant $tenant */
        $tenant = resolve(Tenant::class);

        return redirect($billing->portalUrl($tenant));
    }
}
