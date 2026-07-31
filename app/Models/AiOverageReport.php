<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Carbon\CarbonImmutable;
use Database\Factories\AiOverageReportFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Monthly ledger of AI credit overage already reported to Stripe's meter,
 * so the daily command only reports the delta.
 *
 * @property-read string $id
 * @property-read string $tenant_id
 * @property-read CarbonImmutable $period
 * @property-read int $credits_reported
 */
final class AiOverageReport extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<AiOverageReportFactory> */
    use HasFactory;

    use HasUuids;

    protected $guarded = [];

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'period' => 'immutable_date',
            'credits_reported' => 'integer',
        ];
    }
}
