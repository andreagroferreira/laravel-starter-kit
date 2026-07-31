<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class AuditLogController
{
    public function __invoke(Request $request): Response
    {
        $actorType = $request->query('actor');

        $logs = AuditLog::query()
            ->with('user:id,name,email')
            ->when(
                in_array($actorType, ['human', 'agent'], true),
                fn (Builder $query) => $query->where('actor_type', $actorType)
            )
            ->latest('created_at')
            ->paginate(30)
            ->through(fn (AuditLog $log): array => [
                'id' => $log->id,
                'actor_type' => $log->actor_type,
                'action' => $log->action,
                'user' => $log->user?->only('name', 'email'),
                'subject_type' => $log->subject_type,
                'subject_id' => $log->subject_id,
                'payload' => $log->payload,
                'created_at' => $log->created_at->diffForHumans(),
            ])
            ->withQueryString();

        return Inertia::render('Settings/Audit', [
            'logs' => $logs,
            'filters' => ['actor' => $actorType],
        ]);
    }
}
