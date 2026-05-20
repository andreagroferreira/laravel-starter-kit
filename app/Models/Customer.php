<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CustomerRole;
use Carbon\CarbonInterface;
use Database\Factories\CustomerFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Override;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $email
 * @property-read string|null $password
 * @property-read string|null $avatar_url
 * @property-read CustomerRole $role
 * @property-read CarbonInterface|null $last_login_at
 * @property-read CarbonInterface|null $email_verified_at
 * @property-read string|null $remember_token
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 * @property-read CarbonInterface|null $deleted_at
 */
final class Customer extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;

    /** @use HasFactory<CustomerFactory> */
    use HasFactory;

    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_url',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    #[Override]
    public function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'role' => CustomerRole::class,
        ];
    }
}
