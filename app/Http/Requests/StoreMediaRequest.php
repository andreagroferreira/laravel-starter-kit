<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\MediaAsset;
use Illuminate\Foundation\Http\FormRequest;

final class StoreMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', MediaAsset::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,gif,svg,pdf', 'max:10240'],
            'alt' => ['nullable', 'string', 'max:255'],
        ];
    }
}
