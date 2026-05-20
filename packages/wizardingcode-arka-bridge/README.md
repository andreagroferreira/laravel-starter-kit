# wizardingcode/arka-bridge

ArkaOS integration bridge for the WizardingCode boilerplate.

## Provides

- Console command `arka:sync` (wraps `bin/arka-sync-agents` from project root).
- ServiceProvider that registers the command.
- Reading helper for `.arka/project.yaml` and `.arka/compatibility.yaml`.

## Install

In a WC boilerplate-derived project this is required automatically. Standalone:

```bash
composer require wizardingcode/arka-bridge
```

## Usage

```bash
php artisan arka:sync
```

## Versioning

Follows ArkaOS core version. See `.arka/compatibility.yaml`.
