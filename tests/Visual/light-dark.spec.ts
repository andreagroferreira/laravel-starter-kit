import { test, expect } from '@playwright/test';

/**
 * Visual regression for the 26 Histoire stories × 3 viewports × 2 modes.
 *
 * Baseline screenshots are generated on first run (--update-snapshots).
 * CI fails if diff > 2% per spec §6.6.E.3.
 *
 * Histoire serves story sandboxes via the iframe URL:
 *   /__sandbox.html?storyId=<story-id>&variantId=<variant-id>
 *
 * Story IDs are derived from the source path, kebab-cased and slugified
 * (see .histoire/dist/histoire.json). For Plan 2 F3 we test a representative
 * subset (6 priority Wc* stories, 1 variant each). Plans 3-5 expand to all 44
 * variants.
 */
type StoryRef = {
    title: string;
    storyId: string;
    variantId: string;
    screenshotName: string;
};

const stories: StoryRef[] = [
    {
        title: 'WcEmptyState',
        storyId: 'packages-wizardingcode-ui-resources-components-data-wcemptystate-story-vue',
        variantId: 'packages-wizardingcode-ui-resources-components-data-wcemptystate-story-vue-0',
        screenshotName: 'wc-empty-state.png',
    },
    {
        title: 'WcConfirmModal',
        storyId: 'packages-wizardingcode-ui-resources-components-feedback-wcconfirmmodal-story-vue',
        variantId: 'packages-wizardingcode-ui-resources-components-feedback-wcconfirmmodal-story-vue-0',
        screenshotName: 'wc-confirm-modal.png',
    },
    {
        title: 'WcDropzone',
        storyId: 'packages-wizardingcode-ui-resources-components-forms-wcdropzone-story-vue',
        variantId: 'packages-wizardingcode-ui-resources-components-forms-wcdropzone-story-vue-0',
        screenshotName: 'wc-dropzone.png',
    },
    {
        title: 'WcInput',
        storyId: 'packages-wizardingcode-ui-resources-components-forms-wcinput-story-vue',
        variantId: 'packages-wizardingcode-ui-resources-components-forms-wcinput-story-vue-0',
        screenshotName: 'wc-input.png',
    },
    {
        title: 'WcPageHeader',
        storyId: 'packages-wizardingcode-ui-resources-components-layout-wcpageheader-story-vue',
        variantId: 'packages-wizardingcode-ui-resources-components-layout-wcpageheader-story-vue-0',
        screenshotName: 'wc-page-header.png',
    },
    {
        title: 'WcStatsGrid',
        storyId: 'packages-wizardingcode-ui-resources-components-layout-wcstatsgrid-story-vue',
        variantId: 'packages-wizardingcode-ui-resources-components-layout-wcstatsgrid-story-vue-0',
        screenshotName: 'wc-stats-grid.png',
    },
];

for (const story of stories) {
    test(`renders ${story.title} consistently`, async ({ page }) => {
        const url = `/__sandbox.html?storyId=${story.storyId}&variantId=${story.variantId}`;
        await page.goto(url);
        await page.waitForLoadState('networkidle');
        // Ensure web fonts are fully loaded before snapshotting; reduces flake.
        await page.evaluate(() => document.fonts?.ready);
        // Settle remaining transitions / async render passes.
        await page.waitForTimeout(500);
        await expect(page).toHaveScreenshot(story.screenshotName, {
            fullPage: false,
        });
    });
}
