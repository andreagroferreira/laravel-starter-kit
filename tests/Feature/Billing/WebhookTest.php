<?php

declare(strict_types=1);

use App\Models\Tenant;
use App\Services\Plans;
use Laravel\Cashier\Events\WebhookHandled;

it('syncs the tenant plan when a subscription webhook arrives', function (): void {
    config()->set('plans.plans.pro.stripe_price_id', 'price_pro_test');

    $tenant = Tenant::factory()->create([
        'stripe_id' => 'cus_test_123',
        'plan' => 'free',
    ]);

    $tenant->subscriptions()->create([
        'type' => 'default',
        'stripe_id' => 'sub_test_123',
        'stripe_status' => 'active',
        'stripe_price' => 'price_pro_test',
        'quantity' => 1,
    ]);

    event(new WebhookHandled([
        'type' => 'customer.subscription.updated',
        'data' => ['object' => ['customer' => 'cus_test_123']],
    ]));

    $tenant->refresh();

    expect($tenant->plan)->toBe('pro')
        ->and($tenant->ai_credits_monthly)->toBe(Plans::aiCredits('pro'));
});

it('downgrades to free when the subscription is deleted', function (): void {
    $tenant = Tenant::factory()->create([
        'stripe_id' => 'cus_test_456',
        'plan' => 'pro',
        'ai_credits_monthly' => 2000,
    ]);

    event(new WebhookHandled([
        'type' => 'customer.subscription.deleted',
        'data' => ['object' => ['customer' => 'cus_test_456']],
    ]));

    $tenant->refresh();

    expect($tenant->plan)->toBe('free')
        ->and($tenant->ai_credits_monthly)->toBe(Plans::aiCredits('free'));
});

it('ignores unrelated webhook events and unknown customers', function (): void {
    $tenant = Tenant::factory()->create(['stripe_id' => 'cus_test_789', 'plan' => 'pro']);

    event(new WebhookHandled(['type' => 'invoice.paid', 'data' => ['object' => ['customer' => 'cus_test_789']]]));
    event(new WebhookHandled(['type' => 'customer.subscription.updated', 'data' => ['object' => ['customer' => 'cus_unknown']]]));
    event(new WebhookHandled(['type' => 'customer.subscription.updated', 'data' => ['object' => []]]));

    expect($tenant->refresh()->plan)->toBe('pro');
});
