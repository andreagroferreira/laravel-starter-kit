<?php

declare(strict_types=1);

namespace App\Mcp\Concerns;

use App\Models\User;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;

/**
 * Draft-first scope enforcement for agent tools: the token must carry the
 * required ability AND the user behind it must hold the matching tenant
 * permission. Web sessions carry a TransientToken (all abilities), so the
 * permission check is what protects the backoffice path.
 */
trait EnforcesAbilities
{
    private function deniedFor(Request $request, string $ability, string $permission): ?Response
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return Response::error('Unauthenticated.');
        }

        // Session logins carry a TransientToken (always allowed); personal
        // access tokens (agents, CI) must hold the ability explicitly.
        if (! $user->tokenCan($ability)) {
            return Response::error(sprintf('This tool requires the [%s] token ability.', $ability));
        }

        if (! $user->can($permission)) {
            return Response::error(sprintf('You are missing the [%s] permission in this workspace.', $permission));
        }

        return null;
    }
}
