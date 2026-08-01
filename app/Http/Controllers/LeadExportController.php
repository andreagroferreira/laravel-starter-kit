<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\FormSubmission;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;
use SplFileObject;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class LeadExportController
{
    public function __invoke(): StreamedResponse
    {
        Gate::authorize('export', FormSubmission::class);

        return response()->streamDownload(function (): void {
            // SplFileObject throws instead of returning false, so there is
            // no unreachable failure branch to carry around.
            $handle = new SplFileObject('php://output', 'w');

            $handle->fputcsv(['id', 'site', 'form', 'status', 'data', 'created_at'], escape: '\\');

            FormSubmission::query()
                ->with(['site:id,name', 'form:id,name'])
                ->latest()
                ->chunk(200, function (Collection $leads) use ($handle): void {
                    /** @var FormSubmission $lead */
                    foreach ($leads as $lead) {
                        $handle->fputcsv([
                            $lead->id,
                            $lead->site->name,
                            $lead->form->name,
                            $lead->status,
                            (string) json_encode($lead->data),
                            $lead->created_at->toIso8601String(),
                        ], escape: '\\');
                    }
                });
        }, 'leads.csv', ['Content-Type' => 'text/csv']);
    }
}
