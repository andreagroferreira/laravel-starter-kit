<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

final class PostController
{
    public function index(Site $site): Response
    {
        return Inertia::render('Posts/Index', [
            'site' => $site->only('id', 'name', 'slug'),
            'posts' => $site->posts()
                ->with('categories:id,name')
                ->latest()
                ->paginate(15),
            'categories' => $site->categories()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function show(Site $site, Post $post): Response
    {
        abort_unless($post->site_id === $site->id, 404);

        return Inertia::render('Posts/Edit', [
            'site' => $site->only('id', 'name', 'slug'),
            'post' => [
                ...$post->only('id', 'title', 'slug', 'excerpt', 'body', 'seo', 'published_at'),
                'status' => $post->status->value,
            ],
            'categories' => $site->categories()->orderBy('name')->get(['id', 'name']),
            'selectedCategories' => $post->categories()->pluck('categories.id'),
        ]);
    }

    public function store(StorePostRequest $request, Site $site): RedirectResponse
    {
        /** @var array{categories?: list<string>} $validated */
        $validated = $request->validated();

        $post = $site->posts()->create([
            ...collect($validated)->except('categories')->all(),
            'author_id' => $request->user()?->getKey(),
        ]);

        $post->categories()->sync($validated['categories'] ?? []);

        return back();
    }

    public function update(UpdatePostRequest $request, Site $site, Post $post): RedirectResponse
    {
        abort_unless($post->site_id === $site->id, 404);

        /** @var array{categories?: list<string>} $validated */
        $validated = $request->validated();

        $post->update(collect($validated)->except('categories')->all());

        $post->categories()->sync($validated['categories'] ?? []);

        return back();
    }

    public function destroy(Site $site, Post $post): RedirectResponse
    {
        abort_unless($post->site_id === $site->id, 404);

        Gate::authorize('delete', $post);

        $post->delete();

        return back();
    }
}
