<?php

declare(strict_types=1);

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Description('Write a SEO-ready article draft for a site, in the tenant brand voice.')]
final class WriteArticlePrompt extends Prompt
{
    /**
     * @return array<int, Argument>
     */
    public function arguments(): array
    {
        return [
            new Argument('site_slug', 'Slug of the target site.', required: true),
            new Argument('topic', 'What the article should be about.', required: true),
            new Argument('language', 'Language of the article (default: pt-PT).'),
        ];
    }

    public function handle(Request $request): Response
    {
        $site = $request->string('site_slug')->value();
        $topic = $request->string('topic')->value();
        $language = $request->string('language', 'pt-PT')->value();

        return Response::text(<<<PROMPT
        Write an article for the site "{$site}" about: {$topic}

        Steps:
        1. Read the resource tenant://brand-voice and follow its tone, glossary and guardrails.
        2. Read site://{$site}/schema to match the existing content and avoid duplicates.
        3. Write in {$language}: a title under 70 characters, an excerpt under 500 characters,
           and a body in semantic HTML (h2/h3, p, ul) — no inline styles.
        4. Save it with create_post_draft. Never publish: a human reviews the draft.
        PROMPT);
    }
}
