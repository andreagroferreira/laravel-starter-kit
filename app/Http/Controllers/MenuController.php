<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreMenuRequest;
use App\Http\Requests\UpdateMenuRequest;
use App\Models\Menu;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

final class MenuController
{
    public function index(Site $site): Response
    {
        return Inertia::render('Menus/Index', [
            'site' => $site->only('id', 'name', 'slug'),
            'menus' => $site->menus()->get(['id', 'name', 'items']),
        ]);
    }

    public function store(StoreMenuRequest $request, Site $site): RedirectResponse
    {
        $site->menus()->create(['name' => $request->string('name')->value(), 'items' => []]);

        return back();
    }

    public function update(UpdateMenuRequest $request, Site $site, Menu $menu): RedirectResponse
    {
        abort_unless($menu->site_id === $site->id, 404);

        /** @var array{items: list<array{label: string, url: string}>} $validated */
        /** @var array{items: list<array{label: string, url: string}>} $validated */
        $validated = $request->validated();

        $menu->update($validated);

        return back();
    }

    public function destroy(Site $site, Menu $menu): RedirectResponse
    {
        Gate::authorize('delete', $menu);

        abort_unless($menu->site_id === $site->id, 404);

        $menu->delete();

        return back();
    }
}
