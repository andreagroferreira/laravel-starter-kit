# Dynamic Settings - DB-driven, not .env

Runtime-configurable values MUST live in `app_settings` (DB), NOT in `.env`. This is non-negotiable (boilerplate spec section 2.7).

## Scope

Anything that should be modifiable by Staff at runtime:
- Storage providers (S3 bucket/region, local disk, DO Spaces)
- Email providers (SES, Mailgun, SMTP, Resend)
- AI providers (Anthropic, OpenAI, Google, Mistral) + API keys + model mapping + fallback chain
- Branding (primary color, neutral color, logo URLs)
- Locales active (pt_PT, en, +)
- Audit retention (days per model)
- Feature flags (Pennant integration)

## Mechanism

- Table `app_settings`: `id`, `key` (unique), `value` (JSON), `is_encrypted` (bool), `updated_by`, `updated_at`.
- Encryption: Laravel `Crypt::encryptString()` when `is_encrypted=true`. Stored as ciphertext.
- Cache: each setting cached in Redis with key `setting:{key}`. Invalidated on update.
- Typed accessor classes in `app/Settings/`:
  - `StorageSettings`, `MailSettings`, `AiSettings`, `BrandingSettings`, `LocaleSettings`, `AuditSettings`.
- Backoffice UI: `/admin/settings/{section}` - pages auto-generated from typed settings via reflection.
- Permission: `manage settings` (super-admin only).

## Rules

1. NEVER add a runtime config to `.env` if it could change without redeploy.
2. NEVER bypass the typed accessor - DO NOT use `AppSetting::where(...)->first()` outside `app/Settings/`.
3. Secrets MUST set `is_encrypted=true`.
4. Cache invalidation MUST happen in the setter (the typed class handles this).
5. Activity log MUST record setting changes.

## Example

```php
// app/Settings/AiSettings.php
final class AiSettings
{
    public function defaultProvider(): string
    {
        return cache()->rememberForever(
            'setting:ai.default_provider',
            fn () => AppSetting::value('ai.default_provider', 'anthropic')
        );
    }

    public function setDefaultProvider(string $provider): void
    {
        AppSetting::updateOrCreate(
            ['key' => 'ai.default_provider'],
            ['value' => $provider, 'is_encrypted' => false, 'updated_by' => auth()->id()],
        );
        cache()->forget('setting:ai.default_provider');
    }
}
```
