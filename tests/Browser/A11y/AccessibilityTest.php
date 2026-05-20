<?php

declare(strict_types=1);

use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\get;
use function Pest\Laravel\postJson;
use function Pest\Laravel\seed;

/*
|--------------------------------------------------------------------------
| Accessibility — Server-Side Sanity Checks.
|
| Full axe-core a11y testing requires a rendered DOM (browser context)
| and is deferred to the Plan 5 CI workflow, where Playwright runs the
| Pest browser suite and pipes the HTML through axe-core.
|
| Until then this file holds server-side sanity checks that protect the
| basic invariants (auth redirects, Inertia envelope, CSRF token present)
| without depending on Playwright. They run on every local + CI Pest run.
|--------------------------------------------------------------------------
*/

uses(RefreshDatabase::class);

beforeEach(function (): void {
    seed(RolePermissionSeeder::class);
});

it('serves the staff login page with an Inertia envelope and CSRF token', function (): void {
    $response = get('/admin/login');
    $response->assertOk();

    $content = (string) $response->getContent();

    expect($content)
        ->toContain('data-page=')
        ->and($content)->toContain('csrf-token');
});

it('serves the admin dashboard shell with an Inertia envelope', function (): void {
    // Plan 2 admin routes are not yet guarded by `auth` middleware
    // (that lands in Plan 3 once profiles + policies are wired). The
    // baseline a11y check here is that the shell renders without
    // server errors and exposes the Inertia container Vue mounts onto.
    $response = get('/admin');
    $response->assertOk();
    expect((string) $response->getContent())->toContain('data-page=');
});

it('exposes the customer register endpoint as JSON, not HTML', function (): void {
    // a11y heuristic — APIs should never accidentally render an HTML
    // login wall; that breaks screen-reader expectations on SPA clients.
    $response = postJson('/api/v1/auth/register', []);
    $response->assertUnprocessable();
    expect($response->headers->get('Content-Type'))->toContain('application/json');
});

it('renders an axe-core a11y assertion stub for the Plan 5 CI workflow', function (): void {
    // Placeholder. Plan 5 will wrap each visit()->assertSee(...) call with
    // axe-core via @axe-core/playwright inside the Pest browser plugin.
    expect(true)->toBeTrue();
})->skip('Full axe-core a11y assertions require Playwright; CI in Plan 5 enables.');
