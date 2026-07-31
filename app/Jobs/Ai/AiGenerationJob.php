<?php

declare(strict_types=1);

namespace App\Jobs\Ai;

use App\Ai\AiGenerationResult;
use App\Enums\AiGenerationStatus;
use App\Events\AiGenerationUpdated;
use App\Exceptions\OutOfAiCredits;
use App\Models\AiGeneration;
use App\Models\Tenant;
use App\Queue\Middleware\TenantAware;
use App\Services\AiCreditService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Template for every AI generation: reserve a credit, run the agent,
 * settle with real token usage, broadcast each transition. Failures mark
 * the generation as failed (refunding the credit) instead of crashing.
 */
abstract class AiGenerationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        protected string $generationId,
        protected string $tenantId,
    ) {
        $this->onQueue('ai');
    }

    abstract public function agentName(): string;

    abstract public function run(AiGeneration $generation, Tenant $tenant): AiGenerationResult;

    /**
     * @return list<object>
     */
    final public function middleware(): array
    {
        return [new TenantAware($this->tenantId)];
    }

    final public function handle(AiCreditService $credits): void
    {
        $generation = AiGeneration::query()->findOrFail($this->generationId);
        $tenant = Tenant::query()->findOrFail($this->tenantId);

        $generation->update(['status' => AiGenerationStatus::Processing]);
        broadcast(new AiGenerationUpdated($generation));

        try {
            $reservation = $credits->reserve($tenant, $this->agentName(), $generation->user, [
                'type' => 'generation',
                'id' => $generation->id,
            ]);
        } catch (OutOfAiCredits $outOfAiCredits) {
            $generation->update([
                'status' => AiGenerationStatus::Failed,
                'error' => $outOfAiCredits->getMessage(),
            ]);
            broadcast(new AiGenerationUpdated($generation));

            return;
        }

        try {
            $result = $this->run($generation, $tenant);

            $credits->settle($reservation, $result->usage, $result->provider, $result->model);

            $generation->update([
                'status' => AiGenerationStatus::Completed,
                'output' => $result->output,
            ]);
        } catch (Throwable $throwable) {
            $credits->refund($reservation);

            $generation->update([
                'status' => AiGenerationStatus::Failed,
                'error' => $throwable->getMessage(),
            ]);
        }

        broadcast(new AiGenerationUpdated($generation));
    }
}
