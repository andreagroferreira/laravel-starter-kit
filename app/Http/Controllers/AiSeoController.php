<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\AiGenerationStatus;
use App\Http\Requests\AiSeoRequest;
use App\Jobs\Ai\GenerateSeo;
use App\Models\AiGeneration;
use App\Models\Site;
use App\Models\Tenant;
use App\Support\CurrentTenant;
use Illuminate\Http\JsonResponse;

final class AiSeoController
{
    public function __invoke(AiSeoRequest $request, Site $site): JsonResponse
    {
        /** @var Tenant $tenant */
        $tenant = resolve(CurrentTenant::class)->getOrFail();

        /** @var AiGeneration $generation */
        $generation = AiGeneration::query()->create([
            'user_id' => $request->user()?->getKey(),
            'site_id' => $site->id,
            'agent' => 'seo',
            'status' => AiGenerationStatus::Queued,
            'input' => $request->validated(),
        ]);

        dispatch(new GenerateSeo($generation->id, $tenant->id));

        return response()->json(['generation_id' => $generation->id], 202);
    }
}
