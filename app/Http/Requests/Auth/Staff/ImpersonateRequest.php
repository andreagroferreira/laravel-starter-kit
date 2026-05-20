<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth\Staff;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

final class ImpersonateRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var User|null $authUser */
        $authUser = $this->user('web');
        if ($authUser === null) {
            return false;
        }

        /** @var User|null $target */
        $target = $this->route('user');
        if (! $target instanceof User) {
            return false;
        }

        return $authUser->can('impersonate', $target);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
