<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth\Customer;

use Illuminate\Foundation\Http\FormRequest;

final class SocialCallbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => ['sometimes', 'string'],
            'state' => ['sometimes', 'string'],
        ];
    }
}
