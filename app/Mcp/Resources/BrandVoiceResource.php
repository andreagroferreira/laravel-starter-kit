<?php

declare(strict_types=1);

namespace App\Mcp\Resources;

use App\Support\CurrentTenant;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\MimeType;
use Laravel\Mcp\Server\Attributes\Uri;
use Laravel\Mcp\Server\Resource;

#[Uri('tenant://brand-voice')]
#[MimeType('application/json')]
#[Description('Tone of voice, glossary and writing examples of the tenant. Read this before generating any copy.')]
final class BrandVoiceResource extends Resource
{
    public function handle(Request $request): Response
    {
        $profile = resolve(CurrentTenant::class)->getOrFail()->brandProfile;

        if ($profile === null) {
            return Response::text((string) json_encode([
                'configured' => false,
                'note' => 'No brand voice configured — write in a neutral, clear tone.',
            ], JSON_PRETTY_PRINT));
        }

        return Response::text((string) json_encode([
            'configured' => true,
            'name' => $profile->name,
            'tone_of_voice' => $profile->tone_of_voice,
            'glossary' => $profile->glossary ?? [],
            'examples' => $profile->examples ?? [],
            'guardrails' => $profile->guardrails ?? [],
        ], JSON_PRETTY_PRINT));
    }
}
