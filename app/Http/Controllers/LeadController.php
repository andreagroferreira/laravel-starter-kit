<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\FormSubmission;
use App\Models\Site;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

final class LeadController
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', FormSubmission::class);

        $siteId = (string) $request->query('site', '');
        $status = (string) $request->query('status', '');

        return Inertia::render('Leads/Index', [
            'filters' => ['site' => $siteId, 'status' => $status],
            'sites' => Site::query()->orderBy('name')->get(['id', 'name']),
            'leads' => FormSubmission::query()
                ->with(['site:id,name', 'form:id,name'])
                ->when($siteId !== '', fn (Builder $query) => $query->where('site_id', $siteId))
                ->when($status !== '', fn (Builder $query) => $query->where('status', $status))
                ->latest()
                ->paginate(20)
                ->withQueryString()
                ->through(fn (FormSubmission $lead): array => [
                    'id' => $lead->id,
                    'site' => $lead->site->name,
                    'form' => $lead->form->name,
                    'data' => $lead->data,
                    'status' => $lead->status,
                    'created_at' => $lead->created_at->toIso8601String(),
                ]),
        ]);
    }

    public function update(Request $request, FormSubmission $lead): RedirectResponse
    {
        Gate::authorize('update', $lead);

        /** @var array{status: string} $validated */
        $validated = $request->validate([
            'status' => ['required', 'in:new,read,spam'],
        ]);

        $lead->update($validated);

        return back();
    }

    public function destroy(FormSubmission $lead): RedirectResponse
    {
        Gate::authorize('delete', $lead);

        $lead->delete();

        return back()->with('success', 'Lead apagado.');
    }
}
