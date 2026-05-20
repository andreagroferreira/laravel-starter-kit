<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Actions\Auth\AuthenticateCustomerSocial;
use App\Http\Resources\Customer\CustomerTokenResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;

final class SocialCallbackController
{
    public function __invoke(string $provider, AuthenticateCustomerSocial $authenticate): JsonResponse
    {
        Validator::make(['provider' => $provider], [
            'provider' => 'required|in:google,apple,github,microsoft,facebook',
        ])->validate();

        /** @var Provider $driver */
        $driver = Socialite::driver($provider);

        if ($driver instanceof AbstractProvider) {
            $driver = $driver->stateless();
        }

        $socialUser = $driver->user();
        $result = $authenticate($provider, $socialUser);

        return CustomerTokenResource::make((object) ['customer' => $result['customer'], 'token' => $result['token']])
            ->response()
            ->setStatusCode($result['created'] ? 201 : 200);
    }
}
