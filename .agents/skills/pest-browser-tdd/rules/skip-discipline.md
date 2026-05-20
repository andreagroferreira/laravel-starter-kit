# Skip Discipline

Pest browser tests `->skip('reason')` MUST include the reason. Acceptable reasons:

1. "Pest browser requires Playwright; CI in Plan 5 enables." — for tests that need Playwright runtime.
2. "Awaiting Plan N for <feature>" — when the feature isn't implemented yet.
3. "Flaky on macOS Apple Silicon — investigating" — for known-flaky tests being debugged.

Forbidden reasons:
- "TODO" / "later" / "for now" — too vague.
- No reason at all — fails ArchTest.
