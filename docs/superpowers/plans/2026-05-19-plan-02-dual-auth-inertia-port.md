# Plan 2: Dual Auth & Inertia Port — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Implement the Dual Auth system (Staff/Fortify + Customer/Sanctum+Socialite, fully isolated guards) and the fiel port of `nuxt-ui-templates/dashboard-vue@HEAD` to Inertia v3 + Vue 3 + Nuxt UI 4 inside `resources/js/`. Bootstrap `packages/wizardingcode-ui` shared component library. Wire light/dark mode validation infra (Histoire + Playwright visual regression). All 4 vendor pages (Dashboard, Inbox, Customers, Settings) rendered, vendor-locked, and KB-documented in Obsidian.

**Architecture:** Two completely isolated auth systems (separate `users` and `customers` tables, separate guards `web`/`customer`, separate route files). Inertia v3 with SSR-safe color mode (cookie-persisted). The `wizardingcode-ui` package as a Composer path + Bun workspace member. Vendor files carry `@vendor:` headers; edits warn via the existing pre-tool-use-edit hook. Histoire renders each component in light+dark side by side; Playwright captures 3-viewport screenshots in CI.

**Tech Stack:** Laravel 13.9, Fortify ^1.37, Sanctum ^4.3, Socialite ^5.27, Spatie Permission ^6.25 (Staff only), Inertia v3 (inertiajs/inertia-laravel + @inertiajs/vue3), Vue 3.5, Nuxt UI 4, Tailwind 4, Pinia 2, Bun + Vite+, Histoire ^1, Pest 5 browser (Playwright), Vitest.

**Reference spec:** `docs/superpowers/specs/2026-05-19-boilerplate-wizardingcode-design.md` — §2.6 (dual auth), §6 (UI/UX foundation), §7.1 (Auth context), §7.3 (Backoffice context).

**Pre-conditions:** Plan 1 merged (or running on top of `feat/plan-01-foundation-arkaos`). `composer arka:gate` PASSED on Plan 1 boundary.

**Estimated duration:** ~2 weeks single-thread, ~1 week with backend (Paulo) + frontend (Ines) parallel.

---

## File Structure (lock decomposition here)

### Created in this plan

