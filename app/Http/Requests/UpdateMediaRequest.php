<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\MediaAsset;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ($media = $this->route('media')) instanceof MediaAsset && ($this->user()?->can('update', $media) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'alt' => ['nullable', 'string', 'max:255'],
        ];
    }
}
