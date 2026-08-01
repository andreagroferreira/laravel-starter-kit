import type { Component } from 'vue';
import type { ZodType } from 'zod';
import {
    ctaSchema,
    faqSchema,
    featuresSchema,
    formSchema,
    heroSchema,
    imageSchema,
    pricingSchema,
    richTextSchema,
    testimonialsSchema,
} from './contracts';
import CtaBlock from './components/CtaBlock.vue';
import FaqBlock from './components/FaqBlock.vue';
import FeaturesBlock from './components/FeaturesBlock.vue';
import FormBlock from './components/FormBlock.vue';
import HeroBlock from './components/HeroBlock.vue';
import ImageBlock from './components/ImageBlock.vue';
import PricingBlock from './components/PricingBlock.vue';
import RichTextBlock from './components/RichTextBlock.vue';
import TestimonialsBlock from './components/TestimonialsBlock.vue';

export type BlockType =
    | 'hero'
    | 'rich_text'
    | 'image'
    | 'cta'
    | 'features'
    | 'testimonials'
    | 'pricing'
    | 'faq'
    | 'form';

/** How the inspector renders each field of a block. */
export type FieldKind =
    | 'text'
    | 'textarea'
    | 'url'
    | 'rich-text'
    | 'media'
    | 'form-select'
    | 'repeater';

export interface FieldDefinition {
    key: string;
    label: string;
    kind: FieldKind;
    /** Fields of each row, for repeaters. */
    item?: FieldDefinition[];
    /** True when the field is also editable inline on the canvas. */
    inline?: boolean;
}

export interface BlockDefinition {
    type: BlockType;
    label: string;
    icon: string;
    description: string;
    component: Component;
    schema: ZodType;
    defaults: () => Record<string, unknown>;
    fields: FieldDefinition[];
}

const textField = (key: string, label: string, inline = true): FieldDefinition => ({
    key,
    label,
    kind: 'text',
    inline,
});

const areaField = (key: string, label: string, inline = true): FieldDefinition => ({
    key,
    label,
    kind: 'textarea',
    inline,
});

const urlField = (key: string, label: string): FieldDefinition => ({
    key,
    label,
    kind: 'url',
});