```
app/
├─ Models/
│  ├─ User.php                                        # MODIFIED — Staff Fortify-ready
│  └─ Customer.php                                    # NEW — Customer-side
├─ Http/
│  ├─ Controllers/
│  │  ├─ Auth/                                        # NEW (Staff)
│  │  │  ├─ LoginController.php                       # may be Fortify default
│  │  │  ├─ ImpersonateController.php                 # NEW
│  │  ├─ Api/V1/Auth/                                 # NEW (Customer)
│  │  │  ├─ RegisterController.php
│  │  │  ├─ LoginController.php
│  │  │  ├─ LogoutController.php
│  │  │  ├─ PasswordResetController.php
│  │  │  ├─ EmailVerificationController.php
│  │  │  └─ SocialController.php
│  ├─ Requests/
│  │  ├─ Auth/
│  │  │  ├─ Staff/{LoginRequest, ImpersonateRequest}.php
│  │  │  └─ Customer/{RegisterRequest, LoginRequest, PasswordResetRequest, EmailVerificationRequest}.php
│  ├─ Resources/
│  │  └─ Customer/{CustomerResource, CustomerTokenResource}.php
├─ Enums/
│  ├─ CustomerRole.php                                # Free | Pro | Enterprise
├─ Actions/
│  └─ Auth/
│     ├─ RegisterCustomer.php
│     ├─ AuthenticateCustomerSocial.php
│     ├─ ImpersonateStaff.php
├─ Policies/
│  ├─ UserPolicy.php                                  # Staff
│  └─ CustomerPolicy.php                              # Customer
└─ Providers/
   └─ FortifyServiceProvider.php                      # auth views, redirects, throttle

config/
├─ auth.php                                           # MODIFIED — add `customer` guard + provider
├─ fortify.php                                        # MODIFIED — features (2FA, email verify, password reset)
├─ sanctum.php                                        # MODIFIED — token abilities + expiration
└─ services.php                                       # MODIFIED — Socialite providers (google/apple/github/microsoft/facebook)

database/
├─ migrations/
│  ├─ 2026_XX_XX_add_two_factor_columns_to_users.php  # Fortify
│  ├─ 2026_XX_XX_create_customers_table.php           # NEW
│  ├─ 2026_XX_XX_create_customer_social_accounts.php  # NEW
│  ├─ 2026_XX_XX_create_customer_tokens.php           # NEW (Sanctum-shaped for customers guard)
├─ factories/
│  ├─ UserFactory.php                                 # MODIFIED — states: withTwoFactorEnabled, admin
│  └─ CustomerFactory.php                             # NEW — states: fromSocial, pro, enterprise
└─ seeders/
   ├─ RolePermissionSeeder.php                        # NEW — Staff roles+permissions
   └─ CustomerRoleSeeder.php                          # NEW — no-op (enum, not DB)

routes/
├─ web.php                                            # MODIFIED — admin prefix + Inertia welcome
├─ auth.php                                           # NEW — Fortify routes for Staff
├─ api/
│  └─ v1.php                                          # NEW — customer auth + token mgmt

resources/
├─ js/                                                # NEW — Inertia v3 + Vue 3 + Nuxt UI 4
│  ├─ app.ts                                          # bootstrap (Inertia + Nuxt UI plugin + Pinia)
│  ├─ ssr.ts                                          # SSR entry
│  ├─ Layouts/
│  │  ├─ BackofficeLayout.vue                         # [VENDOR] port of src/layouts/default.vue
│  │  ├─ AuthLayout.vue                               # NEW (Staff login/2FA pages)
│  │  └─ GuestLayout.vue                              # NEW (public pages, password reset)
│  ├─ Pages/
│  │  ├─ Dashboard/Index.vue                          # [VENDOR] port of src/pages/index.vue
│  │  ├─ Inbox/Index.vue                              # [VENDOR] port of src/pages/inbox.vue
│  │  ├─ Customers/Index.vue                          # [VENDOR] port of src/pages/customers.vue
│  │  ├─ Settings/{Index,Members,Notifications,Security}.vue
│  │  └─ Auth/{Login, TwoFactorChallenge, ForgotPassword, ResetPassword, ConfirmPassword, VerifyEmail}.vue
│  ├─ Components/
│  │  ├─ Backoffice/Header/{UserMenu, TeamsMenu, NotificationsSlideover}.vue   # [VENDOR]
│  │  ├─ Home/{Chart, DateRangePicker, PeriodSelect, Sales, Stats}.vue        # [VENDOR]
│  │  ├─ Inbox/{List, Mail}.vue                                                # [VENDOR]
│  │  ├─ Customers/{AddModal, DeleteModal}.vue                                 # [VENDOR]
│  │  └─ Settings/MembersList.vue                                              # [VENDOR]
│  ├─ Composables/
│  │  ├─ useDashboard.ts                              # [VENDOR] port of src/composables/useDashboard.ts
│  │  └─ useColorMode.ts                              # NEW — SSR-safe cookie-persisted
│  ├─ Stores/
│  │  └─ useAuthStore.ts                              # NEW — Pinia, exposes user/customer + perms
│  ├─ types/index.d.ts                                # [VENDOR] port
│  ├─ utils/index.ts                                  # [VENDOR] port
│  └─ assets/css/main.css                             # [VENDOR] port (Tailwind 4 + Nuxt UI tokens)
├─ views/
│  ├─ app.blade.php                                   # NEW — Inertia root
│  └─ emails/                                         # NEW — Fortify email templates (welcome, reset, verify)

packages/
└─ wizardingcode-ui/                                  # NEW package
   ├─ composer.json                                   # ServiceProvider, version 1.0.0
   ├─ package.json                                    # Bun workspace member, Vue exports
   ├─ README.md
   ├─ src/
   │  └─ WizardingCodeUiServiceProvider.php           # registers Blade Inertia stub if needed
   ├─ resources/
   │  ├─ components/
   │  │  ├─ Forms/{WcDropzone, WcInput, WcSelect, WcCheckbox, WcDateRange}.vue
   │  │  ├─ Data/{WcDataTable, WcMasterDetail, WcEmptyState}.vue
   │  │  ├─ Feedback/{WcConfirmModal, WcToast}.vue
   │  │  └─ Layout/{WcPageHeader, WcStatsGrid}.vue
   │  ├─ composables/{useTheme, useFormErrors, useToast}.ts
   │  └─ types/index.d.ts
   └─ tests/                                          # Vitest

app.config.ts                                          # NEW — Nuxt UI tokens (primary, neutral, semantic)
vite.config.ts                                         # MODIFIED — Inertia plugin, Nuxt UI plugin
package.json                                           # MODIFIED — Vue, Inertia, Nuxt UI, Pinia, Histoire, Vitest deps
tsconfig.json                                          # NEW — Vue 3 + Inertia paths

histoire.config.ts                                     # NEW — Histoire config (light+dark, 3 viewports)
.histoire/                                             # NEW — Histoire build output (gitignored)

tests/
├─ Feature/
│  └─ Auth/
│     ├─ Staff/{LoginTest, TwoFactorChallengeTest, ImpersonateTest, PasswordResetTest}.php
│     └─ Customer/{RegisterTest, LoginTest, SocialLoginTest, PasswordResetTest, EmailVerificationTest}.php
├─ Browser/
│  └─ Auth/
│     ├─ StaffLoginFlow.php                            # 3 viewports
│     └─ CustomerRegisterFlow.php                      # 3 viewports
└─ Visual/                                             # NEW — Playwright visual regression
   ├─ playwright.config.ts
   └─ light-dark.spec.ts                               # captures Storybook stories light+dark

resources/js/**/*.test.ts                              # NEW — Vitest component tests

bin/
└─ wc-vendor-diff                                      # NEW — diff vendor files vs upstream SHA
└─ wc-vendor-upgrade                                   # NEW — interactive vendor upgrade

.claude/hooks/
└─ pre-component-create.sh                             # NEW — blocks new component without KB Obsidian note

.claude/skills/inertia-vue-nuxtui/                     # MODIFIED — full content (from Plan 1 skeleton)
.claude/skills/pest-browser-tdd/                       # MODIFIED — full content (from Plan 1 skeleton)
.claude/skills/wizardingcode-ui-kb/                    # MODIFIED — full content (from Plan 1 skeleton)

.github/workflows/                                     # NEW — first CI workflows
└─ pr-checks.yml                                       # arka:gate + visual regression on PR
```

---

## Phase A — Inertia + Vue + Nuxt UI bootstrap (frontend foundation)

### Task A1: Install JS deps + tsconfig + vite plugins

**Files:** `package.json`, `tsconfig.json`, `vite.config.ts`, `resources/views/app.blade.php`

- [ ] **Step 1: Add JS dependencies via Bun**

```bash
bun add @inertiajs/vue3@^2.0 vue@^3.5 pinia@^2.2
bun add @nuxt/ui@^4.0
bun add laravel-vite-plugin@^3.1
bun add -d @vitejs/plugin-vue typescript@^5.6 @vue/tsc histoire@^1 @histoire/plugin-vue
bun add -d vitest@latest @vue/test-utils@^2.4 @testing-library/vue@^8
```

- [ ] **Step 2: Write tsconfig.json**

