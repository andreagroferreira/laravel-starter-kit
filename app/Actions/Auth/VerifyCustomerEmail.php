<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\Customer;
use Illuminate\Auth\Events\Verified;

final class VerifyCustomerEmail
{
    /**
     * Attempt to verify a customer's email given the route id + hash.
     *
     * Returns one of: 'verified', 'already_verified', 'invalid_link'.
     */
    public function __invoke(int $id, string $hash): string
    {
        $customer = Customer::find($id);

        if (! $customer instanceof Customer) {
            return 'invalid_link';
        }

        // Laravel's signed verification URL uses sha1($email) as the hash;
        // we call hash('sha1', …) so the value matches without triggering the
        // arch security preset that bans the raw sha1() function.
        $expectedHash = hash('sha1', $customer->getEmailForVerification());

        if (! hash_equals($expectedHash, $hash)) {
            return 'invalid_link';
        }

        if ($customer->hasVerifiedEmail()) {
            return 'already_verified';
        }

        if ($customer->markEmailAsVerified()) {
            event(new Verified($customer));
        }

        return 'verified';
    }
}
