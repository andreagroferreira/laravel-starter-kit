<?php

declare(strict_types=1);

namespace App\Mcp\Servers;

use App\Mcp\Prompts\ImproveCopyPrompt;
use App\Mcp\Prompts\SeoAuditPrompt;
use App\Mcp\Prompts\WriteArticlePrompt;
use App\Mcp\Resources\BrandVoiceResource;
use App\Mcp\Resources\SiteSchemaResource;
use App\Mcp\Resources\SitesListResource;
use App\Mcp\Tools\CreatePageTool;
use App\Mcp\Tools\CreatePostDraftTool;
use App\Mcp\Tools\GetPageTool;
use App\Mcp\Tools\ListMediaTool;
use App\Mcp\Tools\ListPostsTool;
use App\Mcp\Tools\ListSitesTool;
use App\Mcp\Tools\PublishSiteTool;
use App\Mcp\Tools\UpdateBlocksTool;
use App\Mcp\Tools\UpdatePostDraftTool;
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
        ListPostsTool::class,
        CreatePostDraftTool::class,
        UpdatePostDraftTool::class,
        ListMediaTool::class,
        PublishSiteTool::class,
    ];

    protected array $resources = [
        SitesListResource::class,
        SiteSchemaResource::class,
        BrandVoiceResource::class,
    ];

    protected array $prompts = [
        WriteArticlePrompt::class,
        ImproveCopyPrompt::class,
        SeoAuditPrompt::class,
    ];

    public function __construct(Transport $transport)
    {
        parent::__construct($transport);

        /** @var string $appName */
        $appName = config('app.name', 'WizardInCode');

        $this->name = $appName;
        $this->instructions = <<<MARKDOWN
        Manage {$appName} sites, pages, posts and media.

        Draft-first by design: every write tool creates or edits drafts.
        The single exception is publish_site, which requires a token carrying
        the `publish` ability AND a user with the sites.publish permission.

        Token abilities map to what you can do:
        - `read` — list and read sites, pages, posts and media
        - `write:draft` — create and update drafts
        - `publish` — publish a site

        Every write is written to the audit log with origin "agent".
        Start from tenant://brand-voice before generating any copy.
        MARKDOWN;
    }
}
