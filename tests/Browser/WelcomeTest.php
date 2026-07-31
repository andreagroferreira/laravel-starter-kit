<?php

declare(strict_types=1);

use App\Models\Tenant;
use App\Models\User;

it('renders the dashboard shell', function (): void {
    $tenant = Tenant::factory()->create();
    $user = User::factory()->create(['current_tenant_id' => $tenant->id]);
    $tenant->users()->attach($user);

    $this->actingAs($user);

    $page = visit('/');

    $page->assertSee('Dashboard')
        ->assertSee('Sites')
        ->assertSee('AI credits')
        ->assertNoJavaScriptErrors();
});
