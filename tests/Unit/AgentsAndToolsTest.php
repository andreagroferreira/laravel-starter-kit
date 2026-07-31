<?php

declare(strict_types=1);

use App\Ai\Agents\ArticleWriterAgent;
use App\Ai\Agents\CopywriterAgent;
use App\Mcp\Tools\CreatePageTool;
use App\Mcp\Tools\GetPageTool;
use App\Mcp\Tools\ListSitesTool;
use App\Mcp\Tools\UpdateBlocksTool;
use App\Models\BrandProfile;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\JsonSchema\JsonSchemaTypeFactory;
use Illuminate\Support\Facades\RateLimiter;

it('builds full brand instructions with glossary and examples', function (): void {
    $profile = new BrandProfile([
        'tone_of_voice' => 'casual',
        'glossary' => ['CMS' => 'content management system'],
        'examples' => ['Somos fixes.'],
    ]);

    $instructions = new CopywriterAgent($profile)->instructions();

    expect($instructions)->toContain('casual')
        ->toContain('Glossary')
        ->toContain('Somos fixes.');
});

it('includes glossary in article writer instructions', function (): void {
    $profile = new BrandProfile([
        'tone_of_voice' => 'formal',
        'glossary' => ['AI' => 'inteligência artificial'],
    ]);

    $instructions = new ArticleWriterAgent($profile)->instructions();

    expect($instructions)->toContain('formal')
        ->toContain('Glossary');
});

it('defines schemas for all mcp tools', function (): void {
    $factory = new JsonSchemaTypeFactory;

    expect((new ListSitesTool)->schema($factory))->toBeArray()
        ->and((new GetPageTool)->schema($factory))->toHaveKeys(['site_slug', 'page_slug'])
        ->and((new CreatePageTool)->schema($factory))->toHaveKeys(['site_slug', 'title', 'slug'])
        ->and((new UpdateBlocksTool)->schema($factory))->toHaveKeys(['site_slug', 'page_slug', 'blocks']);
});

it('registers login two-factor and passkeys rate limiters', function (): void {
    $request = Request::create('/login', 'POST', ['email' => 'a@b.c']);
    $request->server->set('REMOTE_ADDR', '127.0.0.1');
    $request->setLaravelSession(resolve(Session::class));

    $loginLimiter = RateLimiter::limiter('login');
    expect($loginLimiter($request))->toBeInstanceOf(Limit::class);

    $twoFactorLimiter = RateLimiter::limiter('two-factor');
    expect($twoFactorLimiter($request))->toBeInstanceOf(Limit::class);

    $requestWithCredential = Request::create('/passkeys', 'POST', ['credential' => ['id' => 'cred-1']]);
    $requestWithCredential->server->set('REMOTE_ADDR', '127.0.0.1');
    $requestWithCredential->setLaravelSession(resolve(Session::class));

    $passkeysLimiter = RateLimiter::limiter('passkeys');
    expect($passkeysLimiter($requestWithCredential))->toBeInstanceOf(Limit::class);
});
