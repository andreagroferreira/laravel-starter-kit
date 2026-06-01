<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\DashboardMockData;
use Inertia\Inertia;
use Inertia\Response;

final class CustomerController
{
    public function index(): Response
    {
        return Inertia::render('Customers', [
            'customers' => DashboardMockData::customers(),
        ]);
    }
}