```json
{
    "compilerOptions": {
        "target": "ESNext",
        "module": "ESNext",
        "moduleResolution": "Bundler",
        "strict": true,
        "noImplicitAny": true,
        "esModuleInterop": true,
        "resolveJsonModule": true,
        "isolatedModules": true,
        "jsx": "preserve",
        "lib": ["ESNext", "DOM", "DOM.Iterable"],
        "skipLibCheck": true,
        "baseUrl": ".",
        "paths": {
            "@/*": ["resources/js/*"],
            "@wizardingcode/ui": ["packages/wizardingcode-ui/resources/components/index.ts"]
        }
    },
    "include": ["resources/js/**/*.ts", "resources/js/**/*.vue", "packages/wizardingcode-ui/**/*.ts", "packages/wizardingcode-ui/**/*.vue"],
    "exclude": ["node_modules", "vendor", "dist", "public/build"]
}
```

- [ ] **Step 3: Update vite.config.ts to include Vue + Inertia plugin**

```ts
import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import path from 'node:path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.ts', 'resources/css/main.css'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
            '@wizardingcode/ui': path.resolve(__dirname, 'packages/wizardingcode-ui/resources/components'),
        },
    },
});
```

- [ ] **Step 4: Write resources/views/app.blade.php (Inertia root)**

```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="@if(request()->cookie('color-mode') === 'dark') dark @endif">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title inertia>{{ config('app.name', 'WizardingCode') }}</title>
    @routes
    @vite(['resources/js/app.ts', 'resources/css/main.css'])
    @inertiaHead
</head>
<body class="font-sans antialiased">
    @inertia
</body>
</html>
```

- [ ] **Step 5: Run `bun install && bun run build`**, verify no errors. Commit.

```bash
git add package.json bun.lock tsconfig.json vite.config.ts resources/views/app.blade.php
git commit -m "feat(frontend): install Inertia v3 + Vue 3 + Nuxt UI 4 + Histoire + Vitest"
```

---

### Task A2: Write resources/js/app.ts + ssr.ts + bootstrap

**Files:** `resources/js/app.ts`, `resources/js/ssr.ts`, `resources/css/main.css` (initial)

- [ ] **Step 1: Write resources/js/app.ts**

```ts
import './bootstrap';
import '../css/main.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h, DefineComponent } from 'vue';
import { createPinia } from 'pinia';
import ui from '@nuxt/ui/vue-plugin';

const appName = import.meta.env.VITE_APP_NAME || 'WizardingCode';

createInertiaApp({
    title: (title) => `${title} · ${appName}`,
    resolve: (name) =>
        resolvePageComponent<DefineComponent>(`./Pages/${name}.vue`, import.meta.glob<DefineComponent>('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });
        app.use(plugin);
        app.use(createPinia());
        app.use(ui);
        app.mount(el);
    },
    progress: { color: '#7c3aed' },
});
```

- [ ] **Step 2: Write resources/js/ssr.ts**

```ts
import { createInertiaApp } from '@inertiajs/vue3';
import createServer from '@inertiajs/vue3/server';
import { renderToString } from '@vue/server-renderer';
import { createSSRApp, h, DefineComponent } from 'vue';

const appName = import.meta.env.VITE_APP_NAME || 'WizardingCode';

createServer((page) =>
    createInertiaApp({
        page,
        render: renderToString,
        title: (title) => `${title} · ${appName}`,
        resolve: (name) => {
            const pages = import.meta.glob<DefineComponent>('./Pages/**/*.vue', { eager: true });
            return pages[`./Pages/${name}.vue`];
        },
        setup: ({ App, props, plugin }) => {
            return createSSRApp({ render: () => h(App, props) }).use(plugin);
        },
    }),
);
```

- [ ] **Step 3: Write resources/js/bootstrap.ts**

```ts
import axios from 'axios';

declare global {
    interface Window {
        axios: typeof axios;
    }
}

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
```

- [ ] **Step 4: Write resources/css/main.css (port from upstream src/assets/css/main.css)**

Fetch upstream content via:
```bash
gh api repos/nuxt-ui-templates/dashboard-vue/contents/src/assets/css/main.css --jq '.content' | base64 -d > resources/css/main.css
```

Then verify the file imports tailwind and nuxt-ui. Add a vendor header at top:

```css
/*! @vendor: nuxt-ui-templates/dashboard-vue@<sha> — do not edit. Use /wc-vendor-upgrade to update. */
```

(Capture the upstream HEAD SHA via `gh api repos/nuxt-ui-templates/dashboard-vue | jq -r .default_branch` then resolve.)

- [ ] **Step 5: Build to verify**: `bun run build`. Should compile without errors.

- [ ] **Step 6: Commit**

```bash
git add resources/js/ resources/css/
git commit -m "feat(frontend): scaffold Inertia v3 app.ts/ssr.ts/bootstrap + Tailwind+NuxtUI main.css (vendor)"
```

---

### Task A3: Write app.config.ts (Nuxt UI tokens)

**Files:** `app.config.ts`

- [ ] **Step 1: Write app.config.ts with semantic tokens**

```ts
export default defineAppConfig({
    ui: {
        colors: {
            primary: 'violet',
            neutral: 'slate',
        },
        button: {
            slots: {
                base: 'font-medium',
            },
        },
    },
});
```

NOTE: Final palette per Plan 1 §11.1.5 — Valentina decides. Placeholder `violet + slate` chosen for v1.0-draft.

- [ ] **Step 2: Verify Nuxt UI picks up the config in dev mode**

```bash
bun run dev &
sleep 5
curl -s http://localhost:5173 | head -20
killall bun 2>/dev/null
```

- [ ] **Step 3: Commit**

```bash
git add app.config.ts
git commit -m "feat(frontend): add app.config.ts with primary=violet + neutral=slate (placeholder palette)"
```

