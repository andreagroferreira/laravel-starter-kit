<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class RemoveMemberController
{
    public function __invoke(Request $request, User $user): RedirectResponse
    {
        /** @var Tenant $tenant */
        $tenant = resolve(Tenant::class);

        abort_if($user->id === $request->user()?->id, 422, 'You cannot remove yourself.');

        $tenant->users()->detach($user->id);

        setPermissionsTeamId($tenant->id);
        $user->syncRoles([]);

        return back();
    }
}
