<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\BrandProfile;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateBrandProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage', BrandProfile::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'tone_of_voice' => ['nullable', 'string', 'max:2000'],
            'glossary' => ['nullable', 'array'],
            'examples' => ['nullable', 'array'],
            'examples.*' => ['string', 'max:1000'],
        ];
    }
}
