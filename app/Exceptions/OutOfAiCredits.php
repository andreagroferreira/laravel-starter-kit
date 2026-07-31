<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class OutOfAiCredits extends RuntimeException
{
    public static function make(): self
    {
        return new self('Out of AI credits for this billing period.');
    }
}