---

## Phase B — Vendor Port of Nuxt UI Dashboard

### Task B1: Capture upstream SHA + write vendor diff/upgrade commands

**Files:** `bin/wc-vendor-diff`, `bin/wc-vendor-upgrade`, `.arka/vendor.lock`

- [ ] **Step 1: Capture upstream SHA**

```bash
UPSTREAM_SHA=$(gh api repos/nuxt-ui-templates/dashboard-vue/commits/HEAD --jq .sha)
echo "Upstream SHA: $UPSTREAM_SHA"
```

- [ ] **Step 2: Write .arka/vendor.lock**

```yaml
vendor:
  nuxt-ui-templates/dashboard-vue:
    sha: <UPSTREAM_SHA>
    locked_at: 2026-05-19
    locked_by: ines-frontend
    files:
      - resources/js/Layouts/BackofficeLayout.vue
      - resources/js/Pages/Dashboard/Index.vue
      - resources/js/Pages/Inbox/Index.vue
      - resources/js/Pages/Customers/Index.vue
      - resources/js/Pages/Settings/Index.vue
      - resources/js/Pages/Settings/Members.vue
      - resources/js/Pages/Settings/Notifications.vue
      - resources/js/Pages/Settings/Security.vue
      - resources/js/Components/Backoffice/Header/UserMenu.vue
      - resources/js/Components/Backoffice/Header/TeamsMenu.vue
      - resources/js/Components/Backoffice/Header/NotificationsSlideover.vue
      - resources/js/Components/Home/Chart.vue
      - resources/js/Components/Home/DateRangePicker.vue
      - resources/js/Components/Home/PeriodSelect.vue
      - resources/js/Components/Home/Sales.vue
      - resources/js/Components/Home/Stats.vue
      - resources/js/Components/Inbox/List.vue
      - resources/js/Components/Inbox/Mail.vue
      - resources/js/Components/Customers/AddModal.vue
      - resources/js/Components/Customers/DeleteModal.vue
      - resources/js/Components/Settings/MembersList.vue
      - resources/js/Composables/useDashboard.ts
      - resources/js/types/index.d.ts
      - resources/js/utils/index.ts
      - resources/css/main.css
```

- [ ] **Step 3: Write bin/wc-vendor-diff**

```bash
#!/usr/bin/env bash
# Diff local vendor-locked files vs upstream nuxt-ui-templates/dashboard-vue.
set -euo pipefail

LOCKFILE=".arka/vendor.lock"
SHA=$(grep '^    sha:' "$LOCKFILE" | head -1 | awk '{print $2}')
TMPDIR=$(mktemp -d)
trap "rm -rf $TMPDIR" EXIT

echo "Comparing local vendor against upstream@$SHA"
gh repo clone nuxt-ui-templates/dashboard-vue "$TMPDIR/upstream" -- --depth=1 --branch=main >/dev/null 2>&1
cd "$TMPDIR/upstream" && git checkout "$SHA" >/dev/null 2>&1 && cd - >/dev/null

# Map each locked file to its upstream path
declare -A MAP=(
    ["resources/js/Layouts/BackofficeLayout.vue"]="src/layouts/default.vue"
    ["resources/js/Pages/Dashboard/Index.vue"]="src/pages/index.vue"
    ["resources/js/Pages/Inbox/Index.vue"]="src/pages/inbox.vue"
    ["resources/js/Pages/Customers/Index.vue"]="src/pages/customers.vue"
    ["resources/js/Pages/Settings/Index.vue"]="src/pages/settings/index.vue"
    ["resources/js/Pages/Settings/Members.vue"]="src/pages/settings/members.vue"
    ["resources/js/Pages/Settings/Notifications.vue"]="src/pages/settings/notifications.vue"
    ["resources/js/Pages/Settings/Security.vue"]="src/pages/settings/security.vue"
    ["resources/js/Components/Backoffice/Header/UserMenu.vue"]="src/components/UserMenu.vue"
    ["resources/js/Components/Backoffice/Header/TeamsMenu.vue"]="src/components/TeamsMenu.vue"
    ["resources/js/Components/Backoffice/Header/NotificationsSlideover.vue"]="src/components/NotificationsSlideover.vue"
    ["resources/js/Components/Home/Chart.vue"]="src/components/home/HomeChart.vue"
    ["resources/js/Components/Home/DateRangePicker.vue"]="src/components/home/HomeDateRangePicker.vue"
    ["resources/js/Components/Home/PeriodSelect.vue"]="src/components/home/HomePeriodSelect.vue"
    ["resources/js/Components/Home/Sales.vue"]="src/components/home/HomeSales.vue"
    ["resources/js/Components/Home/Stats.vue"]="src/components/home/HomeStats.vue"
    ["resources/js/Components/Inbox/List.vue"]="src/components/inbox/InboxList.vue"
    ["resources/js/Components/Inbox/Mail.vue"]="src/components/inbox/InboxMail.vue"
    ["resources/js/Components/Customers/AddModal.vue"]="src/components/customers/CustomersAddModal.vue"
    ["resources/js/Components/Customers/DeleteModal.vue"]="src/components/customers/CustomersDeleteModal.vue"
    ["resources/js/Components/Settings/MembersList.vue"]="src/components/settings/SettingsMembersList.vue"
    ["resources/js/Composables/useDashboard.ts"]="src/composables/useDashboard.ts"
    ["resources/js/types/index.d.ts"]="src/types/index.d.ts"
    ["resources/js/utils/index.ts"]="src/utils/index.ts"
    ["resources/css/main.css"]="src/assets/css/main.css"
)

DIFF_COUNT=0
for LOCAL in "${!MAP[@]}"; do
    UPSTREAM="${MAP[$LOCAL]}"
    if [ ! -f "$LOCAL" ]; then continue; fi
    if [ ! -f "$TMPDIR/upstream/$UPSTREAM" ]; then continue; fi
    # Strip vendor headers from local before diffing
    LOCAL_CONTENT=$(grep -v '@vendor:' "$LOCAL")
    UPSTREAM_CONTENT=$(cat "$TMPDIR/upstream/$UPSTREAM")
    if [ "$LOCAL_CONTENT" != "$UPSTREAM_CONTENT" ]; then
        echo "≠ $LOCAL (vs $UPSTREAM)"
        DIFF_COUNT=$((DIFF_COUNT + 1))
    fi
done

if [ "$DIFF_COUNT" -eq 0 ]; then
    echo "All vendor files match upstream@$SHA."
else
    echo
    echo "$DIFF_COUNT file(s) differ. Run /wc-vendor-upgrade to apply upstream changes."
fi
```

