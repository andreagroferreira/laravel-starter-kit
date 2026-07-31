<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Contracts\BillingManager;
use App\Models\AiOverageReport;
use App\Models\Tenant;
use App\Services\AiCreditService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Description('Report unbilled AI credit overage to the Stripe meter')]
#[Signature('billing:report-ai-overage')]
final class ReportAiOverageCommand extends Command
{
    public function handle(BillingManager $billing, AiCreditService $credits): int
    {
        $period = now()->startOfMonth()->toDateString();
        $reportedTotal = 0;

        foreach (Tenant::query()->whereNotNull('stripe_id')->cursor() as $tenant) {
            $subscription = $tenant->subscription('default');
            if ($subscription === null) {
                continue;
            }

            if (! $subscription->active()) {
                continue;
            }

            /** @var AiOverageReport $ledger */
            $ledger = AiOverageReport::query()
                ->withoutGlobalScope('tenant')
                ->firstOrCreate(['tenant_id' => $tenant->id, 'period' => $period]);

            $overage = $credits->usedThisMonth($tenant) - $tenant->ai_credits_monthly - $ledger->credits_reported;

            if ($overage <= 0) {
                continue;
            }

            $billing->reportAiOverage($tenant, $overage);
            $ledger->update(['credits_reported' => $ledger->credits_reported + $overage]);
            $reportedTotal += $overage;
        }

        $this->info(sprintf('Reported %d overage credit(s).', $reportedTotal));

        return self::SUCCESS;
    }
}
