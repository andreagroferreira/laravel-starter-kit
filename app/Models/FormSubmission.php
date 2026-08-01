<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Carbon\CarbonImmutable;
use Database\Factories\FormSubmissionFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A lead captured by a public site form.
 *
 * @property-read string $id
 * @property-read string $tenant_id
 * @property-read string $site_id
 * @property-read string $form_id
 * @property-read array<string, mixed> $data
 * @property-read string $status
 * @property-read string|null $ip_hash
 * @property-read string|null $user_agent
 * @property-read string|null $referrer
 * @property-read CarbonImmutable $created_at
 * @property-read Site $site
 * @property-read Form $form
 */
final class FormSubmission extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<FormSubmissionFactory> */
    use HasFactory;

    use HasUuids;

    protected $guarded = [];

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * @return BelongsTo<Form, $this>
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }
}
