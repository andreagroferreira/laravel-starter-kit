<?php

declare(strict_types=1);

namespace App\Actions\Fortify;

use App\Enums\TenantRole;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantProvisioner;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

final class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user with a personal tenant.
     *
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
        ])->validate();

        return DB::transaction(function () use ($input): User {
            $user = User::query()->create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
            ]);

            $tenant = Tenant::query()->create([
                'name' => $input['name'],
                'slug' => Str::slug($input['name']).'-'.Str::lower(Str::random(6)),
            ]);

            $tenant->users()->attach($user, ['joined_at' => now()]);

            resolve(TenantProvisioner::class)->provision($tenant);

            setPermissionsTeamId($tenant);

            $user->assignRole(TenantRole::Owner);

            $user->forceFill(['current_tenant_id' => $tenant->getKey()])->save();

            return $user;
        });
    }
}
