// Domain types mirrored from app/Models and app/Enums.

export type SiteType = 'site' | 'landing' | 'news';

export type ContentStatus =
    | 'draft'
    | 'review'
    | 'approved'
    | 'scheduled'
    | 'published';

export type BlockType =
    | 'hero'
    | 'rich_text'
    | 'image'
    | 'cta'
    | 'features'
    | 'testimonials'
    | 'faq'
    | 'pricing'
    | 'form';

export type TenantRole =
    | 'owner'
    | 'editor'
    | 'marketeer'
    | 'journalist'
    | 'editor_in_chief';

export interface AuthUser {
    id: string;
    name: string;
    email: string;
    current_tenant_id: string | null;
}

export interface Tenant {
    id: string;
    name: string;
    slug: string;
    plan: string;
}

export interface Site {
    id: string;
    name: string;
    slug: string;
    type: SiteType;
    status: string;
    domain: string | null;
    renderer_version?: string | null;
    pages_count?: number;
    created_at?: string;
}

export interface Page {
    id: string;
    site_id?: string;
    title: string;
    slug: string;
    status: ContentStatus;
    sort_order?: number;
    seo?: Record<string, string> | null;
    published_at: string | null;
    blocks_count?: number;
}

export interface PageBlock {
    id: string;
    type: BlockType;
    content: Record<string, unknown> | null;
    sort_order: number;
}

export interface Category {
    id: string;
    name: string;
}

export interface Post {
    id: string;
    title: string;
    slug: string;
    excerpt?: string | null;
    body?: string | null;
    status: ContentStatus;
    seo?: Record<string, string> | null;
    published_at: string | null;
    categories?: Category[];
}

export interface MediaAsset {
    id: string;
    name: string;
    path: string;
    url: string;
    mime_type: string;
    size: number;
    alt: string | null;
    created_at?: string;
}

export interface MenuItem {
    label: string;
    url: string;
}

export interface Menu {
    id: string;
    name: string;
    items: MenuItem[];
}

export interface FormField {
    name: string;
    type: string;
    required: boolean;
}

export interface SiteForm {
    id: string;
    name: string;
    fields: FormField[];
}

export interface Redirect {
    id: string;
    from_path: string;
    to_path: string;
    status_code: number;
}

export interface SiteVersion {
    id: string;
    origin: string;
    published_at: string | null;
    created_at: string;
}

export interface ApiToken {
    id: string;
    name: string;
    abilities: string[];
    last_used_at: string | null;
    created_at: string | null;
}

export interface AuditLogEntry {
    id: string;
    actor_type: 'human' | 'agent' | 'system';
    action: string;
    user: { name: string; email: string } | null;
    subject_type: string | null;
    subject_id: string | null;
    payload: Record<string, unknown> | null;
    created_at: string;
}

export interface Member {
    id: string;
    name: string;
    email: string;
    role: TenantRole | string | null;
}

export interface BrandProfile {
    name: string;
    tone_of_voice: string | null;
    glossary: Record<string, string>;
    examples: string[];
    guardrails: string[];
}
