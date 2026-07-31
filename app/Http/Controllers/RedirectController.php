<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreRedirectRequest;
use App\Models\Redirect;
use App\Models\Site;
use Illuminate\Http\RedirectResponse as HttpRedirect;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

final class RedirectController
{
    public function index(Site $site): Response
    {
        return Inertia::render('Redirects/Index', [
            'site' => $site->only('id', 'name', 'slug'),
            'redirects' => $site->redirects()->latest()->get(['id', 'from_path', 'to_path', 'status_code']),
        ]);
    }

    public function store(StoreRedirectRequest $request, Site $site): HttpRedirect
    {
        Gate::authorize('create', Redirect::class);

        /** @var array{from_path: string, to_path: string, status_code: string} $validated */
        $validated = $request->validate([
            'from_path' => ['required', 'string', 'starts_with:/', 'max:500'],
            'to_path' => ['required', 'string', 'max:500'],
            'status_code' => ['required', 'in:301,302,307,308'],
        ]);

        $site->redirects()->create($validated);

        return back();
    }

    public function destroy(Site $site, Redirect $redirect): HttpRedirect
    {
        Gate::authorize('delete', $redirect);

        abort_unless($redirect->site_id === $site->id, 404);

        $redirect->delete();

        return back();
    }
}
