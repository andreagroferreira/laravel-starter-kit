<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Services\AiCreditService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class EnsureAiCredits
{
    public function __construct(
        private AiCreditService $credits,
    ) {}

    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->bound(Tenant::class)) {
            abort_unless($this->credits->hasCredits(resolve(Tenant::class)), 402, 'Out of AI credits for this billing period.');
        }

        return $next($request);
    }
}
