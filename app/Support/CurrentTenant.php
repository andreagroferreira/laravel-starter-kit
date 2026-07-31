<?php

declare(strict_types=1);

namespace App\Support;

use App\Exceptions\TenantNotResolved;
use App\Models\Tenant;

/**
 * Octane-safe holder for the tenant of the current request/job lifecycle.
 *
 * Registered as a scoped binding: a fresh instance exists per request
 * (Octane) and per job (queue workers), so tenant state never leaks
 * between lifecycles the way a container instance() binding would.
 */
final class CurrentTenant
{
    private ?Tenant $tenant = null;

    public function set(Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function get(): ?Tenant
    {
        return $this->tenant;
    }

    public function getOrFail(): Tenant
    {
        if (! $this->tenant instanceof Tenant) {
            throw TenantNotResolved::make();
        }

        return $this->tenant;
    }

    public function has(): bool
    {
        return $this->tenant instanceof Tenant;
    }

    public function id(): ?string
    {
        return $this->tenant?->id;
    }

    public function forget(): void
    {
        $this->tenant = null;
    }
}
