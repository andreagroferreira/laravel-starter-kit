<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StorePageRequest;
use App\Http\Requests\UpdatePageRequest;
use App\Models\Page;
use App\Models\Site;
use App\Services\PageBlockSynchronizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

final class PageController
{
    public function show(Site $site, Page $page): Response
    {
        abort_unless($page->site_id === $site->id, 404);

        return Inertia::render('Pages/Edit', [
            'site' => $site->only('id', 'name', 'slug', 'settings'),
            'page' => $page->only('id', 'title', 'slug', 'status', 'seo', 'published_at'),
            'blocks' => $page->blocks()->orderBy('sort_order')->get(['id', 'type', 'content', 'sort_order']),
            'forms' => $site->forms()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StorePageRequest $request, Site $site): RedirectResponse
    {

        $site->pages()->create($request->validated());

        return back();
    }

    public function update(UpdatePageRequest $request, Site $site, Page $page): RedirectResponse
    {
        abort_unless($page->site_id === $site->id, 404);

        /** @var array{title?: string, slug?: string, seo?: array<string, string>|null, blocks?: list<array{type: string, content?: array<string, mixed>|null}>|null} $validated */
        $validated = $request->validated();

        DB::transaction(function () use ($page, $validated): void {
            $page->update(collect($validated)->except('blocks')->all());

            if (array_key_exists('blocks', $validated)) {
                resolve(PageBlockSynchronizer::class)->sync($page, $validated['blocks'] ?? []);
            }
        });

        return back();
    }

    public function destroy(Site $site, Page $page): RedirectResponse
    {
        abort_unless($page->site_id === $site->id, 404);

        Gate::authorize('delete', $page);

        $page->delete();

        return to_route('sites.show', $site);
    }
}
