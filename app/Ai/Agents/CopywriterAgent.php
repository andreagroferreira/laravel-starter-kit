<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use App\Models\BrandProfile;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

final readonly class CopywriterAgent implements Agent
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
        $instructions = 'You are a senior conversion copywriter for a CMS. '
            .'Write concise, high-quality copy for website blocks. '
            .'Return only the copy itself — no explanations, no markdown fences.';

        if ($this->brandProfile instanceof BrandProfile) {
            $instructions .= "\n\nBrand voice: ".($this->brandProfile->tone_of_voice ?? 'neutral');

            if ($this->brandProfile->glossary !== null && $this->brandProfile->glossary !== []) {
                $instructions .= "\nGlossary: ".json_encode($this->brandProfile->glossary);
            }

            if ($this->brandProfile->examples !== null && $this->brandProfile->examples !== []) {
                $instructions .= "\nStyle examples:\n- ".implode("\n- ", $this->brandProfile->examples);
            }
        }

        return $instructions;
    }
}
