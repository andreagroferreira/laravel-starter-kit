<?php

declare(strict_types=1);

namespace App\Support;

final class TokenAbilities
{
    /**
     * Available MCP/API scopes.
     *
     * @var list<string>
     */
    public const array ALL = ['read', 'write:draft', 'publish', 'admin'];
}
