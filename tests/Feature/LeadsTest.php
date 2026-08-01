<?php

declare(strict_types=1);

use App\Enums\TenantRole;
use App\Events\LeadCaptured;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\Site;
use App\Models\SiteVersion;
use App\Models\Tenant;
use App\Models\User;
use App\Notifications\LeadReceived;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create(['current_tenant_id' => $this->tenant->id]);
    $this->tenant->users()->attach($this->user);
    grantRole($this->tenant, $this->user);

    $this->site = Site::factory()->for($this->tenant)->create();
    SiteVersion::factory()->for($this->site)->published()->create();
    $this->form = Form::factory()->for($this->site)->create([
        'fields' => [
            ['name' => 'email', 'type' => 'email', 'required' => true],
            ['name' => 'mensagem', 'type' => 'textarea', 'required' => false],
        ],
    ]);
});

it('captures a public submission, broadcasts and notifies the owners', function (): void {
    Event::fake([LeadCaptured::class]);
    Notification::fake();

    $this->postJson(sprintf('/api/v1/public/sites/%s/forms/%s/submissions', $this->site->id, $this->form->id), [
        'email' => 'lead@exemplo.pt',
        'mensagem' => 'Quero saber mais.',
    ])->assertCreated();

    $lead = FormSubmission::query()->withoutGlobalScope('tenant')->sole();

    expect($lead->tenant_id)->toBe($this->tenant->id)
        ->and($lead->status)->toBe('new')
        ->and($lead->data)->toMatchArray(['email' => 'lead@exemplo.pt']);

    Event::assertDispatched(LeadCaptured::class);
    Notification::assertSentTo($this->user, LeadReceived::class);
});

it('derives validation rules from every field type', function (): void {
    $form = Form::factory()->for($this->site)->create([
        'name' => 'tipos',
        'fields' => [
            ['name' => 'idade', 'type' => 'number', 'required' => true],
            ['name' => 'website', 'type' => 'url', 'required' => false],
            ['name' => 'nota', 'type' => 'textarea', 'required' => false],
            // Campo malformado (sem nome) é ignorado em vez de rebentar.
            ['type' => 'text', 'required' => false],
        ],
    ]);

    $url = sprintf('/api/v1/public/sites/%s/forms/%s/submissions', $this->site->id, $form->id);

    $this->postJson($url, ['idade' => 'não é número', 'website' => 'não é url'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['idade', 'website']);

    $this->postJson($url, ['idade' => 42, 'website' => 'https://exemplo.pt', 'nota' => 'olá'])
        ->assertCreated();
});

it('validates the payload against the form definition', function (): void {
    $this->postJson(sprintf('/api/v1/public/sites/%s/forms/%s/submissions', $this->site->id, $this->form->id), [
        'email' => 'não-é-um-email',
    ])->assertUnprocessable()->assertJsonValidationErrors(['email']);

    expect(FormSubmission::query()->withoutGlobalScope('tenant')->count())->toBe(0);
});

it('accepts honeypot submissions silently as spam without notifying', function (): void {
    Event::fake([LeadCaptured::class]);
    Notification::fake();

    $this->postJson(sprintf('/api/v1/public/sites/%s/forms/%s/submissions', $this->site->id, $this->form->id), [
        'email' => 'bot@exemplo.pt',
        '_website' => 'http://spam.example',
    ])->assertCreated();

    expect(FormSubmission::query()->withoutGlobalScope('tenant')->sole()->status)->toBe('spam');

    Event::assertNotDispatched(LeadCaptured::class);
    Notification::assertNothingSent();
});

it('rejects submissions to unpublished sites and mismatched forms', function (): void {
    $draftSite = Site::factory()->for($this->tenant)->create();
    $draftForm = Form::factory()->for($draftSite)->create();

    $this->postJson(sprintf('/api/v1/public/sites/%s/forms/%s/submissions', $draftSite->id, $draftForm->id), [
        'email' => 'x@exemplo.pt',
    ])->assertNotFound();

    $this->postJson(sprintf('/api/v1/public/sites/%s/forms/%s/submissions', $this->site->id, $draftForm->id), [
        'email' => 'x@exemplo.pt',
    ])->assertNotFound();
});

it('rate limits public submissions per ip', function (): void {
    foreach (range(1, 10) as $i) {
        $this->postJson(sprintf('/api/v1/public/sites/%s/forms/%s/submissions', $this->site->id, $this->form->id), [
            'email' => sprintf('lead%d@exemplo.pt', $i),
        ])->assertCreated();
    }

    $this->postJson(sprintf('/api/v1/public/sites/%s/forms/%s/submissions', $this->site->id, $this->form->id), [
        'email' => 'lead11@exemplo.pt',
    ])->assertTooManyRequests();
});

it('lists leads in the backoffice with filters and pagination', function (): void {
    FormSubmission::factory()->for($this->tenant)->count(3)->create([
        'site_id' => $this->site->id,
        'form_id' => $this->form->id,
    ]);

    $otherSite = Site::factory()->for($this->tenant)->create();
    $otherForm = Form::factory()->for($otherSite)->create();
    FormSubmission::factory()->for($this->tenant)->create([
        'site_id' => $otherSite->id,
        'form_id' => $otherForm->id,
        'status' => 'read',
    ]);

    $this->actingAs($this->user)
        ->get('/leads')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Leads/Index')
            ->has('leads.data', 4)
        );

    $this->actingAs($this->user)
        ->get('/leads?site='.$this->site->id.'&status=new')
        ->assertInertia(fn (Assert $page): Assert => $page->has('leads.data', 3));
});

it('updates status, deletes and exports leads', function (): void {
    $lead = FormSubmission::factory()->for($this->tenant)->create([
        'site_id' => $this->site->id,
        'form_id' => $this->form->id,
    ]);

    $this->actingAs($this->user)
        ->put('/leads/'.$lead->id, ['status' => 'read'])
        ->assertRedirect();

    expect($lead->refresh()->status)->toBe('read');

    $export = $this->actingAs($this->user)->get('/leads/export');
    $export->assertOk()->assertDownload('leads.csv');
    expect($export->streamedContent())->toContain($lead->id)->toContain($this->site->name);

    $this->actingAs($this->user)->delete('/leads/'.$lead->id)->assertRedirect();
    expect(FormSubmission::query()->count())->toBe(0);
});

it('enforces leads permissions by role', function (): void {
    $journalist = User::factory()->create(['current_tenant_id' => $this->tenant->id]);
    $this->tenant->users()->attach($journalist);
    grantRole($this->tenant, $journalist, TenantRole::Journalist);

    $this->actingAs($journalist)->get('/leads')->assertForbidden();
    $this->actingAs($journalist)->get('/leads/export')->assertForbidden();
});

it('does not leak leads across tenants', function (): void {
    $foreign = FormSubmission::factory()->create();

    $this->actingAs($this->user)
        ->put('/leads/'.$foreign->id, ['status' => 'read'])
        ->assertNotFound();
});
