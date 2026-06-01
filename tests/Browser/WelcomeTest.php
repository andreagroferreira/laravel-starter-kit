<?php

declare(strict_types=1);

it('renders the dashboard shell', function (): void {
    $page = visit('/');

    $page->assertSee('Customers')
        ->assertSee('Settings');
});
