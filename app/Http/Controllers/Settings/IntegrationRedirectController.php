<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Models\Tenant;
use App\Support\CurrentTenant;
use App\Support\IntegrationOAuth;
use Illuminate\Support\Facades\Gate;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use Symfony\Component\HttpFoundation\RedirectResponse;

final class IntegrationRedirectController
{
    public function __invoke(string $provider): RedirectResponse
    {
        $integration = IntegrationOAuth::provider($provider);

        /** @var Tenant $tenant */
        $tenant = resolve(CurrentTenant::class)->getOrFail();

        Gate::authorize('manageBilling', $tenant);

        $driver = Socialite::driver($integration->driver());

        // Only the OAuth2 providers expose scopes()/with(); every provider
        // we support is one.
        return $driver instanceof AbstractProvider
            ? $driver->scopes($integration->scopes())
                ->with(['access_type' => 'offline', 'prompt' => 'consent'])
                ->redirect()
            : $driver->redirect();
    }
}
