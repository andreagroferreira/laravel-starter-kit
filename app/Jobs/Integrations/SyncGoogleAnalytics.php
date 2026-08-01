<?php

declare(strict_types=1);

namespace App\Jobs\Integrations;

use App\Enums\IntegrationProvider;
use App\Models\Integration;
use App\Models\MetricSnapshot;
use App\Queue\Middleware\TenantAware;
use App\Services\Integrations\GoogleTokenRefresher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

/**
 * Pulls yesterday's GA4 numbers into metric_snapshots. Idempotent: the
 * unique (site, provider, date) index makes re-runs safe.
 */
final class SyncGoogleAnalytics implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly string $integrationId,
        private readonly string $tenantId,
    ) {}

    /**
     * @return list<object>
     */
    public function middleware(): array
    {
        return [new TenantAware($this->tenantId)];
    }

    public function handle(GoogleTokenRefresher $refresher): void
    {
        $integration = Integration::query()->findOrFail($this->integrationId);

        if ($integration->site_id === null || $integration->external_id === null) {
            return;
        }

        $token = $refresher->accessToken($integration);

        if ($token === null) {
            return;
        }

        $date = now()->subDay()->toDateString();

        $response = Http::withToken($token)
            ->post(sprintf(
                'https://analyticsdata.googleapis.com/v1beta/properties/%s:runReport',
                $integration->external_id,
            ), [
                'dateRanges' => [['startDate' => $date, 'endDate' => $date]],
                'metrics' => [
                    ['name' => 'sessions'],
                    ['name' => 'totalUsers'],
                    ['name' => 'screenPageViews'],
                ],
            ]);

        if ($response->failed()) {
            $integration->update(['status' => 'error']);

            return;
        }

        /** @var array{rows?: list<array{metricValues: list<array{value: string}>}>} $payload */
        $payload = $response->json();
        $values = $payload['rows'][0]['metricValues'] ?? [];

        MetricSnapshot::query()->updateOrCreate(
            [
                'site_id' => $integration->site_id,
                'provider' => IntegrationProvider::GoogleAnalytics,
                'metric_date' => $date,
            ],
            [
                'tenant_id' => $integration->tenant_id,
                'metrics' => [
                    'sessions' => (int) ($values[0]['value'] ?? 0),
                    'users' => (int) ($values[1]['value'] ?? 0),
                    'pageviews' => (int) ($values[2]['value'] ?? 0),
                ],
            ],
        );
    }
}
