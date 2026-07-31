<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class AiArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('ai.generate') ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'briefing' => ['required', 'string', 'max:4000'],
            'language' => ['nullable', 'string', 'max:10'],
        ];
    }
}
