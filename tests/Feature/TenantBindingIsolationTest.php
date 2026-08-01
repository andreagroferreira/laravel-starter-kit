<?php

declare(strict_types=1);

use App\Models\AiGeneration;
use App\Models\FormSubmission;
use App\Models\MediaAsset;
use App\Models\Tenant;
use App\Models\User;

beforeEach(function (): void {
    $this->tenant = Tenant::factory()->create();
    $this->user = User::factory()->create(['current_tenant_id' => $this->tenant->id]);
    $this->tenant->users()->attach($this->user);
    grantRole($this->tenant, $this->user);
});

/**
 * Regression: SetCurrentTenant must run BEFORE SubstituteBindings, or the
 * implicit route model binding resolves without the tenant global scope
 * and hands over another tenant's record on the very first request of a
 * worker (before any other request has warmed the scoped binding).
 */
it('never resolves another tenant record through route model binding', function (string $method, string $url, array $payload): void {
    $response = $this->actingAs($this->user)->json($method, $url, $payload);

    $response->assertNotFound();
})->with([
    'lead update' => fn (): array => ['put', '/leads/'.FormSubmission::factory()->create()->id, ['status' => 'read']],
    'lead delete' => fn (): array => ['delete', '/leads/'.FormSubmission::factory()->create()->id, []],
    'generation show' => fn (): array => ['get', '/ai/generations/'.AiGeneration::factory()->create()->id, []],
    'media update' => fn (): array => ['put', '/media/'.MediaAsset::factory()->create()->id, ['alt' => 'x']],
    'media delete' => fn (): array => ['delete', '/media/'.MediaAsset::factory()->create()->id, []],
]);
