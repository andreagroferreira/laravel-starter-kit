<?php

declare(strict_types=1);

use App\Models\User;

it('renders the dashboard shell', function (): void {
    $this->actingAs(User::factory()->create());

    $page = visit('/');

    $page->assertSee('Dashboard')
        ->assertSee('Sites')
        ->assertSee('AI credits')
        ->assertNoJavaScriptErrors();
});
