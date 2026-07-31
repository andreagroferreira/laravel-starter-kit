<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

final class BrandProfilePolicy
{
    public function manage(User $user): bool
    {
        return $user->can('brand.manage');
    }
}
