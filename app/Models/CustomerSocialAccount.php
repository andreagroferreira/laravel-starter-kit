<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property-read int $id
 * @property-read int $customer_id
 * @property-read string $provider
 * @property-read string $provider_id
 * @property-read string|null $provider_token
 * @property-read string|null $provider_refresh_token
 * @property-read CarbonInterface|null $expires_at
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read Customer $customer
 */
#[Fillable([
    'customer_id',
    'provider',
    'provider_id',
    'provider_token',
    'provider_refresh_token',
    'expires_at',
])]
final class CustomerSocialAccount extends Model
{
    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    public function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'provider_token' => 'encrypted',
            'provider_refresh_token' => 'encrypted',
        ];
    }
}