- [ ] **Step 4: Make executable + commit**

```bash
chmod +x bin/wc-vendor-diff
git add bin/wc-vendor-diff .arka/vendor.lock
git commit -m "feat(vendor): add wc-vendor-diff + .arka/vendor.lock (upstream SHA pin)"
```

---

### Task B2: Port the 4 page-types (Dashboard, Inbox, Customers, Settings)

**Files:** 8 .vue Pages + 16 .vue components + useDashboard.ts + types + utils

- [ ] **Step 1: Fetch each upstream file via `gh api repos/nuxt-ui-templates/dashboard-vue/contents/<path> --jq .content | base64 -d`** and write to the local mapped path.

Loop through the 25 files declared in `.arka/vendor.lock`. For each:
1. Fetch the upstream content.
2. Prepend the vendor header: `<!-- @vendor: nuxt-ui-templates/dashboard-vue@<SHA> -->`.
3. Convert any `definePageMeta` or vue-router specifics to Inertia equivalents.
4. Adapt imports: `vue-router` calls → `@inertiajs/vue3` (`router.visit`, `usePage`).
5. Write to local destination.

This is a multi-hour mechanical port. Recommend breaking into smaller sub-tasks per page-type during execution. For each port:
- Read upstream.
- Identify Inertia-specific adaptations.
- Write local file with vendor header.
- Run `bun run build` to verify compilation.
- Commit per page-type.

Suggested commit structure:
- `feat(vendor): port BackofficeLayout + useDashboard.ts (vendor lock)`
- `feat(vendor): port Dashboard page + 5 home components (vendor lock)`
- `feat(vendor): port Inbox page + List + Mail components (vendor lock)`
- `feat(vendor): port Customers page + AddModal + DeleteModal (vendor lock)`
- `feat(vendor): port Settings index + 3 subpages + MembersList + header components (vendor lock)`
- `feat(vendor): port types + utils (vendor lock)`

- [ ] **Step 2: After each port, run `bun run build`** and `bin/wc-vendor-diff` to verify the file matches upstream (with just the vendor header added).

- [ ] **Step 3: Wire pages into Laravel routes (routes/web.php)**

```php
Route::middleware(['auth:web', 'verified'])->prefix('admin')->group(function (): void {
    Route::inertia('/', 'Dashboard/Index')->name('admin.dashboard');
    Route::inertia('/inbox', 'Inbox/Index')->name('admin.inbox');
    Route::inertia('/customers', 'Customers/Index')->name('admin.customers');

    Route::prefix('settings')->name('admin.settings.')->group(function (): void {
        Route::inertia('/', 'Settings/Index')->name('index');
        Route::inertia('/members', 'Settings/Members')->name('members');
        Route::inertia('/notifications', 'Settings/Notifications')->name('notifications');
        Route::inertia('/security', 'Settings/Security')->name('security');
    });
});
```

- [ ] **Step 4: Run `composer arka:gate`** to confirm green (browser tests still empty — Plan 2 adds them next).

---

## Phase C — Dual Auth Backend (Staff Fortify)

### Task C1: User model + Fortify migrations + roles seeder

**Files:** `app/Models/User.php`, `database/migrations/*two_factor*`, `database/factories/UserFactory.php`, `database/seeders/RolePermissionSeeder.php`, `config/auth.php`, `config/fortify.php`

- [ ] **Step 1: Modify User model — add HasRoles + TwoFactorAuthenticatable + final**

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

final class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use Notifiable;
    use TwoFactorAuthenticatable;

    protected $guard_name = 'web';

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }
}
```

- [ ] **Step 2: Configure config/auth.php** — keep `web` guard. (Customer guard added in Task D1.)

- [ ] **Step 3: Configure config/fortify.php** — enable: 2FA TOTP, password reset, email verification, register OFF (we register staff via seeder/invite), update profile/password. Set guard `web`, prefix `/admin`, home `/admin`.

- [ ] **Step 4: Run Fortify migrations**

```bash
php artisan migrate
```

- [ ] **Step 5: Update UserFactory with states**

```php
public function withTwoFactorEnabled(): static
{
    return $this->state(fn () => [
        'two_factor_secret' => encrypt('SAMPLEBASE32SECRET'),
        'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2', 'code3'])),
        'two_factor_confirmed_at' => now(),
    ]);
}

