<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\IntegrationProvider;
use App\Jobs\Integrations\SyncGoogleAnalytics;
use App\Jobs\Integrations\SyncSearchConsole;
use App\Models\Integration;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Description('Queue the daily metric sync for every connected integration')]
#[Signature('integrations:sync')]
final class SyncIntegrationsCommand extends Command
{
    public function handle(): int
    {
        $queued = 0;

        $integrations = Integration::query()
            ->withoutGlobalScope('tenant')
            ->where('status', 'connected')
            ->whereNotNull('site_id')
            ->cursor();

        foreach ($integrations as $integration) {
            match ($integration->provider) {
                IntegrationProvider::GoogleAnalytics => dispatch(new SyncGoogleAnalytics($integration->id, $integration->tenant_id)),
                IntegrationProvider::SearchConsole => dispatch(new SyncSearchConsole($integration->id, $integration->tenant_id)),
                IntegrationProvider::Meta => null,
            };

            if ($integration->provider !== IntegrationProvider::Meta) {
                $queued++;
            }
        }

        $this->info(sprintf('Queued %d metric sync job(s).', $queued));

        return self::SUCCESS;
    }
}
