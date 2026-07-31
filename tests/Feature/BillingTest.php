<?php

declare(strict_types=1);

use App\Contracts\BillingManager;
use App\Models\Tenant;
use App\Models\User;
use App\Services\BillingService;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Cashier\Checkout;

beforeEach(function (): void {
    $tenant = Tenant::factory()->create();
    $this->tenant = $tenant;

    $this->user = User::factory()->create(['current_tenant_id' => $tenant->id]);
    $tenant->users()->attach($this->user);
    grantRole($this->tenant, $this->user);
});

it('requires authentication for billing routes', function (): void {
    $this->get('/settings/billing')->assertRedirect('/login');
    $this->get('/settings/billing/portal')->assertRedirect('/login');
    $this->get('/settings/billing/checkout/pro')->assertRedirect('/login');
});

it('renders the billing page with the current plan', function (): void {
    $this->actingAs($this->user)
        ->get('/settings/billing')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Settings/Billing')
            ->where('plan', 'free')
            ->where('planDetails.name', 'Free')
            ->where('subscribed', false)
            ->has('availablePlans', 3)
        );
});

it('rejects checkout for the free plan and unknown plans', function (): void {
    $this->actingAs($this->user)
        ->get('/settings/billing/checkout/free')
        ->assertNotFound();

    $this->actingAs($this->user)
        ->get('/settings/billing/checkout/enterprise')
        ->assertNotFound();
});

it('redirects to stripe checkout for a paid plan', function (): void {
    config()->set('plans.plans.pro.stripe_price_id', 'price_pro_test');

    $checkout = Mockery::mock(Checkout::class);
    $checkout->shouldReceive('redirect')->once()->andReturn(redirect('https://checkout.stripe.com/session'));

    $billing = Mockery::mock(BillingManager::class);
    $billing->shouldReceive('checkout')
        ->once()
        ->withArgs(fn (Tenant $tenant, string $plan): bool => $tenant->is($this->tenant) && $plan === 'pro')
        ->andReturn($checkout);
    $this->instance(BillingManager::class, $billing);

    $this->actingAs($this->user)
        ->get('/settings/billing/checkout/pro')
        ->assertRedirect('https://checkout.stripe.com/session');
});

it('redirects to the billing portal', function (): void {
    $billing = Mockery::mock(BillingManager::class);
    $billing->shouldReceive('portalUrl')->once()->andReturn('https://billing.stripe.com/portal');
    $this->instance(BillingManager::class, $billing);

    $this->actingAs($this->user)
        ->get('/settings/billing/portal')
        ->assertRedirect('https://billing.stripe.com/portal');
});

it('syncs the tenant to free when there is no subscription', function (): void {
    resolve(BillingService::class)->syncPlan($this->tenant);

    expect($this->tenant->refresh()->plan)->toBe('free')
        ->and($this->tenant->ai_credits_monthly)->toBe(100);
});
