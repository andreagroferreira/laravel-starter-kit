<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

final class FormPolicy
{
    public function create(User $user): bool
    {
        return $user->can('forms.manage');
    }

    public function delete(User $user): bool
    {
        return $user->can('forms.manage');
    }
}
