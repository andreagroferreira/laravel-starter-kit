<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Ai\Agents\ArticleWriterAgent;
use App\Enums\ContentStatus;
use App\Http\Requests\AiArticleRequest;
use App\Models\Site;
use App\Models\Tenant;
use App\Services\AiCreditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Laravel\Ai\Responses\StructuredAgentResponse;

final class AiArticleController
{
    public function __invoke(AiArticleRequest $request, Site $site, AiCreditService $credits): JsonResponse
    {
        /** @var Tenant $tenant */
        $tenant = resolve(Tenant::class);

        /** @var array{briefing: string, language?: string} $validated */
        $validated = $request->validated();

        $prompt = 'Briefing: '.$validated['briefing']
            .(isset($validated['language']) ? '
Language: '.$validated['language'] : '');

        $response = new ArticleWriterAgent($tenant->brandProfile)->prompt($prompt);

        $credits->record($tenant, 'article_writer', $response->usage, $request->user(), [
            'type' => 'site',
            'id' => $site->id,
        ], $response->meta->provider, $response->meta->model);

        /** @var array{title: string, excerpt: string, body: string, seo_title?: string, seo_description?: string} $article */
        $article = $response instanceof StructuredAgentResponse ? $response->toArray() : [];

        $post = $site->posts()->create([
            'author_id' => $request->user()?->getKey(),
            'title' => $article['title'],
            'slug' => Str::slug($article['title']),
            'status' => ContentStatus::Draft,
            'excerpt' => $article['excerpt'],
            'body' => $article['body'],
            'seo' => array_filter([
                'title' => $article['seo_title'] ?? null,
                'description' => $article['seo_description'] ?? null,
            ]) ?: null,
        ]);

        return response()->json([
            'post_id' => $post->id,
            'status' => $post->status->value,
            'article' => $article,
        ], 201);
    }
}
