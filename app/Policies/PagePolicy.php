<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

final class PagePolicy
{
    public function create(User $user): bool
    {
        return $user->can('pages.manage');
    }

    public function update(User $user): bool
    {
        return $user->can('pages.manage');
    }

    public function delete(User $user): bool
    {
        return $user->can('pages.manage');
    }

    public function publish(User $user): bool
    {
        return $user->can('pages.publish');
    }
}
