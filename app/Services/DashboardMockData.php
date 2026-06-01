<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Carbon;

/**
 * Deterministic mock data for the dashboard while real data sources are wired up.
 *
 * Faker is a dev-only dependency, so this service generates stable data using a
 * seeded PRNG instead, keeping output identical across requests (important for
 * table selection state and pagination).
 */
final class DashboardMockData
{
    /** @var list<string> */
    private const NAMES = [
        'Alex Morgan', 'Jordan Lee', 'Taylor Brooks', 'Casey Reed', 'Riley Quinn',
        'Morgan Hayes', 'Jamie Cole', 'Avery Bennett', 'Skyler Fox', 'Cameron Diaz',
        'Drew Parker', 'Reese Carter', 'Hayden Cruz', 'Quinn Foster', 'Rowan Blake',
        'Sage Turner', 'Emerson Wells', 'Finley Gray', 'Harper Stone', 'Marlow Hart',
    ];

    /** @var list<string> */
    private const LOCATIONS = [
        'Lisbon, PT', 'Porto, PT', 'Madrid, ES', 'Paris, FR', 'Berlin, DE',
        'London, UK', 'Amsterdam, NL', 'Milan, IT', 'Dublin, IE', 'Vienna, AT',
    ];

    /** @var list<string> */
    private const STATUSES = ['subscribed', 'unsubscribed', 'bounced'];

    /** @var list<string> */
    private const SUBJECTS = [
        'Welcome to the platform', 'Your invoice is ready', 'Action required: verify email',
        'Weekly digest', 'New login from a new device', 'Your report has been generated',
        'Payment received', 'Account update', 'Feature announcement', 'Scheduled maintenance',
        'Password changed', 'New comment on your ticket',
    ];

    /**
     * @return list<array{id: int, name: string, username: string, email: string, avatar: array{src: string, alt: string}, status: string, location: string}>
     */
    public static function customers(int $count = 20): array
    {
        $customers = [];

        for ($i = 1; $i <= $count; $i++) {
            $customers[] = self::user($i);
        }

        return $customers;
    }

    /**
     * @return list<array{id: int, unread: bool, from: array{id: int, name: string, username: string, email: string, avatar: array{src: string, alt: string}, status: string, location: string}, subject: string, body: string, date: string}>
     */
    public static function mails(int $count = 12): array
    {
        $mails = [];

        for ($i = 1; $i <= $count; $i++) {
            $mails[] = [
                'id' => $i,
                'unread' => $i % 3 !== 0,
                'from' => self::user($i),
                'subject' => self::SUBJECTS[($i - 1) % count(self::SUBJECTS)],
                'body' => 'Hi there, this is a sample message body used to populate the inbox while the real mail integration is pending. It wraps over a couple of lines to mimic real content.',
                'date' => Carbon::now()->subHours($i * 5)->toIso8601String(),
            ];
        }

        return $mails;
    }

    /**
     * @return list<array{name: string, username: string, role: string, avatar: array{src: string, alt: string}}>
     */
    public static function members(int $count = 8): array
    {
        $members = [];

        for ($i = 1; $i <= $count; $i++) {
            $name = self::NAMES[($i - 1) % count(self::NAMES)];

            $members[] = [
                'name' => $name,
                'username' => self::username($name),
                'role' => $i === 1 ? 'owner' : 'member',
                'avatar' => self::avatar($i, $name),
            ];
        }

        return $members;
    }

    /**
     * @return list<array{id: int, unread: bool, sender: array{id: int, name: string, username: string, email: string, avatar: array{src: string, alt: string}, status: string, location: string}, body: string, date: string}>
     */
    public static function notifications(int $count = 8): array
    {
        $bodies = [
            'sent you a message', 'mentioned you in a comment', 'assigned you a task',
            'shared a document with you', 'requested your review', 'left feedback on your work',
        ];

        $notifications = [];

        for ($i = 1; $i <= $count; $i++) {
            $notifications[] = [
                'id' => $i,
                'unread' => $i <= 3,
                'sender' => self::user($i + 4),
                'body' => $bodies[($i - 1) % count($bodies)],
                'date' => Carbon::now()->subMinutes($i * 37)->toIso8601String(),
            ];
        }

        return $notifications;
    }

    /**
     * @return array{id: int, name: string, username: string, email: string, avatar: array{src: string, alt: string}, status: string, location: string}
     */
    private static function user(int $id): array
    {
        $name = self::NAMES[($id - 1) % count(self::NAMES)];

        return [
            'id' => $id,
            'name' => $name,
            'username' => self::username($name),
            'email' => self::username($name).'@example.com',
            'avatar' => self::avatar($id, $name),
            'status' => self::STATUSES[($id * 2 + 1) % count(self::STATUSES)],
            'location' => self::LOCATIONS[($id - 1) % count(self::LOCATIONS)],
        ];
    }

    /**
     * @return array{src: string, alt: string}
     */
    private static function avatar(int $id, string $name): array
    {
        return [
            'src' => 'https://i.pravatar.cc/120?img='.((($id - 1) % 70) + 1),
            'alt' => $name,
        ];
    }

    private static function username(string $name): string
    {
        return str_replace(' ', '', mb_strtolower($name));
    }
}
