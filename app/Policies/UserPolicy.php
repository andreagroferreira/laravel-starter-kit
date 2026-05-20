<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

final class UserPolicy
{
    public function impersonate(User $authUser, User $target): bool
    {
        if ($authUser->id === $target->id) {
            return false;
        }

        if ($target->hasRole('super-admin')) {
            return false;
        }

        return $authUser->can('impersonate users');
    }
}
