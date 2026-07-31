<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\SiteType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreSiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['required', 'string', 'alpha_dash', 'max:120'],
            'type' => ['required', Rule::enum(SiteType::class)],
            'domain' => ['nullable', 'string', 'max:255'],
        ];
    }
}
