# Promotion Rule - Models to Domains

Start with `app/Models/X.php`. Promote to `app/Domains/X/` ONLY when complexity justifies.

## Promotion criteria (need 1+)

- 5+ related files (model + 2+ services + 2+ actions + DTOs + repository).
- 2+ contexts share the model.
- Model has its own lifecycle (events, jobs, sagas).
- Policies non-trivial (3+ abilities).

## How to promote

Use `/wc-domain <Name>` command. It does:
1. Create `app/Domains/<Name>/{Models, Services, Actions, Data, Repositories, Policies}`.
2. Move existing files.
3. Update namespaces (Rector handles).
4. Update `composer.json` autoload if first domain.
5. Run `composer dump-autoload`.
6. Run Pint + PHPStan + Pest.
7. Document decision in `docs/superpowers/specs/decision-log/promotion-<Name>.md`.

## Anti-pattern: premature promotion

Do NOT promote at the first model. Wait for real complexity. Francisca rejects PRs that promote based on speculation.
