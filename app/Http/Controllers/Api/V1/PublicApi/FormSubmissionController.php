<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\PublicApi;

use App\Enums\TenantRole;
use App\Events\LeadCaptured;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\Site;
use App\Models\User;
use App\Notifications\LeadReceived;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Public lead capture endpoint used by the rendered sites. Draft sites
 * never receive submissions; spam is accepted silently (201) so bots
 * learn nothing, but flagged and never notified.
 */
final class FormSubmissionController
{
    public function __invoke(Request $request, Site $site, Form $form): JsonResponse
    {
        abort_unless($form->site_id === $site->id, 404);
        abort_if($site->publishedVersion === null, 404);

        /** @var array<string, mixed> $payload */
        $payload = Validator::make($request->all(), $this->rulesFor($form))->validate();

        $isSpam = $this->smellsLikeSpam($request);

        /** @var FormSubmission $submission */
        $submission = FormSubmission::query()->create([
            'tenant_id' => $site->tenant_id,
            'site_id' => $site->id,
            'form_id' => $form->id,
            'data' => collect($payload)->except('_website')->all(),
            'status' => $isSpam ? 'spam' : 'new',
            'ip_hash' => hash('sha256', (string) $request->ip()),
            'user_agent' => (string) $request->userAgent(),
            'referrer' => $request->headers->get('referer'),
        ]);

        if (! $isSpam) {
            broadcast(new LeadCaptured($submission));

            setPermissionsTeamId($site->tenant_id);

            User::query()
                ->role(TenantRole::Owner)
                ->whereHas('tenants', fn (Builder $query) => $query->whereKey($site->tenant_id))
                ->get()
                ->each(fn (User $owner) => $owner->notify(new LeadReceived($submission)));
        }

        return response()->json(['id' => $submission->id], 201);
    }

    /**
     * @return array<string, list<string>>
     */
    private function rulesFor(Form $form): array
    {
        $rules = ['_website' => ['nullable', 'string']];

        foreach ($form->fields as $field) {
            $name = is_string($field['name'] ?? null) ? $field['name'] : null;

            if ($name === null) {
                continue;
            }

            $fieldRules = [($field['required'] ?? false) === true ? 'required' : 'nullable'];

            $fieldRules[] = match ($field['type'] ?? 'text') {
                'email' => 'email',
                'number' => 'numeric',
                'url' => 'url',
                default => 'string',
            };

            if (($field['type'] ?? 'text') !== 'number') {
                $fieldRules[] = 'max:5000';
            }

            $rules[$name] = $fieldRules;
        }

        return $rules;
    }

    private function smellsLikeSpam(Request $request): bool
    {
        // Honeypot: real visitors never fill the invisible _website field.
        return $request->filled('_website');
    }
}
