<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

final class FormSubmissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('leads.view');
    }

    public function update(User $user): bool
    {
        return $user->can('leads.view');
    }

    public function delete(User $user): bool
    {
        return $user->can('leads.view');
    }

    public function export(User $user): bool
    {
        return $user->can('leads.export');
    }
}
