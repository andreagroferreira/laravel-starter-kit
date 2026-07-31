<?php

declare(strict_types=1);

use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', fn (User $user, string $id): bool => $user->id === $id);

Broadcast::channel('tenant.{tenantId}', fn (User $user, string $tenantId): bool => $user->tenants()->whereKey($tenantId)->exists());

Broadcast::channel('site.{siteId}', fn (User $user, string $siteId): bool => Site::query()
    ->withoutGlobalScope('tenant')
    ->whereKey($siteId)
    ->whereIn('tenant_id', $user->tenants()->select('tenants.id'))
    ->exists());
