---
name: wc-module
description: Scaffold a new optional package in packages/wizardingcode-<name>/.
arguments: module-name (kebab-case)
---

# /wc-module <module-name>

Scaffold a new optional Composer package as a local path repository.

1. `[arka:routing] dev -> Paulo`.
2. Create `packages/wizardingcode-<module-name>/` with:
   - `composer.json` (psr-4 `WizardingCode\<PascalName>\`)
   - `README.md`
   - `src/<PascalName>ServiceProvider.php`
   - `tests/` (Pest)
3. Add `wizardingcode/<module-name>` to root `composer.json` require block.
4. Register ServiceProvider auto-discovery in package composer.json.
5. Run `composer dump-autoload` + `php artisan package:discover`.
6. Create a feature test that confirms the ServiceProvider boots.
7. Document in `.arka/kill-list.md` if the module is OPTIONAL.

ARGUMENTS: $1 = kebab-case module name (e.g. `wc-module billing`).
