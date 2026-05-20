import { useToast as useNuxtUiToast } from '@nuxt/ui/composables';

export interface WcToastApi {
    success: (message: string) => void;
    error: (message: string) => void;
    info: (message: string) => void;
}

/**
 * Opinionated wrapper around Nuxt UI's `useToast` with WC's semantic helpers.
 */
export function useToast(): WcToastApi {
    const toast = useNuxtUiToast();

    return {
        success: (message: string): void => {
            toast.add({ color: 'success', title: message });
        },
        error: (message: string): void => {
            toast.add({ color: 'error', title: message });
        },
        info: (message: string): void => {
            toast.add({ color: 'primary', title: message });
        },
    };
}
