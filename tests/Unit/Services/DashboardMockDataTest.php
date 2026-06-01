<?php

declare(strict_types=1);

use App\Services\DashboardMockData;

it('builds the requested number of customers with a complete shape', function (): void {
    $customers = DashboardMockData::customers(20);

    expect($customers)->toHaveCount(20)
        ->and($customers[0])->toHaveKeys(['id', 'name', 'username', 'email', 'avatar', 'status', 'location'])
        ->and($customers[0]['status'])->toBeIn(['subscribed', 'unsubscribed', 'bounced'])
        ->and($customers[0]['avatar'])->toHaveKeys(['src', 'alt']);
});

it('builds deterministic output across calls', function (): void {
    expect(DashboardMockData::customers())->toEqual(DashboardMockData::customers());
});

it('builds mails with a nested sender user', function (): void {
    $mails = DashboardMockData::mails(12);

    expect($mails)->toHaveCount(12)
        ->and($mails[0])->toHaveKeys(['id', 'unread', 'from', 'subject', 'body', 'date'])
        ->and($mails[0]['unread'])->toBeBool()
        ->and($mails[0]['from'])->toHaveKey('username');
});

it('builds members with valid roles', function (): void {
    $members = DashboardMockData::members(8);

    expect($members)->toHaveCount(8)
        ->and($members[0]['role'])->toBe('owner')
        ->and($members[1]['role'])->toBe('member')
        ->and($members[0])->toHaveKeys(['name', 'username', 'role', 'avatar']);
});

it('builds notifications with a sender and unread flag', function (): void {
    $notifications = DashboardMockData::notifications(8);

    expect($notifications)->toHaveCount(8)
        ->and($notifications[0])->toHaveKeys(['id', 'unread', 'sender', 'body', 'date'])
        ->and($notifications[0]['unread'])->toBeBool()
        ->and($notifications[0]['sender'])->toHaveKey('name');
});
