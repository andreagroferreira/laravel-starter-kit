<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\DashboardMockData;
use Inertia\Inertia;
use Inertia\Response;

final class InboxController
{
    public function index(): Response
    {
        return Inertia::render('Inbox', [
            'mails' => DashboardMockData::mails(),
        ]);
    }
}
