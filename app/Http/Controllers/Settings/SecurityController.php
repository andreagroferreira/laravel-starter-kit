<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class SecurityController
{
    public function __invoke(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Settings/Security', [
            'twoFactorEnabled' => $user->two_factor_secret !== null,
            'twoFactorConfirmed' => $user->two_factor_confirmed_at !== null,
        ]);
    }
}
