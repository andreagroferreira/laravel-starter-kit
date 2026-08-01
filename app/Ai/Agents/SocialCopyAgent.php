<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use App\Models\BrandProfile;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

/**
 * Adapts one message to a specific network: each has its own length,
 * hashtag culture and tone, but the brand voice stays the same.
 */
final readonly class SocialCopyAgent implements Agent
{
    use Promptable;

    private const array NETWORK_RULES = [
        'facebook' => 'Up to 400 characters, conversational, at most one link, no hashtag spam.',
        'instagram' => 'Up to 300 characters, visual and direct, 3 to 5 relevant hashtags at the end, no links in the copy.',
        'linkedin' => 'Up to 700 characters, professional, insight first, at most 2 hashtags.',
    ];

    public function __construct(
        private ?BrandProfile $brandProfile = null,
        private string $network = 'facebook',
    ) {}

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): string
    {
        $rules = self::NETWORK_RULES[$this->network] ?? self::NETWORK_RULES['facebook'];

        $instructions = sprintf(
            'You adapt content into a social post for %s. Rules for this network: %s '
            .'Return only the post text — no preamble, no explanation, no quotes.',
            $this->network,
            $rules,
        );

        if ($this->brandProfile instanceof BrandProfile) {
            $instructions .= "\n\nBrand voice: ".($this->brandProfile->tone_of_voice ?? 'neutral');

            if ($this->brandProfile->glossary !== null && $this->brandProfile->glossary !== []) {
                $instructions .= "\nGlossary: ".json_encode($this->brandProfile->glossary);
            }
        }

        return $instructions;
    }
}
