<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Services\DashboardMockData;
use Inertia\Inertia;
use Inertia\Response;

final class MembersController
{
    public function __invoke(): Response
    {
        return Inertia::render('Settings/Members', [
            'members' => DashboardMockData::members(),
        ]);
    }
}
