<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\AiCreditService;
use App\Support\CurrentTenant;
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
        $current = resolve(CurrentTenant::class);

        if ($current->has()) {
            abort_unless($this->credits->hasCredits($current->getOrFail()), 402, 'Out of AI credits for this billing period.');
        }

        return $next($request);
    }
}
