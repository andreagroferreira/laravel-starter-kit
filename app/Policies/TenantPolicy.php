<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

final class TenantPolicy
{
    public function manageMembers(User $user): bool
    {
        return $user->can('members.manage');
    }

    public function manageBilling(User $user): bool
    {
        return $user->can('billing.manage');
    }

    public function manageTokens(User $user): bool
    {
        return $user->can('tokens.manage');
    }

    public function viewAudit(User $user): bool
    {
        return $user->can('audit.view');
    }
}
