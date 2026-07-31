<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreMediaRequest;
use App\Http\Requests\UpdateMediaRequest;
use App\Models\MediaAsset;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

final class MediaController
{
    public function index(Request $request): Response
    {
        $search = (string) $request->query('search', '');

        return Inertia::render('Media/Index', [
            'filters' => ['search' => $search],
            'assets' => MediaAsset::query()
                ->when($search !== '', fn (Builder $query) => $query->whereLike('filename', '%'.$search.'%'))
                ->latest()
                ->paginate(24)
                ->withQueryString()
                ->through(fn (MediaAsset $asset): array => [
                    'id' => $asset->id,
                    'url' => $asset->url(),
                    'filename' => $asset->filename,
                    'alt' => $asset->alt,
                    'size' => $asset->size,
                    'mime_type' => $asset->mime_type,
                ]),
        ]);
    }

    public function store(StoreMediaRequest $request): RedirectResponse
    {
        Gate::authorize('create', MediaAsset::class);

        /** @var array{file: UploadedFile, alt?: string|null} $validated */
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,gif,svg,pdf', 'max:10240'],
            'alt' => ['nullable', 'string', 'max:255'],
        ]);

        $file = $validated['file'];

        /** @var string $disk */
        $disk = config('filesystems.media_disk', 'public');
        $filename = Str::lower(Str::random(16)).'.'.$file->getClientOriginalExtension();
        $path = $file->storeAs('media', $filename, $disk);

        MediaAsset::query()->create([
            'uploaded_by' => $request->user()?->getKey(),
            'disk' => $disk,
            'path' => $path,
            'filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize() ?: 0,
            'alt' => $validated['alt'] ?? null,
        ]);

        return back();
    }

    public function update(UpdateMediaRequest $request, MediaAsset $media): RedirectResponse
    {
        Gate::authorize('update', $media);

        /** @var array{alt?: string|null} $validated */
        $validated = $request->validate([
            'alt' => ['nullable', 'string', 'max:255'],
        ]);

        $media->update($validated);

        return back();
    }

    public function destroy(MediaAsset $media): RedirectResponse
    {
        Gate::authorize('delete', $media);

        Storage::disk($media->disk)->delete($media->path);
        $media->delete();

        return back();
    }
}
