<?php

declare(strict_types=1);

namespace App\Services\Integrations;

use App\Models\Integration;
use Illuminate\Support\Facades\Http;

/**
 * Google access tokens live for an hour; every sync goes through here so
 * a long-lived refresh token is all we need to keep.
 */
final class GoogleTokenRefresher
{
    public function accessToken(Integration $integration): ?string
    {
        if (! $integration->isExpired()) {
            return $integration->access_token;
        }

        if ($integration->refresh_token === null) {
            $integration->update(['status' => 'error']);

            return null;
        }

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => config()->string('services.google.client_id'),
            'client_secret' => config()->string('services.google.client_secret'),
            'refresh_token' => $integration->refresh_token,
            'grant_type' => 'refresh_token',
        ]);

        if ($response->failed()) {
            $integration->update(['status' => 'error']);

            return null;
        }

        /** @var array{access_token?: string, expires_in?: int} $payload */
        $payload = $response->json();

        if (! isset($payload['access_token'])) {
            $integration->update(['status' => 'error']);

            return null;
        }

        $integration->update([
            'access_token' => $payload['access_token'],
            'expires_at' => now()->addSeconds($payload['expires_in'] ?? 3600),
            'status' => 'connected',
        ]);

        return $payload['access_token'];
    }
}
