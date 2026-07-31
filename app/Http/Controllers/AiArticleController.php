<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\AiGenerationStatus;
use App\Http\Requests\AiArticleRequest;
use App\Jobs\Ai\GenerateArticle;
use App\Models\AiGeneration;
use App\Models\Site;
use App\Models\Tenant;
use App\Support\CurrentTenant;
use Illuminate\Http\JsonResponse;

final class AiArticleController
{
    public function __invoke(AiArticleRequest $request, Site $site): JsonResponse
    {
        /** @var Tenant $tenant */
        $tenant = resolve(CurrentTenant::class)->getOrFail();

        /** @var AiGeneration $generation */
        $generation = AiGeneration::query()->create([
            'user_id' => $request->user()?->getKey(),
            'site_id' => $site->id,
            'agent' => 'article_writer',
            'status' => AiGenerationStatus::Queued,
            'input' => $request->validated(),
        ]);

        dispatch(new GenerateArticle($generation->id, $tenant->id));

        return response()->json(['generation_id' => $generation->id], 202);
    }
}
