<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\RegisterCustomer;
use App\Http\Requests\Auth\Customer\RegisterRequest;
use App\Http\Resources\Customer\CustomerTokenResource;
use Illuminate\Http\JsonResponse;

final class RegisterController
{
    public function __invoke(RegisterRequest $request, RegisterCustomer $register): JsonResponse
    {
        /** @var array{name: string, email: string, password: string} $data */
        $data = $request->validated();

        $result = $register($data);

        return CustomerTokenResource::make((object) $result)
            ->response()
            ->setStatusCode(201);
    }
}
