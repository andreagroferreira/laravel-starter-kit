import { defineConfig, devices } from '@playwright/test';

/**
 * Playwright visual regression for Histoire stories.
 *
 * Plan 2 Task F3 — light+dark × 3 viewports.
 * Baseline run target: 6 priority Wc* stories × 6 projects = 36 snapshots.
 * Plans 3-5 expand to all 44 variants and wire this into CI.
 *
 * Spec §6.6.E.3 — fails if diff > 2% per spec.
 */
export default defineConfig({
    testDir: '.',
    testMatch: '**/*.spec.ts',
    timeout: 30_000,
    expect: {
        toHaveScreenshot: {
            // Spec §6.6.E.3 — fails if diff > 2%.
            // `maxDiffPixelRatio` is the canonical "% of differing pixels" cap;
            // `threshold` controls per-pixel YIQ tolerance (kept tight at 0.2).
            maxDiffPixelRatio: 0.02,
            threshold: 0.2,
            animations: 'disabled',
        },
    },
    fullyParallel: true,
    forbidOnly: !!process.env.CI,
    retries: process.env.CI ? 2 : 0,
    workers: process.env.CI ? 2 : undefined,
    reporter: process.env.CI ? 'github' : 'list',
    use: {
        baseURL: 'http://localhost:6006',
        trace: 'on-first-retry',
    },
    projects: [
        {
            name: 'mobile-light',
            use: { ...devices['iPhone 13'], colorScheme: 'light', viewport: { width: 375, height: 800 } },
        },
        {
            name: 'mobile-dark',
            use: { ...devices['iPhone 13'], colorScheme: 'dark', viewport: { width: 375, height: 800 } },
        },
        {
            name: 'tablet-light',
            use: { ...devices['iPad'], colorScheme: 'light', viewport: { width: 820, height: 1180 } },
        },
        {
            name: 'tablet-dark',
            use: { ...devices['iPad'], colorScheme: 'dark', viewport: { width: 820, height: 1180 } },
        },
        {
            name: 'desktop-light',
            use: { ...devices['Desktop Chrome'], colorScheme: 'light', viewport: { width: 1440, height: 900 } },
        },
        {
            name: 'desktop-dark',
            use: { ...devices['Desktop Chrome'], colorScheme: 'dark', viewport: { width: 1440, height: 900 } },
        },
    ],
    webServer: process.env.CI ? undefined : {
        command: 'bunx histoire dev --port=6006',
        url: 'http://localhost:6006',
        timeout: 60_000,
        reuseExistingServer: true,
    },
});
