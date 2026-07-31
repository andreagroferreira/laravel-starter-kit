<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Models\Tenant;
use App\Models\User;
use App\Support\CurrentTenant;
use Inertia\Inertia;
use Inertia\Response;

final class MembersController
{
    public function __invoke(): Response
    {
        /** @var Tenant $tenant */
        $tenant = resolve(CurrentTenant::class)->getOrFail();

        return Inertia::render('Settings/Members', [
            'members' => $tenant->users()
                ->get(['users.id', 'users.name', 'users.email'])
                ->map(fn (User $user): array => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->getRoleNames()->first() ?? 'member',
                ]),
        ]);
    }
}
