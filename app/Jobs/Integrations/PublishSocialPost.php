<?php

declare(strict_types=1);

namespace App\Jobs\Integrations;

use App\Enums\IntegrationProvider;
use App\Models\Integration;
use App\Models\SocialPost;
use App\Queue\Middleware\TenantAware;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Publishes one scheduled post to Meta. The row is flipped to
 * "publishing" before the call so a retry never double-posts.
 */
final class PublishSocialPost implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly string $socialPostId,
        private readonly string $tenantId,
    ) {}

    /**
     * @return list<object>
     */
    public function middleware(): array
    {
        return [new TenantAware($this->tenantId)];
    }

    public function handle(): void
    {
        $post = SocialPost::query()->findOrFail($this->socialPostId);

        if ($post->status !== 'publishing') {
            return;
        }

        $integration = Integration::query()
            ->where('provider', IntegrationProvider::Meta)
            ->where(fn (Builder $query) => $query->where('site_id', $post->site_id)->orWhereNull('site_id'))
            ->first();

        if ($integration === null || $integration->external_id === null) {
            $post->update(['status' => 'failed', 'error' => 'Meta não está ligado a este site.']);

            return;
        }

        try {
            $response = Http::withToken((string) $integration->access_token)
                ->post(sprintf('https://graph.facebook.com/v21.0/%s/feed', $integration->external_id), [
                    'message' => $post->content,
                ])
                ->throw();

            /** @var array{id?: string} $payload */
            $payload = $response->json();

            $post->update([
                'status' => 'published',
                'published_at' => now(),
                'external_id' => $payload['id'] ?? null,
                'error' => null,
            ]);
        } catch (Throwable $throwable) {
            $post->update(['status' => 'failed', 'error' => $throwable->getMessage()]);
        }
    }
}
