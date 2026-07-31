<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Enums\TenantRole;
use App\Models\Tenant;
use App\Models\User;
use App\Support\CurrentTenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

final class InviteMemberController
{
    public function __invoke(Request $request): RedirectResponse
    {
        /** @var array{email: string, role: string} $validated */
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', Rule::enum(TenantRole::class)],
        ]);

        /** @var Tenant $tenant */
        $tenant = resolve(CurrentTenant::class)->getOrFail();

        $user = User::query()->where('email', $validated['email'])->first();

        if ($user === null) {
            $user = User::query()->create([
                'name' => Str::before($validated['email'], '@'),
                'email' => $validated['email'],
                'password' => Str::random(32),
            ]);
        }

        $tenant->users()->syncWithoutDetaching([$user->id => ['joined_at' => now()]]);

        setPermissionsTeamId($tenant->id);

        foreach (TenantRole::cases() as $role) {
            Role::findOrCreate($role);
        }

        $user->syncRoles([$validated['role']]);

        return back();
    }
}
