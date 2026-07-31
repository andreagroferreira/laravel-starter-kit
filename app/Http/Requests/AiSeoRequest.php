<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class AiSeoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('ai.generate') ?? false;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'briefing' => ['required', 'string', 'max:20000'],
        ];
    }
}
