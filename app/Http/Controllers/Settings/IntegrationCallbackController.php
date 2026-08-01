<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Models\Integration;
use App\Models\Tenant;
use App\Support\CurrentTenant;
use App\Support\IntegrationOAuth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use RuntimeException;
use Throwable;

final class IntegrationCallbackController
{
    public function __invoke(string $provider): RedirectResponse
    {
        $integration = IntegrationOAuth::provider($provider);

        /** @var Tenant $tenant */
        $tenant = resolve(CurrentTenant::class)->getOrFail();

        Gate::authorize('manageBilling', $tenant);

        try {
            $oauthUser = Socialite::driver($integration->driver())->user();

            throw_unless($oauthUser instanceof SocialiteUser, RuntimeException::class, 'Unexpected OAuth user payload.');
        } catch (Throwable) {
            return to_route('settings.integrations')
                ->with('error', 'Não foi possível concluir a ligação.');
        }

        Integration::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->id,
                'site_id' => null,
                'provider' => $integration,
            ],
            [
                'access_token' => $oauthUser->token,
                'refresh_token' => $oauthUser->refreshToken,
                'expires_at' => now()->addSeconds($oauthUser->expiresIn),
                'status' => 'connected',
                'connected_at' => now(),
                'meta' => ['email' => $oauthUser->getEmail()],
            ],
        );

        return to_route('settings.integrations')
            ->with('success', sprintf('%s ligado. Escolhe o site e a propriedade.', $integration->label()));
    }
}
