<?php

declare(strict_types=1);

namespace App\Mcp\Prompts;

use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Prompt;
use Laravel\Mcp\Server\Prompts\Argument;

#[Description('Audit a site for SEO issues and propose concrete fixes.')]
final class SeoAuditPrompt extends Prompt
{
    /**
     * @return array<int, Argument>
     */
    public function arguments(): array
    {
        return [
            new Argument('site_slug', 'Slug of the site to audit.', required: true),
        ];
    }

    public function handle(Request $request): Response
    {
        $site = $request->string('site_slug')->value();

        return Response::text(<<<PROMPT
        Audit the SEO of the site "{$site}".

        Steps:
        1. Read site://{$site}/schema and list_posts to see every page and post.
        2. For each one check: meta title (<= 70 chars, unique), meta description
           (<= 160 chars, unique), a single h1, descriptive slugs, and images with alt text
           (use list_media to see what is available).
        3. Report the issues grouped by severity, each with the concrete fix.
        4. Apply only the text fixes you are confident about, with update_blocks or
           update_post_draft. Leave structural changes to a human.
        PROMPT);
    }
}
