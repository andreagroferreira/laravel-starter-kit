<?php

declare(strict_types=1);

use App\Events\AiGenerationUpdated;
use App\Events\PostPublished;
use App\Models\AiGeneration;
use App\Models\Post;
use App\Models\Site;
use App\Models\Tenant;
use Illuminate\Broadcasting\PrivateChannel;

it('broadcasts post publications on the tenant channel', function (): void {
    $tenant = Tenant::factory()->create();
    $site = Site::factory()->for($tenant)->create();
    $post = Post::factory()->for($site)->create(['title' => 'Notícia']);

    $event = new PostPublished($post, $tenant->id);

    expect($event->broadcastOn())->toEqual([new PrivateChannel('tenant.'.$tenant->id)])
        ->and($event->broadcastWith())->toBe([
            'id' => $post->id,
            'site_id' => $site->id,
            'title' => 'Notícia',
        ]);
});

it('broadcasts generation updates on the tenant channel with a light payload', function (): void {
    $generation = AiGeneration::factory()->create();

    $event = new AiGenerationUpdated($generation);

    expect($event->broadcastOn())->toEqual([new PrivateChannel('tenant.'.$generation->tenant_id)])
        ->and($event->broadcastWith()['id'])->toBe($generation->id)
        ->and($event->broadcastWith()['status'])->toBe('queued');
});
