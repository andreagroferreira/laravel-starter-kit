<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Carbon\CarbonInterface;
use Database\Factories\BrandProfileFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read string $id
 * @property-read string $tenant_id
 * @property-read string $name
 * @property-read string|null $tone_of_voice
 * @property-read array<string, string>|null $glossary
 * @property-read array<int, string>|null $examples
 * @property-read array<string, mixed>|null $guardrails
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class BrandProfile extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<BrandProfileFactory> */
    use HasFactory;

    use HasUuids;

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'string',
            'tenant_id' => 'string',
            'name' => 'string',
            'tone_of_voice' => 'string',
            'glossary' => 'array',
            'examples' => 'array',
            'guardrails' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
