<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\BillingManager;
use App\Models\Tenant;
use InvalidArgumentException;
use Laravel\Cashier\Checkout;
use Laravel\Cashier\Subscription;

final class BillingService implements BillingManager
{
    /**
     * Create a Stripe Checkout session to subscribe the tenant to a plan.
     */
    public function checkout(Tenant $tenant, string $plan): Checkout
    {
        $priceId = Plans::priceId($plan);

        throw_if($priceId === null, InvalidArgumentException::class, sprintf('Plan [%s] is not purchasable.', $plan));

        return $tenant->newSubscription('default', $priceId)
            ->checkout([
                'success_url' => route('settings.billing', ['subscribed' => 1]),
                'cancel_url' => route('settings.billing'),
            ]);
    }

    /**
     * URL for the Stripe Customer Portal.
     */
    public function portalUrl(Tenant $tenant): string
    {
        return $tenant->billingPortalUrl(route('settings.billing'));
    }

    /**
     * Sync the tenant's local plan/credits from its active subscription.
     */
    public function syncPlan(Tenant $tenant): void
    {
        $subscription = $tenant->subscription('default');

        if (! $subscription instanceof Subscription || ! $subscription->active()) {
            $tenant->forceFill([
                'plan' => 'free',
                'ai_credits_monthly' => Plans::aiCredits('free'),
            ])->save();

            return;
        }

        foreach (Plans::keys() as $plan) {
            $priceId = Plans::priceId($plan);

            if ($priceId !== null && $subscription->hasPrice($priceId)) {
                $tenant->forceFill([
                    'plan' => $plan,
                    'ai_credits_monthly' => Plans::aiCredits($plan),
                ])->save();

                return;
            }
        }
    }

    /**
     * Report AI credit overage usage to Stripe's meter (metered billing).
     */
    public function reportAiOverage(Tenant $tenant, int $credits): void
    {
        $tenant->subscription('default')?->reportUsage($credits);
    }
}
