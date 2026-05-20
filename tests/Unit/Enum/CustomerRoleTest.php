<?php

declare(strict_types=1);

use App\Enums\CustomerRole;

it('has 3 cases', function (): void {
    expect(CustomerRole::cases())->toHaveCount(3);
});

it('labels each case', function (): void {
    expect(CustomerRole::Free->label())->toBe('Free')
        ->and(CustomerRole::Pro->label())->toBe('Pro')
        ->and(CustomerRole::Enterprise->label())->toBe('Enterprise');
});

it('returns all values via values()', function (): void {
    expect(CustomerRole::values())->toBe(['free', 'pro', 'enterprise']);
});
