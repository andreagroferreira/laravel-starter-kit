<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Requests\UpdateBrandProfileRequest;
use App\Models\Tenant;
use App\Support\CurrentTenant;
use Illuminate\Http\RedirectResponse;
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

    public function update(UpdateBrandProfileRequest $request): RedirectResponse
    {
        /** @var array{name: string, tone_of_voice?: string|null, glossary?: array<string, string>|null, examples?: list<string>|null} $validated */
        $validated = $request->validated();

        /** @var Tenant $tenant */
        $tenant = resolve(CurrentTenant::class)->getOrFail();

        $tenant->brandProfile()->updateOrCreate(
            ['tenant_id' => $tenant->id],
            $validated,
        );

        return back();
    }
}
