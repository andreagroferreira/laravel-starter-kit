<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Enums\TenantRole;
use App\Models\Tenant;
use App\Models\User;
use App\Support\CurrentTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

final class RemoveMemberController
{
    public function __invoke(Request $request, User $user): RedirectResponse
    {
        /** @var Tenant $tenant */
        $tenant = resolve(CurrentTenant::class)->getOrFail();

        Gate::authorize('manageMembers', $tenant);

        abort_if($user->id === $request->user()?->id, 422, 'You cannot remove yourself.');

        setPermissionsTeamId($tenant->id);

        if ($user->hasRole(TenantRole::Owner)) {
            $owners = User::query()
                ->role(TenantRole::Owner)
                ->whereHas('tenants', fn (Builder $query) => $query->whereKey($tenant->id))
                ->count();

            abort_if($owners <= 1, 422, 'You cannot remove the last owner.');
        }

        $tenant->users()->detach($user->id);

        $user->syncRoles([]);

        return back();
    }
}
