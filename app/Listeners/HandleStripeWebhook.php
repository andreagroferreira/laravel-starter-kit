<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Contracts\BillingManager;
use App\Models\Tenant;
use Laravel\Cashier\Events\WebhookHandled;

/**
 * Closes the billing loop: after Cashier processes a subscription webhook,
 * sync the tenant's local plan and AI credit allowance.
 */
final readonly class HandleStripeWebhook
{
    public function __construct(private BillingManager $billing) {}

    public function handle(WebhookHandled $event): void
    {
        $type = $event->payload['type'] ?? null;

        if (! in_array($type, [
            'customer.subscription.created',
            'customer.subscription.updated',
            'customer.subscription.deleted',
        ], true)) {
            return;
        }

        $data = $event->payload['data'] ?? null;
        $object = is_array($data) ? ($data['object'] ?? null) : null;
        $customerId = is_array($object) ? ($object['customer'] ?? null) : null;

        if (! is_string($customerId)) {
            return;
        }

        $tenant = Tenant::query()->where('stripe_id', $customerId)->first();

        if ($tenant instanceof Tenant) {
            $this->billing->syncPlan($tenant);
        }
    }
}
