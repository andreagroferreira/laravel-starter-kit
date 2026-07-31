<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Ai\Agents\SeoAgent;
use App\Http\Requests\AiCopyRequest;
use App\Models\Site;
use App\Models\Tenant;
use App\Services\AiCreditService;
use App\Support\CurrentTenant;
use Illuminate\Http\JsonResponse;
use Laravel\Ai\Responses\StructuredAgentResponse;

final class AiSeoController
{
    public function __invoke(AiCopyRequest $request, Site $site, AiCreditService $credits): JsonResponse
    {
        /** @var Tenant $tenant */
        $tenant = resolve(CurrentTenant::class)->getOrFail();

        /** @var array{briefing: string} $validated */
        $validated = $request->validated();

        $response = (new SeoAgent)->prompt('Content:
'.$validated['briefing']);

        $credits->record($tenant, 'seo', $response->usage, $request->user(), [
            'type' => 'site',
            'id' => $site->id,
        ], $response->meta->provider, $response->meta->model);

        return response()->json(
            $response instanceof StructuredAgentResponse ? $response->toArray() : []
        );
    }
}
