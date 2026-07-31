import type { ContentStatus } from '@/types/models';

type BadgeColor =
    | 'success'
    | 'warning'
    | 'info'
    | 'neutral'
    | 'primary'
    | 'error';

const STATUS_COLORS: Record<ContentStatus, BadgeColor> = {
    draft: 'neutral',
    review: 'info',
    approved: 'info',
    scheduled: 'warning',
    published: 'success',
};

const STATUS_LABELS: Record<ContentStatus, string> = {
    draft: 'Rascunho',
    review: 'Em revisão',
    approved: 'Aprovado',
    scheduled: 'Agendado',
    published: 'Publicado',
};

export function statusColor(status: string): BadgeColor {
    return STATUS_COLORS[status as ContentStatus] ?? 'neutral';
}

export function statusLabel(status: string): string {
    return STATUS_LABELS[status as ContentStatus] ?? status;
}
