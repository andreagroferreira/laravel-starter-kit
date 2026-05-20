<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\ImpersonateStaff;
use App\Http\Requests\Auth\Staff\ImpersonateRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

final class ImpersonateController
{
    public function __invoke(ImpersonateRequest $request, User $user, ImpersonateStaff $impersonate): RedirectResponse
    {
        /** @var User $authUser */
        $authUser = $request->user('web');
        $impersonate($authUser, $user);

        return redirect('/admin');
    }
}
