<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\ContentStatus;
use App\Events\PostPublished;
use App\Models\Post;
use App\Services\AuditLogger;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Description('Publish posts whose scheduled time has passed')]
#[Signature('posts:publish-scheduled')]
final class PublishScheduledPostsCommand extends Command
{
    public function handle(): int
    {
        $due = Post::query()
            ->where('status', ContentStatus::Scheduled)
            ->where('published_at', '<=', now())
            ->with('site.tenant')
            ->get();

        foreach ($due as $post) {
            $tenant = $post->site->tenant;

            $post->update(['status' => ContentStatus::Published]);

            AuditLogger::record(
                $tenant,
                'post.published',
                null,
                'system',
                'post',
                $post->id,
                ['title' => $post->title, 'scheduled_for' => $post->published_at?->toIso8601String()],
            );

            broadcast(new PostPublished($post, $tenant->id));
        }

        $this->info(sprintf('Published %d scheduled post(s).', $due->count()));

        return self::SUCCESS;
    }
}
