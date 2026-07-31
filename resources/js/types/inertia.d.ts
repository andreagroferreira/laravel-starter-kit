import type { AuthUser } from './models';

export interface FlashToken {
    name: string;
    plainTextToken: string | null;
}

export interface Flash {
    success: string | null;
    error: string | null;
    token: FlashToken | null;
}

declare module '@inertiajs/core' {
    interface PageProps {
        auth: { user: AuthUser | null };
        flash: Flash;
    }
}
