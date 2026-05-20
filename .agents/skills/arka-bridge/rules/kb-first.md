# KB-First Research (non-negotiable)

Before any external research (Context7, WebSearch, WebFetch, Firecrawl):

1. Call `mcp__obsidian__search_notes` on the query first.
2. Cite relevant hits with `[[wikilinks]]` or explicitly declare a KB gap.
3. Only after (1) and (2) may external tools run.

## Where to search in this project

```
Projects/Boilerplate WizardingCode/
├─ UI-UX/                            # components, patterns, heuristics, decision log
├─ Architecture/                     # ADRs, decisions
├─ Conventions/                      # WC patterns explained
└─ Onboarding/                       # how to join the squad
```

## When KB has no answer

State explicitly: "KB gap declared - no note in Projects/Boilerplate WizardingCode/<area>/." Then:
1. Use Context7 MCP for library docs (Laravel, Inertia, Vue, Nuxt UI).
2. Use WebFetch only for upstream docs not in Context7.
3. After research, WRITE the new KB note (Valentina/Carolina for non-code; Paulo/Ines for code).
