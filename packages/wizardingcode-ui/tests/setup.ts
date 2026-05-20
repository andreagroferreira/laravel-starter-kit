/**
 * Vitest setup for @wizardingcode/ui.
 *
 * Smoke-render tests in F2 / Plan 5 will hook here for global stubs.
 * For now we only normalize the test environment so component imports
 * resolve cleanly under Vue Test Utils.
 */

// Stub Nuxt UI's auto-imported component tags so smoke renders don't fail.
// Real visual coverage lives in the Histoire stories (F2).
import { config } from '@vue/test-utils';

const stubbedTags = [
    'UButton',
    'UCard',
    'UCheckbox',
    'UFormField',
    'UIcon',
    'UInput',
    'UModal',
    'USelect',
    'UTable',
    'UToaster',
];

config.global.stubs = Object.fromEntries(
    stubbedTags.map((tag) => [tag, true]),
);
