<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Http\Requests\InviteMemberRequest;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantProvisioner;
use App\Support\CurrentTenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

final class InviteMemberController
{
    public function __invoke(InviteMemberRequest $request, TenantProvisioner $provisioner): RedirectResponse
    {
        /** @var Tenant $tenant */
        $tenant = resolve(CurrentTenant::class)->getOrFail();

        /** @var array{email: string, role: string} $validated */
        $validated = $request->validated();

        $user = User::query()->where('email', $validated['email'])->first();

        if ($user === null) {
            $user = User::query()->create([
                'name' => Str::before($validated['email'], '@'),
                'email' => $validated['email'],
                'password' => Str::random(32),
            ]);
        }

        $tenant->users()->syncWithoutDetaching([$user->id => ['joined_at' => now()]]);

        $provisioner->provision($tenant);

        setPermissionsTeamId($tenant->id);

        $user->syncRoles([$validated['role']]);

        return back();
    }
}
