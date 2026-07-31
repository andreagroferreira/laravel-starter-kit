<?php

declare(strict_types=1);

use App\Contracts\BillingManager;
use App\Models\AiOverageReport;
use App\Models\AiUsage;
use App\Models\Tenant;

beforeEach(function (): void {
    $this->billing = Mockery::mock(BillingManager::class);
    $this->app->instance(BillingManager::class, $this->billing);
});

function subscribedTenant(int $allowance): Tenant
{
    $tenant = Tenant::factory()->create([
        'stripe_id' => 'cus_'.fake()->unique()->lexify('????????'),
        'ai_credits_monthly' => $allowance,
    ]);

    $tenant->subscriptions()->create([
        'type' => 'default',
        'stripe_id' => 'sub_'.fake()->unique()->lexify('????????'),
        'stripe_status' => 'active',
        'stripe_price' => 'price_x',
        'quantity' => 1,
    ]);

    return $tenant;
}

it('reports only the unbilled overage delta', function (): void {
    $tenant = subscribedTenant(5);
    AiUsage::factory()->for($tenant)->count(8)->create(['credits' => 1]);

    // 8 used - 5 allowance = 3 overage on the first run…
    $this->billing->shouldReceive('reportAiOverage')->once()->withArgs(
        fn (Tenant $t, int $credits): bool => $t->is($tenant) && $credits === 3,
    );

    $this->artisan('billing:report-ai-overage')->assertSuccessful();

    expect(AiOverageReport::query()->withoutGlobalScope('tenant')->sole()->credits_reported)->toBe(3);

    // …and nothing more when usage has not grown.
    $this->artisan('billing:report-ai-overage')->assertSuccessful();
});

it('skips tenants without overage or without an active subscription', function (): void {
    $within = subscribedTenant(100);
    AiUsage::factory()->for($within)->count(3)->create(['credits' => 1]);

    $unsubscribed = Tenant::factory()->create(['stripe_id' => null, 'ai_credits_monthly' => 0]);
    AiUsage::factory()->for($unsubscribed)->count(5)->create(['credits' => 1]);

    // Customer no Stripe mas sem subscrição: ignorado.
    $lapsed = Tenant::factory()->create(['stripe_id' => 'cus_lapsed', 'ai_credits_monthly' => 0]);
    AiUsage::factory()->for($lapsed)->count(5)->create(['credits' => 1]);

    // Subscrição cancelada (inativa): também ignorado.
    $canceled = Tenant::factory()->create(['stripe_id' => 'cus_canceled', 'ai_credits_monthly' => 0]);
    $canceled->subscriptions()->create([
        'type' => 'default',
        'stripe_id' => 'sub_canceled',
        'stripe_status' => 'canceled',
        'stripe_price' => 'price_x',
        'quantity' => 1,
        'ends_at' => now()->subDay(),
    ]);
    AiUsage::factory()->for($canceled)->count(5)->create(['credits' => 1]);

    $this->billing->shouldNotReceive('reportAiOverage');

    $this->artisan('billing:report-ai-overage')
        ->expectsOutputToContain('Reported 0 overage credit(s).')
        ->assertSuccessful();
});
