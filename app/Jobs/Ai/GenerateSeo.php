<?php

declare(strict_types=1);

namespace App\Jobs\Ai;

use App\Ai\Agents\SeoAgent;
use App\Ai\AiGenerationResult;
use App\Models\AiGeneration;
use App\Models\Tenant;
use Laravel\Ai\Responses\StructuredAgentResponse;
use RuntimeException;

final class GenerateSeo extends AiGenerationJob
{
    public function agentName(): string
    {
        return 'seo';
    }

    public function run(AiGeneration $generation, Tenant $tenant): AiGenerationResult
    {
        $briefing = is_string($generation->input['briefing'] ?? null) ? $generation->input['briefing'] : '';

        $response = (new SeoAgent)->prompt("Content:\n".$briefing);

        throw_unless($response instanceof StructuredAgentResponse, RuntimeException::class, 'The SEO agent returned an unstructured response.');

        /** @var array<string, mixed> $output */
        $output = $response->toArray();

        return new AiGenerationResult(
            output: $output,
            usage: $response->usage,
            provider: $response->meta->provider,
            model: $response->meta->model,
        );
    }
}
