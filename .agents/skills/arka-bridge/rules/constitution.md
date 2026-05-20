# Constitution (10 non-negotiable rules)

1. **Mandatory 13-phase flow** - see `@rules/mandatory-flow.md`.
2. **Squad routing** - `[arka:routing] dev -> Paulo` etc. as first non-trivial line.
3. **KB-first research** - `@rules/kb-first.md`.
4. **Spec-driven** - `docs/superpowers/specs/<date>-<topic>-design.md` before code.
5. **Quality Gate** - `@rules/quality-gate.md`.
6. **Dual-auth discipline** - guards separated, never contaminate.
7. **Dynamic-settings-only** - runtime config in DB, not `.env`.
8. **Vendor-lock respect** - `@vendor:` files off-limits without `/wc-vendor-upgrade`.
9. **No-secrets-commit** - gitleaks + forbidden files (pre-commit).
10. **No-self-approval** - Marta+Eduardo+Francisca review required for done.

Violations: PR rejected, mandatory remediation before merge.
