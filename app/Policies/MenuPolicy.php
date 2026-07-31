<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

final class MenuPolicy
{
    public function create(User $user): bool
    {
        return $user->can('menus.manage');
    }

    public function update(User $user): bool
    {
        return $user->can('menus.manage');
    }

    public function delete(User $user): bool
    {
        return $user->can('menus.manage');
    }
}
