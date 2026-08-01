<?php

declare(strict_types=1);

namespace App\Enums;

enum DeploymentStatus: string
{
    case Queued = 'queued';
    case Running = 'running';
    case Deployed = 'deployed';
    case Failed = 'failed';
}
