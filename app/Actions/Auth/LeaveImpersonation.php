<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

final class LeaveImpersonation
{
    public function __invoke(): ?User
    {
        $impersonatorId = Session::pull('impersonator_id');
        if (! is_string($impersonatorId) && ! is_int($impersonatorId)) {
            return null;
        }

        $impersonator = User::query()->find($impersonatorId);
        if (! $impersonator instanceof User) {
            return null;
        }

        activity('staff-impersonate')
            ->causedBy($impersonator)
            ->withProperties([
                'impersonator_id' => $impersonator->id,
                'left_impersonating_user_id' => Auth::guard('web')->id(),
            ])
            ->log('Left impersonation');

        Auth::guard('web')->login($impersonator);

        return $impersonator;
    }
}
