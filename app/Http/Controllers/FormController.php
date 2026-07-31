<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

final class FormController
{
    public function index(Site $site): Response
    {
        return Inertia::render('Forms/Index', [
            'site' => $site->only('id', 'name', 'slug'),
            'forms' => $site->forms()->get(['id', 'name', 'fields']),
        ]);
    }

    public function store(Request $request, Site $site): RedirectResponse
    {
        Gate::authorize('create', Form::class);

        /** @var array{name: string, fields: list<array{name: string, type: string, required?: bool}>} $validated */
        $validated = $request->validate([
            'name' => ['required', 'string', 'alpha_dash', 'max:60'],
            'fields' => ['required', 'array', 'min:1'],
            'fields.*.name' => ['required', 'string', 'max:60'],
            'fields.*.type' => ['required', 'in:text,email,textarea,number,tel,url'],
            'fields.*.required' => ['boolean'],
        ]);

        $site->forms()->create($validated);

        return back();
    }

    public function destroy(Site $site, Form $form): RedirectResponse
    {
        Gate::authorize('delete', $form);

        abort_unless($form->site_id === $site->id, 404);

        $form->delete();

        return back();
    }
}
