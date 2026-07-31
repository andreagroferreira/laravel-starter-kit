<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Menu;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ($menu = $this->route('menu')) instanceof Menu && ($this->user()?->can('update', $menu) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'items' => ['present', 'array'],
            'items.*.label' => ['required', 'string', 'max:100'],
            'items.*.url' => ['required', 'string', 'max:500'],
        ];
    }
}
