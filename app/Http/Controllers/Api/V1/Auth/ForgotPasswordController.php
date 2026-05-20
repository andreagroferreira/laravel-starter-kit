<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Requests\Auth\Customer\ForgotPasswordRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;

final class ForgotPasswordController
{
    public function __invoke(ForgotPasswordRequest $request): JsonResponse
    {
        /** @var array{email: string} $credentials */
        $credentials = $request->validated();

        $status = Password::broker('customers')->sendResetLink($credentials);

        return response()->json(['status' => __($status)]);
    }
}
