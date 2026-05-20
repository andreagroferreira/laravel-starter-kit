<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\Customer;
use Illuminate\Support\Facades\Hash;

final class AuthenticateCustomer
{
    /**
     * Attempt to authenticate a customer by email/password.
     *
     * Returns the customer on success, null on failure. Does NOT issue tokens
     * or sessions — that's the caller's responsibility.
     */
    public function __invoke(string $email, string $password): ?Customer
    {
        $customer = Customer::query()->where('email', $email)->first();

        if (! $customer instanceof Customer) {
            return null;
        }

        if (! Hash::check($password, (string) $customer->password)) {
            return null;
        }

        return $customer;
    }
}
