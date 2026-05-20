<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\Customer;
use App\Models\CustomerSocialAccount;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Contracts\User as SocialiteUser;

final class AuthenticateCustomerSocial
{
    /**
     * Authenticate (or create) a customer from a Socialite OAuth user.
     *
     * Returns the customer, a freshly-issued Sanctum token, and a flag
     * indicating whether the customer was created during this call.
     *
     * @return array{customer: Customer, token: string, created: bool}
     */
    public function __invoke(string $provider, SocialiteUser $socialUser): array
    {
        return DB::transaction(function () use ($provider, $socialUser): array {
            $providerId = (string) $socialUser->getId();

            $account = CustomerSocialAccount::query()
                ->where('provider', $provider)
                ->where('provider_id', $providerId)
                ->first();

            if ($account instanceof CustomerSocialAccount) {
                /** @var Customer $customer */
                $customer = $account->customer;
                $created = false;
            } else {
                $email = (string) $socialUser->getEmail();
                $name = $socialUser->getName() ?? $socialUser->getNickname() ?? 'Customer';

                $customer = Customer::query()->firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => $name,
                        'password' => null,
                        'avatar_url' => $socialUser->getAvatar(),
                    ],
                );

                $created = $customer->wasRecentlyCreated;

                if ($created && $customer->email_verified_at === null) {
                    $customer->forceFill(['email_verified_at' => now()])->save();
                }

                $customer->socialAccounts()->create([
                    'provider' => $provider,
                    'provider_id' => $providerId,
                    'provider_token' => $socialUser->token ?? null,
                    'provider_refresh_token' => $socialUser->refreshToken ?? null,
                    'expires_at' => isset($socialUser->expiresIn) ? now()->addSeconds((int) $socialUser->expiresIn) : null,
                ]);
            }

            $customer->forceFill(['last_login_at' => now()])->save();

            $token = $customer->createToken("social-{$provider}", ['*'], now()->addDays(30))->plainTextToken;

            return ['customer' => $customer, 'token' => $token, 'created' => $created];
        });
    }
}
