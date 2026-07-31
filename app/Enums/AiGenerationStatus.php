<?php

declare(strict_types=1);

namespace App\Enums;

enum AiGenerationStatus: string
{
    case Queued = 'queued';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';

    public function isFinal(): bool
    {
        return $this === self::Completed || $this === self::Failed;
    }
}
