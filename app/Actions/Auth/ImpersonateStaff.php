<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

final class ImpersonateStaff
{
    public function __invoke(User $authUser, User $target): void
    {
        Session::put('impersonator_id', $authUser->id);

        activity('staff-impersonate')
            ->causedBy($authUser)
            ->performedOn($target)
            ->withProperties([
                'impersonator_id' => $authUser->id,
                'impersonator_email' => $authUser->email,
                'target_email' => $target->email,
            ])
            ->log('Started impersonation');

        Auth::guard('web')->login($target);
    }
}
