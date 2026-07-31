<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;

final class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        $post = $this->route('post');

        return $post instanceof Post && ($this->user()?->can('update', $post) ?? false);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:200'],
            'slug' => ['sometimes', 'required', 'string', 'max:200'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['nullable', 'string'],
            'seo' => ['nullable', 'array'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['uuid', 'exists:categories,id'],
            'published_at' => ['nullable', 'date'],
        ];
    }
}
