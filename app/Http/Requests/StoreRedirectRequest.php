<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Redirect;
use Illuminate\Foundation\Http\FormRequest;

final class StoreRedirectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Redirect::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'from_path' => ['required', 'string', 'starts_with:/', 'max:500'],
            'to_path' => ['required', 'string', 'max:500'],
            'status_code' => ['required', 'in:301,302,307,308'],
        ];
    }
}
