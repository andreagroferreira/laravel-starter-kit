<?php

declare(strict_types=1);

namespace App\Support;

final class McpSnippets
{
    /**
     * MCP connection snippets for agents.
     *
     * @return array{claude: string, codex: string}
     */
    public static function make(string $url): array
    {
        return [
            'claude' => 'claude mcp add --transport http wizard '.$url,
            'codex' => "mcp_servers.wizard\n  url = \"{$url}\"",
        ];
    }
}
