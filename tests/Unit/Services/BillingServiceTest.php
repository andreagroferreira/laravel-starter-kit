<?php

declare(strict_types=1);

use App\Contracts\BillingManager;
use App\Models\Tenant;
use App\Services\BillingService;
use App\Services\Plans;
use Laravel\Cashier\Subscription;
use Stripe\ApiRequestor;
use Stripe\HttpClient\ClientInterface;
use Stripe\HttpClient\CurlClient;

/**
 * Minimal fake Stripe HTTP client that returns canned JSON per endpoint substring.
 *
 * @param  array<string, string>  $responses
 */
final class FakeStripeHttpClient implements ClientInterface
{
    /** @var list<array{string, string}> */
    public array $requests = [];

    public function __construct(private readonly array $responses) {}

    public function request($method, $absUrl, $headers, $params, $hasFile, $apiMode = 'v1', $maxNetworkRetries = null): array
    {
        $this->requests[] = [$method, $absUrl];

        foreach ($this->responses as $needle => $json) {
            if (str_contains($absUrl, (string) $needle)) {
                return [$json, 200, []];
            }
        }

        return ['{}', 200, []];
    }
}

afterEach(function (): void {
    ApiRequestor::setHttpClient(CurlClient::instance());
});

it('creates a checkout session for a paid plan', function (): void {
    config()->set('plans.plans.pro.stripe_price_id', 'price_pro_123');

    ApiRequestor::setHttpClient(new FakeStripeHttpClient([
        '/v1/checkout/sessions' => json_encode(['object' => 'checkout.session', 'id' => 'cs_123', 'url' => 'https://checkout.stripe.com/cs_123']),
        '/v1/customers' => json_encode(['id' => 'cus_123']),
    ]));

    $tenant = Tenant::factory()->create(['stripe_id' => 'cus_123']);

    $checkout = resolve(BillingManager::class)->checkout($tenant, 'pro');

    expect($checkout->url)->toBe('https://checkout.stripe.com/cs_123');
});

it('builds the billing portal url', function (): void {
    ApiRequestor::setHttpClient(new FakeStripeHttpClient([
        '/v1/billing_portal/sessions' => json_encode(['url' => 'https://billing.stripe.com/portal_123']),
    ]));

    $tenant = Tenant::factory()->create(['stripe_id' => 'cus_123']);

    $url = resolve(BillingManager::class)->portalUrl($tenant);

    expect($url)->toBe('https://billing.stripe.com/portal_123');
});

it('syncs the plan from an active subscription', function (): void {
    config()->set('plans.plans.pro.stripe_price_id', 'price_pro_123');

    $tenant = Tenant::factory()->create(['stripe_id' => 'cus_123']);

    Subscription::query()->forceCreate([
        'tenant_id' => $tenant->id,
        'type' => 'default',
        'stripe_id' => 'sub_123',
        'stripe_status' => 'active',
        'stripe_price' => 'price_pro_123',
        'quantity' => 1,
    ]);

    resolve(BillingService::class)->syncPlan($tenant);

    expect($tenant->refresh()->plan)->toBe('pro')
        ->and($tenant->ai_credits_monthly)->toBe(Plans::aiCredits('pro'));
});

it('reports ai overage usage to the meter', function (): void {
    $fake = new FakeStripeHttpClient([
        'meter_events' => json_encode(['event_name' => 'ai_credits']),
    ]);
    ApiRequestor::setHttpClient($fake);

    $tenant = Tenant::factory()->create(['stripe_id' => 'cus_123']);

    $subscription = Subscription::query()->forceCreate([
        'tenant_id' => $tenant->id,
        'type' => 'default',
        'stripe_id' => 'sub_123',
        'stripe_status' => 'active',
        'stripe_price' => 'price_pro_123',
        'quantity' => 1,
    ]);

    $subscription->items()->forceCreate([
        'stripe_id' => 'si_123',
        'stripe_product' => 'prod_123',
        'stripe_price' => 'price_pro_123',
        'quantity' => 1,
        'meter_id' => 'mtr_123',
        'meter_event_name' => 'ai_credits',
    ]);

    resolve(BillingService::class)->reportAiOverage($tenant, 42);

    expect($fake->requests)->not->toBeEmpty();
});
