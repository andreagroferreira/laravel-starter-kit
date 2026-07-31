<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Requests\StoreTokenRequest;
use App\Models\Tenant;
use App\Support\CurrentTenant;
use App\Support\TokenAbilities;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Sanctum\PersonalAccessToken;

final class TokenController
{
    public function show(): Response
    {
        /** @var Tenant $tenant */
        $tenant = resolve(CurrentTenant::class)->getOrFail();

        Gate::authorize('manageTokens', $tenant);

        $user = request()->user();

        return Inertia::render('Settings/Api', [
            'tokens' => $user?->tokens()
                ->latest()
                ->get(['id', 'name', 'abilities', 'last_used_at', 'created_at'])
                ->map(fn (PersonalAccessToken $token): array => [
                    'id' => $token->id,
                    'name' => $token->name,
                    'abilities' => $token->abilities,
                    'last_used_at' => $token->last_used_at?->diffForHumans(),
                    'created_at' => $token->created_at?->toDateString(),
                ]),
            'availableAbilities' => TokenAbilities::ALL,
            'mcpEndpoint' => url('/mcp/wizard'),
            'tenant' => $tenant->only('name', 'slug'),
        ]);
    }

    public function store(StoreTokenRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        /** @var array{name: string, abilities: list<string>} $abilities */
        $abilities = $validated;

        $token = $request->user()?->createToken($abilities['name'], $abilities['abilities']);

        return back()->with('token', [
            'name' => $abilities['name'],
            'plainTextToken' => $token?->plainTextToken,
        ]);
    }

    public function destroy(Request $request, string $tokenId): RedirectResponse
    {
        Gate::authorize('manageTokens', resolve(CurrentTenant::class)->getOrFail());

        $request->user()?->tokens()->where('id', $tokenId)->delete();

        return back();
    }
}
