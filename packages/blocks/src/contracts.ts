import { z } from 'zod';

/**
 * Canonical block contracts, shared by the backoffice editor and the
 * public renderer. Legacy shapes (title/description pairs written by the
 * first editor) are accepted and normalised via transforms so existing
 * content keeps rendering.
 */

const optionalText = z.string().optional().default('');

export const heroSchema = z.object({
    heading: optionalText,
    subheading: optionalText,
    cta_label: optionalText,
    cta_url: optionalText,
    image_url: optionalText,
});

export const richTextSchema = z.object({
    html: optionalText,
});

export const imageSchema = z.object({
    url: optionalText,
    alt: optionalText,
    caption: optionalText,
});

export const ctaSchema = z.object({
    heading: optionalText,
    description: optionalText,
    label: optionalText,
    url: optionalText,
});

const featureItem = z
    .object({
        title: optionalText,
        description: optionalText,
        icon: optionalText,
    })
    .passthrough();

export const featuresSchema = z.object({
    heading: optionalText,
    items: z.array(featureItem).optional().default([]),
});

const testimonialItem = z
    .object({
        quote: z.string().optional(),
        author: z.string().optional(),
        role: z.string().optional(),
        avatar_url: z.string().optional(),
        // Legacy: the old editor stored title/description.
        title: z.string().optional(),
        description: z.string().optional(),
    })
    .transform((item) => ({
        quote: item.quote ?? item.description ?? '',
        author: item.author ?? item.title ?? '',
        role: item.role ?? '',
        avatar_url: item.avatar_url ?? '',
    }));

export const testimonialsSchema = z.object({
    heading: optionalText,
    items: z.array(testimonialItem).optional().default([]),
});

const pricingItem = z
    .object({
        name: z.string().optional(),
        price: z.string().optional(),
        period: z.string().optional(),
        features: z.array(z.string()).optional(),
        cta_label: z.string().optional(),
        cta_url: z.string().optional(),
        highlighted: z.boolean().optional(),
        title: z.string().optional(),
        description: z.string().optional(),
    })
    .transform((item) => ({
        name: item.name ?? item.title ?? '',
        price: item.price ?? '',
        period: item.period ?? '',
        features: item.features ?? [],
        cta_label: item.cta_label ?? '',
        cta_url: item.cta_url ?? '',
        highlighted: item.highlighted ?? false,
        description: item.description ?? '',
    }));

export const pricingSchema = z.object({
    heading: optionalText,
    items: z.array(pricingItem).optional().default([]),
});

const faqItem = z
    .object({
        question: z.string().optional(),
        answer: z.string().optional(),
        title: z.string().optional(),
        description: z.string().optional(),
    })
    .transform((item) => ({
        question: item.question ?? item.title ?? '',
        answer: item.answer ?? item.description ?? '',
    }));

export const faqSchema = z.object({
    heading: optionalText,
    items: z.array(faqItem).optional().default([]),
});

export const formSchema = z.object({
    heading: optionalText,
    description: optionalText,
    form_id: optionalText,
    submit_label: optionalText,
});

export type HeroContent = z.output<typeof heroSchema>;
export type RichTextContent = z.output<typeof richTextSchema>;
export type ImageContent = z.output<typeof imageSchema>;
export type CtaContent = z.output<typeof ctaSchema>;
export type FeaturesContent = z.output<typeof featuresSchema>;
export type TestimonialsContent = z.output<typeof testimonialsSchema>;
export type PricingContent = z.output<typeof pricingSchema>;
export type FaqContent = z.output<typeof faqSchema>;
export type FormContent = z.output<typeof formSchema>;
