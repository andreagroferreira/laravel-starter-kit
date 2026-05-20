<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\AuthenticateCustomer;
use App\Http\Requests\Auth\Customer\LoginRequest;
use App\Http\Resources\Customer\CustomerTokenResource;
use Illuminate\Http\JsonResponse;

final class LoginController
{
    public function __invoke(LoginRequest $request, AuthenticateCustomer $authenticate): JsonResponse
    {
        $email = $request->string('email')->toString();
        $password = $request->string('password')->toString();

        $customer = $authenticate($email, $password);

        if ($customer === null) {
            return response()->json([
                'type' => 'https://wizardingcode.io/errors/auth',
                'title' => 'Invalid Credentials',
                'status' => 401,
                'detail' => 'The provided credentials are incorrect.',
            ], 401, [], JSON_UNESCAPED_SLASHES)
                ->header('Content-Type', 'application/problem+json');
        }

        $customer->forceFill(['last_login_at' => now()])->save();

        $deviceName = $request->string('device_name', 'api')->toString();
        $token = $customer->createToken($deviceName, ['*'], now()->addDays(30))->plainTextToken;

        return CustomerTokenResource::make((object) ['customer' => $customer, 'token' => $token])->response();
    }
}