public function admin(): static
{
    return $this->afterCreating(fn (User $u) => $u->assignRole('super-admin'));
}
```

- [ ] **Step 6: Write RolePermissionSeeder** — create roles `super-admin`, `admin`, `editor`, `viewer` with sensible Spatie permissions.

- [ ] **Step 7: Pest tests** — `tests/Feature/Auth/Staff/LoginTest.php`, `TwoFactorChallengeTest.php`. Cover: login rate limit 5/min, 2FA flow, recovery code consumption, logout.

- [ ] **Step 8: Run composer arka:gate, commit per logical chunk.**

---

### Task C2: Fortify views → Inertia (Login, 2FA, Password Reset)

**Files:** `app/Providers/FortifyServiceProvider.php`, `resources/js/Pages/Auth/*.vue`

- [ ] **Step 1: Register Fortify view callbacks → Inertia::render**

```php
Fortify::loginView(fn () => Inertia::render('Auth/Login'));
Fortify::twoFactorChallengeView(fn () => Inertia::render('Auth/TwoFactorChallenge'));
Fortify::requestPasswordResetLinkView(fn () => Inertia::render('Auth/ForgotPassword'));
Fortify::resetPasswordView(fn (Request $r) => Inertia::render('Auth/ResetPassword', ['token' => $r->route('token'), 'email' => $r->email]));
Fortify::verifyEmailView(fn () => Inertia::render('Auth/VerifyEmail'));
Fortify::confirmPasswordView(fn () => Inertia::render('Auth/ConfirmPassword'));
```

- [ ] **Step 2: Write each Vue Page using Nuxt UI form components** (UForm, UFormField, UInput, UButton). Use `WcEmptyState` and toast for feedback.

Page templates are extensive — break into one task per page during execution.

- [ ] **Step 3: Add AuthLayout.vue and GuestLayout.vue.**

- [ ] **Step 4: E2E Pest browser test for full Staff login flow with 2FA.**

- [ ] **Step 5: Commit.**

---

### Task C3: Impersonate (admin only)

**Files:** `app/Actions/Auth/ImpersonateStaff.php`, `app/Http/Controllers/Auth/ImpersonateController.php`, `app/Http/Requests/Auth/Staff/ImpersonateRequest.php`, `routes/auth.php`

- [ ] **Step 1: Implement ImpersonateStaff Action** — stores original_id in session, logs activity via spatie/activitylog.

- [ ] **Step 2: Controller + Route** — `POST /admin/impersonate/{user}`, `DELETE /admin/impersonate` (leave).

- [ ] **Step 3: Policy** — only super-admin can impersonate.

- [ ] **Step 4: Tests** — happy path, authorization failure, audit log assertion.

- [ ] **Step 5: Commit.**

---

## Phase D — Dual Auth Backend (Customer Sanctum + Socialite)

### Task D1: Customer table + model + guard + role enum

**Files:** `database/migrations/*create_customers*`, `app/Models/Customer.php`, `app/Enums/CustomerRole.php`, `config/auth.php`

- [ ] **Step 1: Write migration `create_customers_table`**

```php
Schema::create('customers', function (Blueprint $t): void {
    $t->id();
    $t->string('name');
    $t->string('email')->unique();
    $t->string('password')->nullable(); // nullable for social-only accounts
    $t->string('avatar_url')->nullable();
    $t->string('role')->default('free'); // CustomerRole enum value
    $t->timestamp('last_login_at')->nullable();
    $t->timestamp('email_verified_at')->nullable();
    $t->rememberToken();
    $t->timestamps();
    $t->softDeletes();
    $t->index(['email', 'deleted_at']);
});
```

- [ ] **Step 2: Write CustomerRole enum**

```php
<?php

declare(strict_types=1);

namespace App\Enums;

enum CustomerRole: string
{
    case Free = 'free';
    case Pro = 'pro';
    case Enterprise = 'enterprise';

    public function label(): string
    {
        return match ($this) {
            self::Free => 'Free',
            self::Pro => 'Pro',
            self::Enterprise => 'Enterprise',
        };
    }
}
```

- [ ] **Step 3: Write Customer model**

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CustomerRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

final class Customer extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    protected $guard_name = 'customer';

    protected $fillable = ['name', 'email', 'password', 'avatar_url', 'role'];

    protected $hidden = ['password', 'remember_token'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'role' => CustomerRole::class,
        ];
    }
}
```

- [ ] **Step 4: Add `customer` guard to config/auth.php**

```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
    'customer' => [
        'driver' => 'sanctum',
        'provider' => 'customers',
    ],
],

'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model' => App\Models\User::class,
    ],
    'customers' => [
        'driver' => 'eloquent',
        'model' => App\Models\Customer::class,
    ],
],
```

- [ ] **Step 5: Write CustomerFactory with states (fromSocial, pro, enterprise).**

- [ ] **Step 6: Pest unit tests for Customer model + factory.**

- [ ] **Step 7: composer arka:gate, commit.**

---

### Task D2: Customer API endpoints (register, login, logout, password reset, verify, social)

**Files:** `routes/api/v1.php`, `app/Http/Controllers/Api/V1/Auth/*`, `app/Http/Requests/Auth/Customer/*`, `app/Http/Resources/Customer/*`, `app/Actions/Auth/*`

- [ ] **Step 1: Write routes/api/v1.php**

```php
<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\Auth as Auth;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::post('register', Auth\RegisterController::class)->middleware('throttle:5,1');
    Route::post('login', Auth\LoginController::class)->middleware('throttle:5,1');
    Route::post('password/forgot', [Auth\PasswordResetController::class, 'requestReset'])->middleware('throttle:3,1');
    Route::post('password/reset', [Auth\PasswordResetController::class, 'resetPassword'])->middleware('throttle:5,1');
    Route::post('verify-email/{id}/{hash}', Auth\EmailVerificationController::class)->middleware('signed', 'throttle:6,1');
    Route::get('social/{provider}', [Auth\SocialController::class, 'redirect'])->middleware('throttle:10,1');
    Route::post('social/{provider}/callback', [Auth\SocialController::class, 'callback'])->middleware('throttle:10,1');
});

Route::middleware('auth:customer')->prefix('auth')->group(function (): void {
    Route::post('logout', Auth\LogoutController::class);
});
```

- [ ] **Step 2-N: Each controller is a single-action invokable. Each takes a typed FormRequest. Each returns CustomerResource (or 204 for logout).**

This is a multi-task chunk — break per endpoint. Each task:
1. Write FormRequest with validation rules + authorize.
2. Write controller (invokable, single-action).
3. Write Action class for business logic.
4. Wire OpenAPI annotations (Scramble).
5. Feature test (happy path + 3 edge cases incl. rate limit, validation errors, conflict).

Suggested order:
- Register
- Login
- Logout
- Password forgot/reset
- Email verification
- Social login (Google first, then config-driven for the others)

- [ ] **Step N+1: composer arka:gate + commit per endpoint.**

---

### Task D3: Socialite multi-provider integration

**Files:** `app/Actions/Auth/AuthenticateCustomerSocial.php`, `app/Http/Controllers/Api/V1/Auth/SocialController.php`, `config/services.php`, `database/migrations/*customer_social_accounts*`

- [ ] **Step 1: Migration for customer_social_accounts (provider, provider_id, customer_id, provider_token, provider_refresh_token).**

- [ ] **Step 2: Configure config/services.php with placeholders for google/apple/github/microsoft/facebook (read API keys from app_settings if available — Plan 3 wires dynamic settings; until then, env fallback).**

- [ ] **Step 3: SocialController::redirect — returns Socialite driver redirect URL.**

- [ ] **Step 4: SocialController::callback — calls AuthenticateCustomerSocial action.**

- [ ] **Step 5: AuthenticateCustomerSocial — finds or creates Customer, links provider account, issues Sanctum token.**

- [ ] **Step 6: Feature tests with Socialite mocking.**

- [ ] **Step 7: composer arka:gate + commit.**

---

## Phase E — wizardingcode-ui Shared Package

### Task E1: Scaffold wizardingcode-ui package

**Files:** `packages/wizardingcode-ui/composer.json`, `package.json`, `README.md`, `src/WizardingCodeUiServiceProvider.php`, `resources/components/index.ts`, `tests/`

- [ ] **Step 1: Scaffold structure** (analogous to wizardingcode-arka-bridge from Plan 1).

- [ ] **Step 2: Write components/index.ts as the public Vue exports.**

- [ ] **Step 3: Write each Wc* component (10 components):**

   Forms: WcDropzone, WcInput, WcSelect, WcCheckbox, WcDateRange.
   Data: WcDataTable, WcMasterDetail, WcEmptyState.
   Feedback: WcConfirmModal, WcToast.
   Layout: WcPageHeader, WcStatsGrid.

   Each component must:
   - Have a Vendor header IF derived from upstream (most are new — no header).
   - Use semantic tokens only.
   - Support light + dark.
   - Respect useReducedMotion.
   - Have Histoire story.
   - Have Vitest unit test.

- [ ] **Step 4: Register the package in root composer.json + package.json + tsconfig paths.**

- [ ] **Step 5: composer arka:gate + commit.**

---

### Task E2: KB Obsidian — create UI/UX library notes for each component

**Files:** `Projects/Boilerplate WizardingCode/UI-UX/Components/**/*.md` (in Obsidian vault)

This task is performed against the user's Obsidian vault (path from OBSIDIAN_VAULT_PATH env). May require Obsidian MCP.

- [ ] **Step 1: For each of the 10 new Wc* components, create an Obsidian note with: props, slots, events, screenshots (light + dark), do/don't.**

- [ ] **Step 2: For each ported vendor component (16 total), create an Obsidian note with vendor reference + screenshots.**

- [ ] **Step 3: Create MOC pages: `Theme Reference`, `Patterns`, `Heuristics`.**

This is a content task largely owned by Valentina (Brand). Output stored outside the git repo (in Obsidian vault). The skill `wizardingcode-ui-kb` enforces presence (Plan 1 skeleton, Plan 2 full).

---

## Phase F — Color Mode + Visual Regression

### Task F1: useColorMode (SSR-safe)

**Files:** `resources/js/Composables/useColorMode.ts`, `resources/views/app.blade.php` (verify cookie read)

- [ ] **Step 1: Implement useColorMode composable**

```ts
import { ref, watch, onMounted } from 'vue';

