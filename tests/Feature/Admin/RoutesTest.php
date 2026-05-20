<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\get;

it('renders the admin dashboard via Inertia', function (): void {
    get('/admin')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page->component('Dashboard/Index'));
});

it('renders the admin inbox', function (): void {
    get('/admin/inbox')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page->component('Inbox/Index'));
});

it('renders the admin customers', function (): void {
    get('/admin/customers')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page->component('Customers/Index'));
});

it('renders the admin settings index', function (): void {
    get('/admin/settings')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page->component('Settings/Index'));
});

it('renders the admin settings members', function (): void {
    get('/admin/settings/members')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page->component('Settings/Members'));
});

it('renders the admin settings notifications', function (): void {
    get('/admin/settings/notifications')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page->component('Settings/Notifications'));
});

it('renders the admin settings security', function (): void {
    get('/admin/settings/security')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page->component('Settings/Security'));
});
