<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\BlockType;
use App\Models\Page;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdatePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        $page = $this->route('page');

        return $page instanceof Page && ($this->user()?->can('update', $page) ?? false);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:200'],
            'slug' => ['sometimes', 'required', 'string', 'max:200'],
            'seo' => ['nullable', 'array'],
            'seo.title' => ['nullable', 'string', 'max:70'],
            'seo.description' => ['nullable', 'string', 'max:160'],
            'blocks' => ['nullable', 'array'],
            'blocks.*.type' => ['required', Rule::enum(BlockType::class)],
            'blocks.*.content' => ['nullable', 'array'],
        ];
    }
}
