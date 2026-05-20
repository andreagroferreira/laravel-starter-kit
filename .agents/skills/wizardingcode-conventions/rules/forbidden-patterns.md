# Forbidden Patterns (catalog)

Triggers automatic PR rejection. See `CLAUDE.md section 7` for canonical list.

## In PHP

| Pattern | Replacement |
|---|---|
| `$guarded = []` | `protected $fillable = [...]` |
| `$request->all()` | FormRequest + `$request->validated()` |
| Eloquent query in controller | Move to Service or Action |
| Inline `$request->validate([...])` | FormRequest class |
| `Auth::user()` without guard | `auth('web')->user()` or `auth('customer')->user()` |
| `dd()`, `dump()`, `var_dump()`, `ray()` in source | Remove before commit (lint catches) |
| Untranslated user-facing strings | `__('key')` |
| Secrets in `.env` (runtime config) | `app/Settings/<X>Settings.php` |

## In Vue / Inertia (monolith mode - Plan 2+)

| Pattern | Replacement |
|---|---|
| `<input type="file">` | `<WcDropzone>` |
| `USlideover` for destructive confirms | `UModal` + `WcConfirmModal` |
| Raw Tailwind colors (`text-gray-*`, `bg-white`) | Semantic tokens (`text-default`, `bg-default`) |
| Page-per-CRUD when modal fits | UModal in master-detail or table |
| Vendor file edited without `@vendor:` header check | Use `/wc-vendor-upgrade` workflow |

## In CI / git

| Pattern | Replacement |
|---|---|
| `git commit --no-verify` | Fix the hook failure |
| `git push --force` to main | Reset locally + clean push from feature branch |
| `composer arka:gate` skipped | Run it - no `--no-gate` flag exists |
| `.env`, `*.pem`, `*.key`, `id_rsa` in `git add` | Forbidden by pre-commit hook |
