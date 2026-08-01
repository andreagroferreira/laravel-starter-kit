<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AiGeneration;
use App\Models\Site;
use App\Models\Tenant;
use App\Services\AiCreditService;
use App\Support\CurrentTenant;
use Inertia\Inertia;
use Inertia\Response;

final class AiCopilotController
{
    public function __invoke(AiCreditService $credits): Response
    {
        /** @var Tenant $tenant */
        $tenant = resolve(CurrentTenant::class)->getOrFail();

        return Inertia::render('Ai/Index', [
            'sites' => Site::query()->orderBy('name')->get(['id', 'name']),
            'credits' => [
                'used' => $credits->usedThisMonth($tenant),
                'monthly' => $tenant->ai_credits_monthly,
            ],
            'generations' => AiGeneration::query()
                ->with('site:id,name')
                ->latest()
                ->limit(20)
                ->get()
                ->map(fn (AiGeneration $generation): array => [
                    'id' => $generation->id,
                    'agent' => $generation->agent,
                    'status' => $generation->status->value,
                    'site' => $generation->site?->name,
                    'output' => $generation->output,
                    'error' => $generation->error,
                    'created_at' => $generation->created_at->toIso8601String(),
                ]),
        ]);
    }
}
