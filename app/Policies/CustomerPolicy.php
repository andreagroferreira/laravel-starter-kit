<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Customer;

final class CustomerPolicy
{
    public function viewOwn(Customer $customer, Customer $target): bool
    {
        return $customer->id === $target->id;
    }

    public function updateOwn(Customer $customer, Customer $target): bool
    {
        return $customer->id === $target->id;
    }

    public function deleteOwn(Customer $customer, Customer $target): bool
    {
        return $customer->id === $target->id;
    }
}
