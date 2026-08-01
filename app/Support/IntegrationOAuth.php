<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\IntegrationProvider;

final class IntegrationOAuth
{
    /**
     * Resolve a provider from the route segment, 404 for anything else.
     */
    public static function provider(string $provider): IntegrationProvider
    {
        $resolved = IntegrationProvider::tryFrom($provider);

        abort_if($resolved === null, 404);

        return $resolved;
    }
}