export const registry: Record<BlockType, BlockDefinition> = {
    hero: {
        type: 'hero',
        label: 'Hero',
        icon: 'i-lucide-panel-top',
        description: 'Cabeçalho com título, subtítulo e chamada à ação.',
        component: HeroBlock,
        schema: heroSchema,
        defaults: () => ({
            heading: 'O teu título aqui',
            subheading: 'Uma frase que explica o valor da oferta.',
            cta_label: 'Começar',
            cta_url: '#',
            image_url: '',
        }),
        fields: [
            textField('heading', 'Título'),
            areaField('subheading', 'Subtítulo'),
            textField('cta_label', 'Texto do botão', false),
            urlField('cta_url', 'Link do botão'),
            { key: 'image_url', label: 'Imagem de fundo', kind: 'media' },
        ],
    },
    rich_text: {
        type: 'rich_text',
        label: 'Texto',
        icon: 'i-lucide-text',
        description: 'Bloco de texto formatado.',
        component: RichTextBlock,
        schema: richTextSchema,
        defaults: () => ({ html: '<p>Escreve aqui…</p>' }),
        fields: [{ key: 'html', label: 'Conteúdo', kind: 'rich-text' }],
    },
    image: {
        type: 'image',
        label: 'Imagem',
        icon: 'i-lucide-image',
        description: 'Imagem com legenda.',
        component: ImageBlock,
        schema: imageSchema,
        defaults: () => ({ url: '', alt: '', caption: '' }),
        fields: [
            { key: 'url', label: 'Imagem', kind: 'media' },
            textField('alt', 'Texto alternativo', false),
            textField('caption', 'Legenda'),
        ],
    },
    cta: {
        type: 'cta',
        label: 'Call to action',
        icon: 'i-lucide-megaphone',
        description: 'Bloco de conversão com botão.',
        component: CtaBlock,
        schema: ctaSchema,
        defaults: () => ({
            heading: 'Pronto para começar?',
            description: 'Diz-nos o que precisas e tratamos do resto.',
            label: 'Falar connosco',
            url: '#',
        }),
        fields: [
            textField('heading', 'Título'),
            areaField('description', 'Descrição'),
            textField('label', 'Texto do botão', false),
            urlField('url', 'Link do botão'),
        ],
    },
    features: {
        type: 'features',
        label: 'Funcionalidades',
        icon: 'i-lucide-layout-grid',
        description: 'Grelha de funcionalidades ou benefícios.',
        component: FeaturesBlock,
        schema: featuresSchema,
        defaults: () => ({
            heading: 'O que oferecemos',
            items: [
                { title: 'Rápido', description: 'Publica em segundos.' },
                { title: 'Simples', description: 'Sem código.' },
                { title: 'Seguro', description: 'Backups automáticos.' },
            ],
        }),
        fields: [
            textField('heading', 'Título da secção'),
            {
                key: 'items',
                label: 'Funcionalidades',
                kind: 'repeater',
                item: [
                    textField('title', 'Título', false),
                    areaField('description', 'Descrição', false),
                ],
            },
        ],
    },
    testimonials: {
        type: 'testimonials',
        label: 'Testemunhos',
        icon: 'i-lucide-quote',
        description: 'Prova social de clientes.',
        component: TestimonialsBlock,
        schema: testimonialsSchema,
        defaults: () => ({
            heading: 'O que dizem de nós',
            items: [
                { quote: 'Mudou a forma como trabalhamos.', author: 'Ana Silva', role: 'CEO' },
            ],
        }),
        fields: [
            textField('heading', 'Título da secção'),
            {
                key: 'items',
                label: 'Testemunhos',
                kind: 'repeater',
                item: [
                    areaField('quote', 'Citação', false),
                    textField('author', 'Autor', false),
                    textField('role', 'Cargo', false),
                    { key: 'avatar_url', label: 'Avatar', kind: 'media' },
                ],
            },
        ],
    },
    pricing: {
        type: 'pricing',
        label: 'Preços',
        icon: 'i-lucide-euro',
        description: 'Tabela de planos com preços e CTA.',
        component: PricingBlock,
        schema: pricingSchema,
        defaults: () => ({
            heading: 'Planos',
            items: [
                {
                    name: 'Pro',
                    price: '29€',
                    period: 'mês',
                    features: ['Tudo do plano base', 'Suporte prioritário'],
                    cta_label: 'Escolher',
                    cta_url: '#',
                    highlighted: true,
                },
            ],
        }),
        fields: [
            textField('heading', 'Título da secção'),
            {
                key: 'items',
                label: 'Planos',
                kind: 'repeater',
                item: [
                    textField('name', 'Nome', false),
                    textField('price', 'Preço', false),
                    textField('period', 'Período', false),
                    areaField('description', 'Descrição', false),
                    textField('cta_label', 'Texto do botão', false),
                    urlField('cta_url', 'Link do botão'),
                ],
            },
        ],
    },
    faq: {
        type: 'faq',
        label: 'FAQ',
        icon: 'i-lucide-circle-help',
        description: 'Perguntas frequentes.',
        component: FaqBlock,
        schema: faqSchema,
        defaults: () => ({
            heading: 'Perguntas frequentes',
            items: [
                { question: 'Como começo?', answer: 'Cria conta e publica o primeiro site.' },
            ],
        }),
        fields: [
            textField('heading', 'Título da secção'),
            {
                key: 'items',
                label: 'Perguntas',
                kind: 'repeater',
                item: [
                    textField('question', 'Pergunta', false),
                    areaField('answer', 'Resposta', false),
                ],
            },
        ],
    },
    form: {
        type: 'form',
        label: 'Formulário',
        icon: 'i-lucide-clipboard-list',
        description: 'Formulário de contacto ligado à captação de leads.',
        component: FormBlock,
        schema: formSchema,
        defaults: () => ({
            heading: 'Fala connosco',
            description: '',
            form_id: '',
            submit_label: 'Enviar',
        }),
        fields: [
            textField('heading', 'Título'),
            areaField('description', 'Descrição'),
            { key: 'form_id', label: 'Formulário', kind: 'form-select' },
            textField('submit_label', 'Texto do botão', false),
        ],
    },
};

export const blockTypes = Object.keys(registry) as BlockType[];

export function isBlockType(value: string): value is BlockType {
    return value in registry;
}

/**
 * Parse raw block content through its schema so legacy shapes are
 * normalised and every field has a defined value.
 */
export function parseContent(
    type: BlockType,
    content: Record<string, unknown> | null,
): Record<string, unknown> {
    const result = registry[type].schema.safeParse(content ?? {});

    return result.success
        ? (result.data as Record<string, unknown>)
        : (registry[type].schema.parse({}) as Record<string, unknown>);
}
