<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\EdgeDeployer;
use App\Enums\DeploymentStatus;
use App\Events\DeploymentUpdated;
use App\Models\Deployment;
use App\Queue\Middleware\TenantAware;
use App\Support\SiteUrl;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Makes a published version reachable: provision the hostname the first
 * time, then purge the CDN so the new schema is served immediately.
 */
final class DeploySite implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly string $deploymentId,
        private readonly string $tenantId,
    ) {}

    /**
     * @return list<object>
     */
    public function middleware(): array
    {
        return [new TenantAware($this->tenantId)];
    }

    public function handle(EdgeDeployer $deployer): void
    {
        $deployment = Deployment::query()->findOrFail($this->deploymentId);
        $site = $deployment->site;

        $deployment->update(['status' => DeploymentStatus::Running]);
        broadcast(new DeploymentUpdated($deployment));

        try {
            $url = $deployer->provisionDomain($site);
            $deployer->purge($site, SiteUrl::purgeable($site));

            $deployment->update([
                'status' => DeploymentStatus::Deployed,
                'url' => $url,
                'finished_at' => now(),
            ]);
        } catch (Throwable $throwable) {
            $deployment->update([
                'status' => DeploymentStatus::Failed,
                'error' => $throwable->getMessage(),
                'finished_at' => now(),
            ]);
        }

        broadcast(new DeploymentUpdated($deployment));
    }
}
