<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\TenantRole;
use App\Support\CurrentTenant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class InviteMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manageMembers', resolve(CurrentTenant::class)->getOrFail()) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', Rule::enum(TenantRole::class)],
        ];
    }
}
