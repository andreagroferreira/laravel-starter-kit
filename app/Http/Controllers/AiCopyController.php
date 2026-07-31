<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\AiGenerationStatus;
use App\Http\Requests\AiCopyRequest;
use App\Jobs\Ai\GenerateCopy;
use App\Models\AiGeneration;
use App\Models\Site;
use App\Models\Tenant;
use App\Support\CurrentTenant;
use Illuminate\Http\JsonResponse;

final class AiCopyController
{
    public function __invoke(AiCopyRequest $request, Site $site): JsonResponse
    {
        /** @var Tenant $tenant */
        $tenant = resolve(CurrentTenant::class)->getOrFail();

        /** @var AiGeneration $generation */
        $generation = AiGeneration::query()->create([
            'user_id' => $request->user()?->getKey(),
            'site_id' => $site->id,
            'agent' => 'copywriter',
            'status' => AiGenerationStatus::Queued,
            'input' => $request->validated(),
        ]);

        dispatch(new GenerateCopy($generation->id, $tenant->id));

        return response()->json(['generation_id' => $generation->id], 202);
    }
}
