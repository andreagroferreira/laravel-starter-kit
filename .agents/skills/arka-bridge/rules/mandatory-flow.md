# Mandatory 13-Phase Flow

Every non-trivial request MUST follow this flow. Emit `[arka:phase:N] <label>` before each phase.

```
1. Input (verbatim)
2. Get context (profile, repo, git, cwd tag, session digests)
3. Decide route - emit [arka:routing] <dept> -> <lead>
4. Call hierarchy (Tier 0 when strategic/cross-dept/security/financial)
5. Research (Obsidian + vector DB; cite sources or declare gap)
6. Call team (dispatch specialists via Agent tool)
7. Plan with six parallel reviewers:
     positive analyst / devil's advocate / Q&A / KB research /
     best-solution validator / pessimistic analyst
8. Present plan (save to Obsidian + vector DB + ~/.arkaos/plans/)
9. Wait for EXPLICIT approval (silence is not approval)
10. TODO list (atomic, ordered, independently verifiable)
11. Per-todo loop:
      team call - complete - QA (all tests, E2E, Playwright)
      - Security review - Quality Gate (Marta+Eduardo+Francisca, Opus)
      - Document (Obsidian + vector DB)
12. Loop until TODO exhausted
13. Detailed summary (what was done, where, how to verify, what is open)
```

## Trivial bypass

The ONLY bypass: single-file edit under 10 lines with imperative verb. Emit `[arka:trivial] <reason>` as first line.

## Never bypass

Code-modifying requests, multi-file changes, anything touching auth/security/data/UI must follow the full flow.
