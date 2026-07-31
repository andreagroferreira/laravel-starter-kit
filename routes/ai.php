<?php

declare(strict_types=1);

use App\Http\Middleware\SetCurrentTenant;
use App\Mcp\Servers\WizardServer;
use Laravel\Mcp\Facades\Mcp;

Mcp::web('/mcp/wizard', WizardServer::class)
    ->middleware(['auth:sanctum', SetCurrentTenant::class]);