type Mode = 'light' | 'dark' | 'system';

export function useColorMode() {
    const mode = ref<Mode>('system');
    const resolved = ref<'light' | 'dark'>('light');

    onMounted(() => {
        const cookie = document.cookie.split('; ').find((row) => row.startsWith('color-mode='));
        if (cookie) {
            mode.value = cookie.split('=')[1] as Mode;
        }
        applyMode(mode.value);
    });

    function applyMode(m: Mode): void {
        const dark = m === 'dark' || (m === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
        resolved.value = dark ? 'dark' : 'light';
        document.documentElement.classList.toggle('dark', dark);
    }

    function setMode(m: Mode): void {
        mode.value = m;
        document.cookie = `color-mode=${m}; path=/; max-age=${60 * 60 * 24 * 365}; samesite=lax`;
        applyMode(m);
    }

    watch(mode, applyMode);

    return { mode, resolved, setMode };
}
```

- [ ] **Step 2: Verify Blade root reads the cookie** (already declared in Task A1).

- [ ] **Step 3: Wire color mode switcher into BackofficeLayout header.**

- [ ] **Step 4: Vitest test + Pest browser test (3 viewports).**

- [ ] **Step 5: composer arka:gate + commit.**

---

### Task F2: Histoire setup (component dev environment)

**Files:** `histoire.config.ts`, `resources/js/Components/**/*.story.vue`, `packages/wizardingcode-ui/resources/components/**/*.story.vue`

- [ ] **Step 1: Write histoire.config.ts**

```ts
import { defineConfig } from 'histoire';
import { HstVue } from '@histoire/plugin-vue';

export default defineConfig({
    plugins: [HstVue()],
    setupFile: 'resources/js/histoire.setup.ts',
    storyMatch: [
        'resources/js/**/*.story.vue',
        'packages/wizardingcode-ui/resources/components/**/*.story.vue',
    ],
    backgroundPresets: [
        { label: 'Light', color: '#ffffff', contrastColor: '#0f172a' },
        { label: 'Dark', color: '#0f172a', contrastColor: '#ffffff' },
    ],
    responsivePresets: [
        { label: 'Mobile', width: 375, height: 800 },
        { label: 'Tablet', width: 820, height: 1180 },
        { label: 'Desktop', width: 1440, height: 900 },
    ],
});
```

- [ ] **Step 2: Write Histoire setup with Nuxt UI plugin + Pinia.**

- [ ] **Step 3: Write 1 .story.vue per component (vendor + Wc*).**

- [ ] **Step 4: Verify `bunx histoire dev` opens cleanly. Verify `bunx histoire build` produces output.**

- [ ] **Step 5: Add `.histoire/` to .gitignore.**

- [ ] **Step 6: composer arka:gate + commit.**

---

### Task F3: Playwright visual regression (light+dark, 3 viewports)

**Files:** `tests/Visual/playwright.config.ts`, `tests/Visual/light-dark.spec.ts`

- [ ] **Step 1: Set up Playwright config** with 3 viewport projects × 2 color modes.

- [ ] **Step 2: Write spec that visits Histoire stories and captures screenshots per (viewport, mode) combo.**

- [ ] **Step 3: First run generates baseline. Subsequent CI runs compare and fail if diff > 2%.**

- [ ] **Step 4: Add visual regression as a non-required phase in bin/arka-gate** (or in CI workflow only).

- [ ] **Step 5: composer arka:gate + commit.**

---

## Phase G — Tests + Quality Gate Reactivation

### Task G1: Pest browser E2E tests for auth flows

**Files:** `tests/Browser/Auth/StaffLoginFlow.php`, `tests/Browser/Auth/CustomerRegisterFlow.php`

- [ ] **Step 1: Staff full login flow** — visit /admin → login form → fill credentials → 2FA challenge → recovery code → dashboard.

- [ ] **Step 2: Customer register flow** — POST /api/v1/auth/register (API test, not browser) + the equivalent web flow if exposed.

- [ ] **Step 3: 3 viewports** for both.

- [ ] **Step 4: a11y assertion** via axe-core/playwright on each page.

- [ ] **Step 5: composer arka:gate + commit.**

---

### Task G2: Reactivate Infection MSI thresholds

**Files:** `infection.json5`

- [ ] **Step 1: Set minMsi: 75, minCoveredMsi: 85.**

- [ ] **Step 2: Run `vendor/bin/infection` locally (needs pcov/xdebug — install if missing or accept CI-only).**

- [ ] **Step 3: If MSI < 75, add tests until passing.**

- [ ] **Step 4: composer arka:gate + commit.**

---

### Task G3: Full skills content (Inertia/Vue/NuxtUI + Pest Browser + UI-KB)

**Files:** `.agents/skills/inertia-vue-nuxtui/**`, `.agents/skills/pest-browser-tdd/**`, `.agents/skills/wizardingcode-ui-kb/**`

- [ ] **Step 1: Write full content for each skill** (replacing Plan 1 skeletons). Rules per skill.

- [ ] **Step 2: Run `bin/arka-sync-agents`. Commit.**

---

## Phase H — Pre-Component-Create Hook + Final Gate + PR

### Task H1: pre-component-create.sh hook

**Files:** `.claude/hooks/pre-component-create.sh`, `.claude/settings.json` (register matcher)

- [ ] **Step 1: Write the hook script** — blocks creation of a new file under `resources/js/Components/` or `packages/wizardingcode-ui/resources/components/` if no matching KB Obsidian note exists.

- [ ] **Step 2: Register in settings.json** under PreToolUse with matcher for Write on Components paths.

- [ ] **Step 3: Test locally (try to write a fake component without a KB note — must be blocked).**

- [ ] **Step 4: Commit.**

---

### Task H2: Final composer arka:gate + push + draft PR

- [ ] **Step 1: composer arka:gate. Verdict must be PASSED.**

- [ ] **Step 2: Push branch + open draft PR titled "Plan 2: Dual Auth & Inertia Port".**

- [ ] **Step 3: PR body with checklist + spec/plan references + gate-report verdict.**

- [ ] **Step 4: Tag reviewers (Paulo + Ines + Francisca + Bruno + Marta + Valentina).**

---

## Self-Review

**Spec coverage:**
- §2.6 Dual Auth — Tasks C1-C3 (Staff) + D1-D3 (Customer).
- §6 UI/UX Foundation — Tasks A1-A3 (Inertia bootstrap), B1-B2 (vendor port), E1-E2 (wizardingcode-ui), F1-F3 (color mode + visual regression).
- §7.1 Auth context — covered by C+D.
- §7.3 Backoffice context — covered by B (vendor port).

**Gaps for THIS plan (Plan 2):**
- Dynamic Settings, AI, file upload, notifications, i18n, audit — deferred to Plan 3.
- Install wizard — deferred to Plan 4.

**Placeholder scan:** Palette placeholder `violet + slate` documented as awaiting Valentina (§11.1.5 of spec). All other content concrete.

**Type consistency:** User vs Customer references aligned. CustomerRole enum used consistently. Guards `web` vs `customer` referenced consistently.

**Ambiguity check:** Vendor lock mechanism explicit. SSR-safe cookie color-mode explicit. Light/dark validation criteria explicit.

---

## Execution Handoff

When Plan 1 PR is merged, execute Plan 2 with `superpowers:subagent-driven-development` (recommended) or `superpowers:executing-plans`.
