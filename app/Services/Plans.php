<?php

declare(strict_types=1);

namespace App\Services;

use InvalidArgumentException;

final readonly class Plans
{
    /**
     * @return array{name: string, stripe_price_id: string|null, ai_credits_monthly: int, sites: int, metered_overage_price_id?: string|null}
     */
    public static function get(string $plan): array
    {
        /** @var array<string, array{name: string, stripe_price_id: string|null, ai_credits_monthly: int, sites: int, metered_overage_price_id?: string|null}> $plans */
        $plans = config('plans.plans', []);

        throw_unless(isset($plans[$plan]), InvalidArgumentException::class, sprintf('Unknown plan [%s].', $plan));

        return $plans[$plan];
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_keys((array) config('plans.plans', []));
    }

    public static function priceId(string $plan): ?string
    {
        return self::get($plan)['stripe_price_id'];
    }

    public static function aiCredits(string $plan): int
    {
        return self::get($plan)['ai_credits_monthly'];
    }
}
