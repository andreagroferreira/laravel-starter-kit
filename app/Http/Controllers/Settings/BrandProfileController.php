<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Models\Tenant;
use App\Support\CurrentTenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class BrandProfileController
{
    public function show(): Response
    {
        /** @var Tenant $tenant */
        $tenant = resolve(CurrentTenant::class)->getOrFail();

        $profile = $tenant->brandProfile;

        return Inertia::render('Settings/Brand', [
            'profile' => $profile === null ? null : [
                'name' => $profile->name,
                'tone_of_voice' => $profile->tone_of_voice,
                'glossary' => $profile->glossary ?? [],
                'examples' => $profile->examples ?? [],
                'guardrails' => $profile->guardrails ?? [],
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        /** @var array{name: string, tone_of_voice?: string|null, glossary?: array<string, string>|null, examples?: list<string>|null} $validated */
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'tone_of_voice' => ['nullable', 'string', 'max:2000'],
            'glossary' => ['nullable', 'array'],
            'examples' => ['nullable', 'array'],
            'examples.*' => ['string', 'max:1000'],
        ]);

        /** @var Tenant $tenant */
        $tenant = resolve(CurrentTenant::class)->getOrFail();

        $tenant->brandProfile()->updateOrCreate(
            ['tenant_id' => $tenant->id],
            $validated,
        );

        return back();
    }
}
