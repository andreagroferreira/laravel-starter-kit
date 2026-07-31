<?php

declare(strict_types=1);

namespace App\Mcp\Servers;

use App\Mcp\Tools\CreatePageTool;
use App\Mcp\Tools\GetPageTool;
use App\Mcp\Tools\ListSitesTool;
use App\Mcp\Tools\UpdateBlocksTool;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Version;
use Laravel\Mcp\Server\Contracts\Transport;

#[Version('1.0.0')]
final class WizardServer extends Server
{
    protected array $tools = [
        ListSitesTool::class,
        GetPageTool::class,
        CreatePageTool::class,
        UpdateBlocksTool::class,
    ];

    protected array $resources = [
        //
    ];

    protected array $prompts = [
        //
    ];

    public function __construct(Transport $transport)
    {
        parent::__construct($transport);

        /** @var string $appName */
        $appName = config('app.name', 'WizardInCode');

        $this->name = $appName;
        $this->instructions = <<<MARKDOWN
        Manage {$appName} sites, pages and content.
        Draft-first by design: write tools create or modify drafts and never publish.
        Publishing requires a human in the backoffice (or a token with the publish ability, when enabled).
        MARKDOWN;
    }
}
