# Component Note Anatomy

Required sections in `Projects/Boilerplate WizardingCode/UI-UX/Components/<Category>/<Name>.md`:

```markdown
---
component: <Name>
category: <Forms | Data | Feedback | Layout | Backoffice>
status: Stable | Beta | Deprecated
since: <Plan N>
owner: <agent-name>
---

# <ComponentName>

Brief one-line description.

## Screenshots

![[<Name>-light.png]] ![[<Name>-dark.png]]

## Props

| Name | Type | Default | Description |
|---|---|---|---|

## Slots

| Name | Description |
|---|---|

## Events

| Name | Payload | Description |
|---|---|---|

## Do

- [list of recommended usage patterns]

## Don't

- [list of anti-patterns]

## Source

- Code: [[wizardingcode-ui/resources/components/<Category>/<Name>.vue]]
- Histoire: [[Histoire/<Category>/<Name>]]
```
