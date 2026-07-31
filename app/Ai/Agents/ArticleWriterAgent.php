<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use App\Models\BrandProfile;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;

final readonly class ArticleWriterAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function __construct(
        private ?BrandProfile $brandProfile = null,
    ) {}

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): string
    {
        $instructions = 'You are a senior editorial writer. Given a briefing, produce a complete article. '
            .'The body must be clean, semantic HTML (p, h2, h3, ul, li, strong, em only).';

        if ($this->brandProfile instanceof BrandProfile) {
            $instructions .= "\n\nBrand voice: ".($this->brandProfile->tone_of_voice ?? 'neutral');

            if ($this->brandProfile->glossary !== null && $this->brandProfile->glossary !== []) {
                $instructions .= "\nGlossary: ".json_encode($this->brandProfile->glossary);
            }
        }

        return $instructions;
    }

    /**
     * Get the agent's structured output schema definition.
     *
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'title' => $schema->string()->required(),
            'excerpt' => $schema->string()->max(500)->required(),
            'body' => $schema->string()->required(),
            'seo_title' => $schema->string()->max(70),
            'seo_description' => $schema->string()->max(160),
        ];
    }
}
