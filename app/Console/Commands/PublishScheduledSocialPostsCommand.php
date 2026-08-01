<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\Integrations\PublishSocialPost;
use App\Models\SocialPost;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Description('Queue social posts whose scheduled time has passed')]
#[Signature('social:publish-scheduled')]
final class PublishScheduledSocialPostsCommand extends Command
{
    public function handle(): int
    {
        $due = SocialPost::query()->withoutGlobalScope('tenant')->due()->get();

        foreach ($due as $post) {
            // Claim it first: the job is a no-op unless the row is already
            // "publishing", so a duplicate dispatch cannot double-post.
            $post->update(['status' => 'publishing']);

            dispatch(new PublishSocialPost($post->id, $post->tenant_id));
        }

        $this->info(sprintf('Queued %d social post(s).', $due->count()));

        return self::SUCCESS;
    }
}
