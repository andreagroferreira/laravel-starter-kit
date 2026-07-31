<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

final class SitePolicy
{
    public function create(User $user): bool
    {
        return $user->can('sites.create');
    }

    public function delete(User $user): bool
    {
        return $user->can('sites.delete');
    }

    public function publish(User $user): bool
    {
        return $user->can('sites.publish');
    }
}
