<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Models\Tenant;
use App\Services\Plans;
use App\Support\CurrentTenant;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

final class BillingController
{
    public function show(): Response
    {
        /** @var Tenant $tenant */
        $tenant = resolve(CurrentTenant::class)->getOrFail();
        Gate::authorize('manageBilling', $tenant);

        $subscription = $tenant->subscription('default');

        return Inertia::render('Settings/Billing', [
            'plan' => $tenant->plan,
            'planDetails' => Plans::get($tenant->plan),
            'availablePlans' => config('plans.plans'),
            'subscribed' => $subscription !== null && $subscription->active(),
            'onGracePeriod' => $subscription?->onGracePeriod() ?? false,
            'endsAt' => $subscription?->ends_at?->toDateString(),
        ]);
    }
}
