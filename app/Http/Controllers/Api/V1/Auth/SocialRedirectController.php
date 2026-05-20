<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;

final class SocialRedirectController
{
    public function __invoke(string $provider): JsonResponse
    {
        Validator::make(['provider' => $provider], [
            'provider' => ['required', 'in:google,apple,github,microsoft,facebook'],
        ])->validate();

        /** @var Provider $driver */
        $driver = Socialite::driver($provider);

        if ($driver instanceof AbstractProvider) {
            $driver = $driver->stateless();
        }

        $redirect = $driver->redirect();

        $url = method_exists($redirect, 'getTargetUrl')
            ? $redirect->getTargetUrl()
            : (string) $redirect->headers->get('Location');

        return response()->json(['redirect_url' => $url]);
    }
}
