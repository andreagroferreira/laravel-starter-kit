<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

final class RedirectPolicy
{
    public function create(User $user): bool
    {
        return $user->can('redirects.manage');
    }

    public function delete(User $user): bool
    {
        return $user->can('redirects.manage');
    }
}
