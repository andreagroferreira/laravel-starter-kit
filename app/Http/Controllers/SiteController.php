<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreSiteRequest;
use App\Models\Deployment;
use App\Models\Site;
use App\Services\SiteService;
use App\Support\SiteUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

final readonly class SiteController
{
    public function __construct(
        private SiteService $sites,
    ) {}

    public function index(): Response
    {
        return Inertia::render('Sites/Index', [
            'sites' => Site::query()
                ->withCount('pages')
                ->latest()
                ->get(['id', 'name', 'slug', 'type', 'status', 'domain', 'created_at']),
        ]);
    }

    public function store(StoreSiteRequest $request): RedirectResponse
    {
        /** @var array{name: string, slug: string, type: string, domain?: string|null} $validated */
        $validated = $request->validated();

        $site = $this->sites->createSite($validated);

        return to_route('sites.show', $site);
    }

    public function show(Site $site): Response
    {

        return Inertia::render('Sites/Show', [
            'site' => $site->only('id', 'name', 'slug', 'type', 'status', 'domain', 'renderer_version'),
            'pages' => $site->pages()
                ->withCount('blocks')
                ->orderBy('sort_order')
                ->get(['id', 'site_id', 'title', 'slug', 'status', 'sort_order', 'published_at']),
            'versions' => $site->versions()
                ->latest('published_at')
                ->limit(10)
                ->get(['id', 'site_id', 'origin', 'published_at', 'created_at']),
            'publicUrl' => SiteUrl::for($site),
            'deployments' => $site->deployments()
                ->latest()
                ->limit(5)
                ->get(['id', 'site_id', 'status', 'url', 'error', 'created_at'])
                ->map(fn (Deployment $deployment): array => [
                    'id' => $deployment->id,
                    'status' => $deployment->status->value,
                    'url' => $deployment->url,
                    'error' => $deployment->error,
                    'created_at' => $deployment->created_at->toIso8601String(),
                ]),
        ]);
    }

    public function destroy(Site $site): RedirectResponse
    {
        Gate::authorize('delete', $site);

        $site->delete();

        return to_route('sites.index');
    }
}
