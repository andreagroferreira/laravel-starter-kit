import { z } from 'zod';

export const designTokensSchema = z.object({
    colors: z
        .object({
            background: z.string().optional(),
            foreground: z.string().optional(),
            muted: z.string().optional(),
            accent: z.string().optional(),
            accent_foreground: z.string().optional(),
        })
        .optional()
        .default({}),
    radius: z.string().optional().default('0.5rem'),
    font: z.string().optional().default(''),
});

export type DesignTokens = z.output<typeof designTokensSchema>;

/**
 * Maps a site's design tokens onto the CSS variables every block reads.
 * Both the editor canvas and the renderer apply the result inline, so a
 * block never needs to know where it is being rendered.
 */
export function toCssVars(input: unknown): Record<string, string> {
    const parsed = designTokensSchema.safeParse(input ?? {});
    const tokens = parsed.success ? parsed.data : designTokensSchema.parse({});

    const vars: Record<string, string> = {};

    if (tokens.colors.background) {
        vars['--site-bg'] = tokens.colors.background;
    }
    if (tokens.colors.foreground) {
        vars['--site-fg'] = tokens.colors.foreground;
    }
    if (tokens.colors.muted) {
        vars['--site-muted'] = tokens.colors.muted;
    }
    if (tokens.colors.accent) {
        vars['--site-accent'] = tokens.colors.accent;
    }
    if (tokens.colors.accent_foreground) {
        vars['--site-accent-fg'] = tokens.colors.accent_foreground;
    }
    if (tokens.radius) {
        vars['--site-radius'] = tokens.radius;
    }
    if (tokens.font) {
        vars['--site-font'] = tokens.font;
    }

    return vars;
}
