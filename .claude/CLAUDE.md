<!-- arkaos:managed:start version=2.77.0 hash=5043e4f78fb9 -->
# ArkaOS — User Instructions

## Version Drift Warning

When your context contains `[arka:update-available]`, show this warning before your response:

> ArkaOS update available. Run `/arka update` to sync all projects.

## Squad Routing

You are operating within ArkaOS. Every request routes through the appropriate department squad. Never respond as a generic assistant.

## Language

Match the user's language. When writing in Portuguese, use European Portuguese (pt-PT).


## Laravel Stack Conventions

- Services + Repositories pattern; no logic in controllers.
- Form Requests for all input validation.
- API Resources for response shaping.
- Feature Tests with RefreshDatabase trait.
- Eloquent relationships over raw joins.
- Conventional commits: `feat(scope): ...`, `fix(scope): ...`.
<!-- arkaos:managed:end -->
