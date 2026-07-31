<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\OutOfAiCredits;
use App\Models\AiUsage;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Responses\Data\Usage;

final class AiCreditService
{
    /**
     * Credits consumed by the tenant in the current billing month.
     */
    public function usedThisMonth(Tenant $tenant): int
    {
        return (int) $tenant->aiUsages()
            ->where('created_at', '>=', now()->startOfMonth())
            ->sum('credits');
    }

    public function hasCredits(Tenant $tenant, int $credits = 1): bool
    {
        return $this->usedThisMonth($tenant) + $credits <= $tenant->ai_credits_monthly;
    }

    /**
     * Credits owed for a generation, from real token usage.
     */
    public function creditsFor(Usage $usage): int
    {
        $tokensPerCredit = max(1, config()->integer('plans.tokens_per_credit', 1000));

        return max(1, (int) ceil(($usage->promptTokens + $usage->completionTokens) / $tokensPerCredit));
    }

    /**
     * Atomically reserve one credit before a generation runs. The row lock
     * on the tenant closes the check-then-write race between concurrent jobs.
     *
     * @param  array{type: string, id: string}|null  $resource
     *
     * @throws OutOfAiCredits
     */
    public function reserve(
        Tenant $tenant,
        string $agent,
        ?User $user = null,
        ?array $resource = null,
    ): AiUsage {
        return DB::transaction(function () use ($tenant, $agent, $user, $resource): AiUsage {
            $locked = Tenant::query()->lockForUpdate()->findOrFail($tenant->id);

            throw_unless($this->hasCredits($locked), OutOfAiCredits::make());

            return $locked->aiUsages()->create([
                'user_id' => $user?->getKey(),
                'agent' => $agent,
                'prompt_tokens' => 0,
                'completion_tokens' => 0,
                'credits' => 1,
                'resource_type' => $resource['type'] ?? null,
                'resource_id' => $resource['id'] ?? null,
            ]);
        });
    }

    /**
     * Settle a reservation with the real token usage after the generation.
     */
    public function settle(AiUsage $reservation, Usage $usage, ?string $provider = null, ?string $model = null): AiUsage
    {
        $reservation->update([
            'provider' => $provider,
            'model' => $model,
            'prompt_tokens' => $usage->promptTokens,
            'completion_tokens' => $usage->completionTokens,
            'credits' => $this->creditsFor($usage),
        ]);

        return $reservation;
    }

    /**
     * Release a reservation when the generation fails — the tenant is not
     * charged for output it never received.
     */
    public function refund(AiUsage $reservation): void
    {
        $reservation->delete();
    }
}
