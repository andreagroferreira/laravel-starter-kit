<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ContentStatus;
use App\Models\Site;
use App\Models\Tenant;
use App\Services\AiCreditService;
use App\Support\CurrentTenant;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use Inertia\Response;

final class DashboardController
{
    public function index(AiCreditService $credits): Response
    {
        /** @var Tenant $tenant */
        $tenant = resolve(CurrentTenant::class)->getOrFail();

        $sites = Site::query()
            ->withCount([
                'pages',
                'posts',
                'pages as published_pages_count' => fn (Builder $query) => $query->where('status', ContentStatus::Published),
                'posts as live_posts_count' => fn (Builder $query) => $query->where('status', ContentStatus::Published),
            ])
            ->latest()
            ->limit(6)
            ->get(['id', 'name', 'slug', 'type', 'status']);

        return Inertia::render('Dashboard/Index', [
            'stats' => [
                'sites' => Site::query()->count(),
                'pages' => $sites->sum('pages_count'),
                'posts' => $sites->sum('posts_count'),
                'ai_credits_used' => $credits->usedThisMonth($tenant),
                'ai_credits_monthly' => $tenant->ai_credits_monthly,
            ],
            'sites' => $sites,
        ]);
    }
}
