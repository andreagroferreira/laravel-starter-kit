<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Ai\Agents\CopywriterAgent;
use App\Http\Requests\AiCopyRequest;
use App\Models\Site;
use App\Models\Tenant;
use App\Services\AiCreditService;
use App\Support\CurrentTenant;
use Illuminate\Http\JsonResponse;

final class AiCopyController
{
    public function __invoke(AiCopyRequest $request, Site $site, AiCreditService $credits): JsonResponse
    {
        /** @var Tenant $tenant */
        $tenant = resolve(CurrentTenant::class)->getOrFail();

        /** @var array{block_type: string, briefing: string, current_content?: string} $validated */
        $validated = $request->validated();

        $prompt = "Block type: {$validated['block_type']}\nBriefing: {$validated['briefing']}";

        if (isset($validated['current_content'])) {
            $prompt .= '
Current content to improve: '.$validated['current_content'];
        }

        $response = new CopywriterAgent($tenant->brandProfile)->prompt($prompt);

        $credits->record($tenant, 'copywriter', $response->usage, $request->user(), [
            'type' => 'site',
            'id' => $site->id,
        ], $response->meta->provider, $response->meta->model);

        return response()->json(['copy' => $response->text]);
    }
}
