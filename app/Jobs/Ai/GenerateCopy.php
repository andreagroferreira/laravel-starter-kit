<?php

declare(strict_types=1);

namespace App\Jobs\Ai;

use App\Ai\Agents\CopywriterAgent;
use App\Ai\AiGenerationResult;
use App\Models\AiGeneration;
use App\Models\Tenant;

final class GenerateCopy extends AiGenerationJob
{
    public function agentName(): string
    {
        return 'copywriter';
    }

    public function run(AiGeneration $generation, Tenant $tenant): AiGenerationResult
    {
        $input = $generation->input;
        $blockType = is_string($input['block_type'] ?? null) ? $input['block_type'] : '';
        $briefing = is_string($input['briefing'] ?? null) ? $input['briefing'] : '';
        $currentContent = is_string($input['current_content'] ?? null) ? $input['current_content'] : null;

        $prompt = sprintf("Block type: %s\nBriefing: %s", $blockType, $briefing);

        if ($currentContent !== null) {
            $prompt .= "\nCurrent content to improve: ".$currentContent;
        }

        $response = new CopywriterAgent($tenant->brandProfile)->prompt($prompt);

        return new AiGenerationResult(
            output: ['copy' => $response->text],
            usage: $response->usage,
            provider: $response->meta->provider,
            model: $response->meta->model,
        );
    }
}
