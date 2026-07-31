<?php

declare(strict_types=1);

namespace App\Jobs\Ai;

use App\Ai\Agents\ArticleWriterAgent;
use App\Ai\AiGenerationResult;
use App\Enums\ContentStatus;
use App\Models\AiGeneration;
use App\Models\Post;
use App\Models\Site;
use App\Models\Tenant;
use App\Support\UniqueSlug;
use Laravel\Ai\Responses\StructuredAgentResponse;
use RuntimeException;

final class GenerateArticle extends AiGenerationJob
{
    public function agentName(): string
    {
        return 'article_writer';
    }

    public function run(AiGeneration $generation, Tenant $tenant): AiGenerationResult
    {
        $input = $generation->input;
        $site = Site::query()->findOrFail($generation->site_id);

        $briefing = is_string($input['briefing'] ?? null) ? $input['briefing'] : '';
        $language = is_string($input['language'] ?? null) ? $input['language'] : null;

        $prompt = 'Briefing: '.$briefing
            .($language !== null ? "\nLanguage: ".$language : '');

        $response = new ArticleWriterAgent($tenant->brandProfile)->prompt($prompt);

        throw_unless($response instanceof StructuredAgentResponse, RuntimeException::class, 'The article agent returned an unstructured response.');

        /** @var array{title: string, excerpt: string, body: string, seo_title?: string, seo_description?: string} $article */
        $article = $response->toArray();

        /** @var Post $post */
        $post = $site->posts()->create([
            'author_id' => $generation->user_id,
            'title' => $article['title'],
            'slug' => UniqueSlug::make($site->posts()->getQuery(), $article['title']),
            'status' => ContentStatus::Draft,
            'excerpt' => $article['excerpt'],
            'body' => $article['body'],
            'seo' => array_filter([
                'title' => $article['seo_title'] ?? null,
                'description' => $article['seo_description'] ?? null,
            ]) ?: null,
        ]);

        return new AiGenerationResult(
            output: ['post_id' => $post->id, 'article' => $article],
            usage: $response->usage,
            provider: $response->meta->provider,
            model: $response->meta->model,
        );
    }
}
