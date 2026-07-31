<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

final class MediaAssetPolicy
{
    public function create(User $user): bool
    {
        return $user->can('media.manage');
    }

    public function update(User $user): bool
    {
        return $user->can('media.manage');
    }

    public function delete(User $user): bool
    {
        return $user->can('media.manage');
    }
}
