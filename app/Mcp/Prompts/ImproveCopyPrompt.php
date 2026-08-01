<?php

declare(strict_types=1);

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Description('Improve the copy of an existing page without changing its structure.')]
final class ImproveCopyPrompt extends Prompt
{
    /**
     * @return array<int, Argument>
     */
    public function arguments(): array
    {
        return [
            new Argument('site_slug', 'Slug of the site.', required: true),
            new Argument('page_slug', 'Slug of the page to improve.', required: true),
            new Argument('goal', 'What to optimise for (clarity, conversion, tone…).'),
        ];
    }

    public function handle(Request $request): Response
    {
        $site = $request->string('site_slug')->value();
        $page = $request->string('page_slug')->value();
        $goal = $request->string('goal', 'clarity and conversion')->value();

        return Response::text(<<<PROMPT
        Improve the copy of the page "{$page}" on site "{$site}", optimising for {$goal}.

        Steps:
        1. Read tenant://brand-voice and respect the tone and glossary.
        2. Call get_page for "{$page}" and study the existing blocks.
        3. Rewrite only the text fields. Keep the same block types, order and structure —
           layout changes need a human.
        4. Apply the result with update_blocks. The page stays a draft version until
           someone publishes the site.
        PROMPT);
    }
}
