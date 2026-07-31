<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Support\CurrentTenant;
use App\Support\TokenAbilities;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manageTokens', resolve(CurrentTenant::class)->getOrFail()) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'abilities' => ['required', 'array', 'min:1'],
            'abilities.*' => [Rule::in(TokenAbilities::ALL)],
        ];
    }
}
