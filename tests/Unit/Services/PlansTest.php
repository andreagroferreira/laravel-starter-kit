<?php

declare(strict_types=1);

use App\Services\Plans;

it('returns plan definitions', function (): void {
    $plan = Plans::get('free');

    expect($plan['name'])->toBe('Free')
        ->and($plan['ai_credits_monthly'])->toBe(100);
});

it('lists plan keys', function (): void {
    expect(Plans::keys())->toBe(['free', 'pro', 'business']);
});

it('throws on unknown plans', function (): void {
    Plans::get('enterprise');
})->throws(InvalidArgumentException::class, 'Unknown plan [enterprise].');

it('resolves price ids and ai credits', function (): void {
    expect(Plans::priceId('free'))->toBeNull()
        ->and(Plans::aiCredits('pro'))->toBe(2_000);
});
