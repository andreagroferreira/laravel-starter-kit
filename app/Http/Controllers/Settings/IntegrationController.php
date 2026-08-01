<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Enums\IntegrationProvider;
use App\Models\Integration;
use App\Models\MetricSnapshot;
use App\Models\Site;
use App\Models\Tenant;
use App\Support\CurrentTenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

final class IntegrationController
{
    public function index(): Response
    {
        /** @var Tenant $tenant */
        $tenant = resolve(CurrentTenant::class)->getOrFail();

        Gate::authorize('manageBilling', $tenant);

        return Inertia::render('Settings/Integrations', [
            'sites' => Site::query()->orderBy('name')->get(['id', 'name']),
            'providers' => array_map(fn (IntegrationProvider $provider): array => [
                'value' => $provider->value,
                'label' => $provider->label(),
                // App reviews take weeks; the UI says so instead of failing.
                'configured' => config()->string('services.'.$provider->driver().'.client_id') !== '',
            ], IntegrationProvider::cases()),
            'integrations' => Integration::query()
                ->with('site:id,name')
                ->get()
                ->map(fn (Integration $integration): array => [
                    'id' => $integration->id,
                    'provider' => $integration->provider->value,
                    'label' => $integration->provider->label(),
                    'site' => $integration->site?->name,
                    'site_id' => $integration->site_id,
                    'external_id' => $integration->external_id,
                    'status' => $integration->status,
                    'connected_at' => $integration->connected_at?->toIso8601String(),
                ]),
            'metrics' => MetricSnapshot::query()
                ->with('site:id,name')
                ->latest('metric_date')
                ->limit(10)
                ->get()
                ->map(fn (MetricSnapshot $snapshot): array => [
                    'id' => $snapshot->id,
                    'site' => $snapshot->site->name,
                    'provider' => $snapshot->provider->value,
                    'date' => $snapshot->metric_date->toDateString(),
                    'metrics' => $snapshot->metrics,
                ]),
        ]);
    }

    public function update(Request $request, Integration $integration): RedirectResponse
    {
        /** @var Tenant $tenant */
        $tenant = resolve(CurrentTenant::class)->getOrFail();

        Gate::authorize('manageBilling', $tenant);

        /** @var array{site_id?: string|null, external_id?: string|null} $validated */
        $validated = $request->validate([
            'site_id' => ['nullable', 'uuid'],
            'external_id' => ['nullable', 'string', 'max:255'],
        ]);

        $integration->update($validated);

        return back()->with('success', 'Integração atualizada.');
    }

    public function destroy(Integration $integration): RedirectResponse
    {
        /** @var Tenant $tenant */
        $tenant = resolve(CurrentTenant::class)->getOrFail();

        Gate::authorize('manageBilling', $tenant);

        $integration->delete();

        return back()->with('success', 'Integração desligada.');
    }
}
