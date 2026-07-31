<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

final class TenantNotResolved extends RuntimeException
{
    public static function make(): self
    {
        return new self('No tenant is bound to the current lifecycle.');
    }
}
