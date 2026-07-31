<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\BlockType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class AiCopyRequest extends FormRequest
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
            'block_type' => ['required', Rule::enum(BlockType::class)],
            'briefing' => ['required', 'string', 'max:2000'],
            'current_content' => ['nullable', 'string', 'max:10000'],
        ];
    }
}
