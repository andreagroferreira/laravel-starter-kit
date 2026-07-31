<?php

declare(strict_types=1);

use App\Enums\ContentStatus;
use App\Events\PostPublished;
use App\Models\AuditLog;
use App\Models\Post;
use App\Models\Site;
use App\Models\Tenant;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Event;

it('publishes scheduled posts whose time has passed', function (): void {
    Event::fake([PostPublished::class]);

    $tenant = Tenant::factory()->create();
    $site = Site::factory()->for($tenant)->create();

    $due = Post::factory()->for($site)->create([
        'status' => ContentStatus::Scheduled,
        'published_at' => now()->subMinute(),
    ]);

    $future = Post::factory()->for($site)->create([
        'status' => ContentStatus::Scheduled,
        'published_at' => now()->addHour(),
    ]);

    $draft = Post::factory()->for($site)->create([
        'status' => ContentStatus::Draft,
        'published_at' => null,
    ]);

    $this->artisan('posts:publish-scheduled')
        ->expectsOutputToContain('Published 1 scheduled post(s).')
        ->assertSuccessful();

    expect($due->refresh()->status)->toBe(ContentStatus::Published)
        ->and($future->refresh()->status)->toBe(ContentStatus::Scheduled)
        ->and($draft->refresh()->status)->toBe(ContentStatus::Draft);

    Event::assertDispatched(PostPublished::class, fn (PostPublished $event): bool => $event->post->is($due) && $event->tenantId === $tenant->id);

    expect(AuditLog::query()->withoutGlobalScope('tenant')->where('action', 'post.published')->where('actor_type', 'system')->count())->toBe(1);
});

it('registers the recurring schedule', function (): void {
    $events = collect(resolve(Schedule::class)->events())
        ->map(fn (Illuminate\Console\Scheduling\Event $event): string => (string) $event->command)
        ->filter()
        ->implode(' ');

    expect($events)->toContain('posts:publish-scheduled')
        ->toContain('billing:report-ai-overage')
        ->toContain('horizon:snapshot')
        ->toContain('sanctum:prune-expired');
});
