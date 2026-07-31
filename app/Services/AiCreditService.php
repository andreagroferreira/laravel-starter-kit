<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AiUsage;
use App\Models\Tenant;
use App\Models\User;
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
     * Record a generation against the tenant.
     *
     * @param  array{type: string, id: string}|null  $resource
     */
    public function record(
        Tenant $tenant,
        string $agent,
        Usage $usage,
        ?User $user = null,
        ?array $resource = null,
        ?string $provider = null,
        ?string $model = null,
    ): AiUsage {
        return $tenant->aiUsages()->create([
            'user_id' => $user?->getKey(),
            'agent' => $agent,
            'provider' => $provider,
            'model' => $model,
            'prompt_tokens' => $usage->promptTokens,
            'completion_tokens' => $usage->completionTokens,
            'credits' => 1,
            'resource_type' => $resource['type'] ?? null,
            'resource_id' => $resource['id'] ?? null,
        ]);
    }
}
