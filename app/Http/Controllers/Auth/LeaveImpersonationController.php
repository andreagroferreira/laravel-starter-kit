<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\LeaveImpersonation;
use Illuminate\Http\RedirectResponse;

final class LeaveImpersonationController
{
    public function __invoke(LeaveImpersonation $leave): RedirectResponse
    {
        $leave();

        return redirect('/admin');
    }
}
