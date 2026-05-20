<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\VerifyCustomerEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class EmailVerificationController
{
    public function __invoke(Request $request, int $id, string $hash, VerifyCustomerEmail $verify): JsonResponse
    {
        $status = $verify($id, $hash);

        return match ($status) {
            'verified' => response()->json(['status' => 'verified']),
            'already_verified' => response()->json(['status' => 'already_verified']),
            default => response()->json(['status' => 'invalid_link'], 403),
        };
    }
}
