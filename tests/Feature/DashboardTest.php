<?php

declare(strict_types=1);

use Inertia\Testing\AssertableInertia as Assert;

it('renders the dashboard home', function (): void {
    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page->component('Dashboard/Index'));
});

it('renders the inbox with mails', function (): void {
    $this->get('/inbox')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Inbox')
            ->has('mails', 12)
            ->has('mails.0', fn (Assert $mail): Assert => $mail
                ->has('id')
                ->has('unread')
                ->has('from')
                ->has('subject')
                ->has('body')
                ->has('date')
            )
        );
});

it('renders customers with data', function (): void {
    $this->get('/customers')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Customers')
            ->has('customers', 20)
            ->has('customers.0', fn (Assert $customer): Assert => $customer
                ->has('id')
                ->has('name')
                ->has('username')
                ->has('email')
                ->has('avatar')
                ->has('status')
                ->has('location')
            )
        );
});

it('renders settings profile', function (): void {
    $this->get('/settings')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page->component('Settings/Profile'));
});

it('renders settings members with data', function (): void {
    $this->get('/settings/members')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page
            ->component('Settings/Members')
            ->has('members', 8)
        );
});

it('renders settings notifications', function (): void {
    $this->get('/settings/notifications')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page->component('Settings/Notifications'));
});

it('renders settings security', function (): void {
    $this->get('/settings/security')
        ->assertOk()
        ->assertInertia(fn (Assert $page): Assert => $page->component('Settings/Security'));
});

it('does not eagerly share notifications on full page loads', function (): void {
    // `notifications` is an Inertia optional prop — it must not be resolved on a
    // regular page load, only when the slideover requests it via partial reload.
    $this->get('/')
        ->assertInertia(fn (Assert $page): Assert => $page->missing('notifications'));
});
