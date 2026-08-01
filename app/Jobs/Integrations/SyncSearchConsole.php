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
 * Search Console reports lag by ~3 days, so we always sync that window.
 */
final class SyncSearchConsole implements ShouldQueue
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

        $date = now()->subDays(3)->toDateString();

        $response = Http::withToken($token)->post(sprintf(
            'https://searchconsole.googleapis.com/webmasters/v3/sites/%s/searchAnalytics/query',
            rawurlencode($integration->external_id),
        ), [
            'startDate' => $date,
            'endDate' => $date,
            'dimensions' => ['query'],
            'rowLimit' => 25,
        ]);

        if ($response->failed()) {
            $integration->update(['status' => 'error']);

            return;
        }

        /** @var array{rows?: list<array{keys: list<string>, clicks: float, impressions: float, position: float}>} $payload */
        $payload = $response->json();
        $rows = $payload['rows'] ?? [];

        MetricSnapshot::query()->updateOrCreate(
            [
                'site_id' => $integration->site_id,
                'provider' => IntegrationProvider::SearchConsole,
                'metric_date' => $date,
            ],
            [
                'tenant_id' => $integration->tenant_id,
                'metrics' => [
                    'clicks' => (int) array_sum(array_column($rows, 'clicks')),
                    'impressions' => (int) array_sum(array_column($rows, 'impressions')),
                    'queries' => array_map(fn (array $row): array => [
                        'query' => $row['keys'][0] ?? '',
                        'clicks' => (int) $row['clicks'],
                        'impressions' => (int) $row['impressions'],
                        'position' => round($row['position'], 1),
                    ], array_slice($rows, 0, 10)),
                ],
            ],
        );
    }
}
