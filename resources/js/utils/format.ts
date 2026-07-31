const DATE_FORMAT = new Intl.DateTimeFormat('pt-PT', {
    dateStyle: 'medium',
});

const DATETIME_FORMAT = new Intl.DateTimeFormat('pt-PT', {
    dateStyle: 'medium',
    timeStyle: 'short',
});

export function formatDate(value: string | null | undefined): string {
    if (!value) {
        return '—';
    }

    return DATE_FORMAT.format(new Date(value));
}

export function formatDateTime(value: string | null | undefined): string {
    if (!value) {
        return '—';
    }

    return DATETIME_FORMAT.format(new Date(value));
}

export function humanSize(bytes: number): string {
    if (bytes < 1024) {
        return `${bytes} B`;
    }

    const units = ['KB', 'MB', 'GB'];
    let value = bytes;
    let unit = 'B';

    for (const next of units) {
        if (value < 1024) {
            break;
        }
        value /= 1024;
        unit = next;
    }

    return `${value.toFixed(1)} ${unit}`;
}

export function initials(name: string): string {
    return name
        .split(' ')
        .map((part) => part.charAt(0))
        .join('')
        .slice(0, 2)
        .toUpperCase();
}
