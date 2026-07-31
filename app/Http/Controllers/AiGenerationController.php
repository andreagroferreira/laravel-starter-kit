<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AiGeneration;
use Illuminate\Http\JsonResponse;

final class AiGenerationController
{
    /**
     * Polling fallback for clients without a websocket connection.
     */
    public function __invoke(AiGeneration $generation): JsonResponse
    {
        return response()->json([
            'id' => $generation->id,
            'agent' => $generation->agent,
            'status' => $generation->status->value,
            'final' => $generation->status->isFinal(),
            'output' => $generation->output,
            'error' => $generation->error,
        ]);
    }
}
