# Vendor-Lock Discipline

Files derived from `nuxt-ui-templates/dashboard-vue` carry a `@vendor:` header pinning the upstream SHA:

```vue
<!-- @vendor: nuxt-ui-templates/dashboard-vue@<SHA> -->
```

## Rules

1. NEVER edit a `@vendor:` file without `/wc-vendor-upgrade` workflow (regra non-negotiable).
2. Path mappings live in `.arka/vendor.lock`.
3. Light WC additions inside vendor files are tolerated IF wrapped in WC markers:
   ```vue
   <!-- WC: ColorModeSwitcher (Plan 2 F1) — non-vendor addition -->
   <ColorModeSwitcher />
   <!-- /WC -->
   ```
4. Run `bin/wc-vendor-diff` before submitting PRs touching vendor files. Document any intentional divergence in the PR description.

## Currently vendor-locked (Plan 2 B2)

- Layouts/BackofficeLayout.vue (with WC additions for ColorModeSwitcher)
- All 8 Pages (Dashboard, Inbox, Customers, 4 Settings, SettingsLayout)
- 13 Components (Header, Home, Inbox, Customers, Settings)
- Composables/useDashboard.ts
- types/index.d.ts, utils/index.ts
- resources/css/app.css (partial — vendor base + WC additions)
