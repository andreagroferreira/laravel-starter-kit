<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\Customer;
use Illuminate\Auth\Events\Registered;

final class RegisterCustomer
{
    /**
     * @param array{name: string, email: string, password: string} $data
     *
     * @return array{customer: Customer, token: string}
     */
    public function __invoke(array $data): array
    {
        $customer = Customer::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        event(new Registered($customer));

        $token = $customer->createToken('register', ['*'], now()->addDays(30))->plainTextToken;

        return ['customer' => $customer, 'token' => $token];
    }
}
