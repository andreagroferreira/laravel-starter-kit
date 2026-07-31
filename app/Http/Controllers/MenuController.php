<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function store(Request $request, Site $site): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'alpha_dash', 'max:60'],
        ]);

        $site->menus()->create(['name' => $request->string('name')->value(), 'items' => []]);

        return back();
    }

    public function update(Request $request, Site $site, Menu $menu): RedirectResponse
    {
        abort_unless($menu->site_id === $site->id, 404);

        /** @var array{items: list<array{label: string, url: string}>} $validated */
        $validated = $request->validate([
            'items' => ['present', 'array'],
            'items.*.label' => ['required', 'string', 'max:100'],
            'items.*.url' => ['required', 'string', 'max:500'],
        ]);

        $menu->update($validated);

        return back();
    }

    public function destroy(Site $site, Menu $menu): RedirectResponse
    {
        abort_unless($menu->site_id === $site->id, 404);

        $menu->delete();

        return back();
    }
}
