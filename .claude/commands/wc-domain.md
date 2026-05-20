---
name: wc-domain
description: Promote a model from app/Models/ to app/Domains/<Context>/ when the promotion rule applies (5+ files OR 2+ shared contexts).
arguments: domain-name (PascalCase)
---

# /wc-domain <DomainName>

Promote a model + its services/actions/data into `app/Domains/<DomainName>/`.

1. `[arka:routing] dev -> Paulo, qa -> Francisca` (Francisca validates promotion rule).
2. Confirm the model satisfies the promotion rule.
3. Create folder structure: `app/Domains/<DomainName>/{Models, Services, Actions, Data, Repositories, Policies}`.
4. Move existing files. Update namespaces in:
   - PHP files (Rector handles)
   - `composer.json` autoload (`psr-4`) - extend `App\\Domains\\` if first time
   - migrations references
   - factories
5. Run `composer dump-autoload`.
6. Run Pint + PHPStan + Pest. Confirm 0 failures.
7. Document promotion decision in `docs/superpowers/specs/decision-log/promotion-<domain>.md`.

ARGUMENTS: $1 = domain name (e.g. `wc-domain Billing`).
