<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Carbon\CarbonInterface;
use Database\Factories\MediaAssetFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * @property-read string $id
 * @property-read string $tenant_id
 * @property-read string|null $uploaded_by
 * @property-read string $disk
 * @property-read string $path
 * @property-read string $filename
 * @property-read string|null $mime_type
 * @property-read int $size
 * @property-read string|null $alt
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class MediaAsset extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<MediaAssetFactory> */
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
            'uploaded_by' => 'string',
            'disk' => 'string',
            'path' => 'string',
            'filename' => 'string',
            'mime_type' => 'string',
            'size' => 'integer',
            'alt' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
