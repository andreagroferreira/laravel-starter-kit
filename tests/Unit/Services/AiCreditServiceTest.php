<?php

declare(strict_types=1);

use App\Exceptions\OutOfAiCredits;
use App\Models\AiUsage;
use App\Models\Tenant;
use App\Services\AiCreditService;
use Laravel\Ai\Responses\Data\Usage;

beforeEach(function (): void {
    $this->service = new AiCreditService;
    $this->tenant = Tenant::factory()->create(['ai_credits_monthly' => 2]);
});

it('reserves a credit atomically and rejects when exhausted', function (): void {
    $first = $this->service->reserve($this->tenant, 'copywriter');
    $second = $this->service->reserve($this->tenant, 'copywriter');

    expect($first->credits)->toBe(1)
        ->and($second->credits)->toBe(1)
        ->and(fn (): AiUsage => $this->service->reserve($this->tenant, 'copywriter'))
        ->toThrow(OutOfAiCredits::class);
});

it('settles the reservation with credits derived from real tokens', function (): void {
    config()->set('plans.tokens_per_credit', 1000);

    $reservation = $this->service->reserve($this->tenant, 'article_writer', null, ['type' => 'generation', 'id' => 'x']);

    $this->service->settle($reservation, new Usage(promptTokens: 1200, completionTokens: 1300), 'openai', 'gpt-test');

    $reservation->refresh();

    expect($reservation->credits)->toBe(3)
        ->and($reservation->prompt_tokens)->toBe(1200)
        ->and($reservation->completion_tokens)->toBe(1300)
        ->and($reservation->provider)->toBe('openai')
        ->and($reservation->model)->toBe('gpt-test');
});

it('charges at least one credit for tiny generations', function (): void {
    expect($this->service->creditsFor(new Usage(promptTokens: 10, completionTokens: 5)))->toBe(1);
});

it('refunds a reservation on failure', function (): void {
    $reservation = $this->service->reserve($this->tenant, 'seo');

    $this->service->refund($reservation);

    expect(AiUsage::query()->count())->toBe(0)
        ->and($this->service->usedThisMonth($this->tenant))->toBe(0);
});
