# Dual Auth - Staff vs Customer

WizardingCode boilerplate has TWO independent auth systems. They MUST NOT contaminate.

## Staff (backoffice users)

- Table: `users`
- Model: `App\Models\User`
- Guard: `web` (sessions)
- Provider: Fortify (login, password reset, email verification, 2FA TOTP, recovery codes, impersonate)
- Authorization: spatie/laravel-permission (roles + permissions, fine-grained)
- Routes: `routes/auth.php` mounted on `/admin/` prefix
- Pages: `resources/js/Pages/Auth/*.vue` (monolith mode)

## Customer (end users)

- Table: `customers`
- Model: `App\Models\Customer`
- Guard: `customer` (defined in `config/auth.php`)
- Provider: Sanctum (API tokens) + Socialite (social login: Google, Apple, GitHub, Microsoft, Facebook)
- Authorization: role enum simple (`CustomerRole::Free, Pro, Enterprise`)
- Routes: `routes/api.php` — `/api/v1/auth/{register, login, logout, password/forgot, password/reset, verify-email, social/{provider}}`

## Rules

1. NEVER call `Auth::user()` without specifying the guard explicitly. Use `auth('web')->user()` for Staff, `auth('customer')->user()` for Customer.
2. NEVER mix `auth('web')` and `auth('customer')` middleware in the same route group.
3. NEVER use the same FormRequest for both Staff and Customer endpoints. Duplicate is fine.
4. NEVER share Policies between User and Customer. If business logic is identical, refactor to a Service that takes either.
5. Each derived project may use only Staff, only Customer, or both. Install wizard handles cleanup of unused side.

## Example - Correct controller

```php
final class AdminUserController extends Controller
{
    public function __invoke(): JsonResource
    {
        $user = auth('web')->user();
        abort_unless($user, 401);

        return UserResource::make($user);
    }
}
```

## Example - INCORRECT

```php
// Contamination - never do this
public function __invoke(Request $request): JsonResource
{
    $user = Auth::user(); // which guard?
    return UserResource::make($user);
}
```
