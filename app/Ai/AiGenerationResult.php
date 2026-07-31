<?php

declare(strict_types=1);

namespace App\Ai;

use Laravel\Ai\Responses\Data\Usage;

final readonly class AiGenerationResult
{
    /**
     * @param  array<string, mixed>  $output
     */
    public function __construct(
        public array $output,
        public Usage $usage,
        public ?string $provider = null,
        public ?string $model = null,
    ) {}
}
