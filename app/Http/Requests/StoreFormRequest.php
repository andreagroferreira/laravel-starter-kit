<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\Form;
use Illuminate\Foundation\Http\FormRequest;

final class StoreFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Form::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'alpha_dash', 'max:60'],
            'fields' => ['required', 'array', 'min:1'],
            'fields.*.name' => ['required', 'string', 'max:60'],
            'fields.*.type' => ['required', 'in:text,email,textarea,number,tel,url'],
            'fields.*.required' => ['boolean'],
        ];
    }
}
