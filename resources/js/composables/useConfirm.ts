import ConfirmModal from '@/components/shared/ConfirmModal.vue';

export interface ConfirmOptions {
    title?: string;
    description?: string;
    confirmLabel?: string;
    cancelLabel?: string;
    destructive?: boolean;
}

/**
 * Programmatic confirmation dialog. Every destructive action goes through
 * this instead of firing the request on first click.
 *
 *   const confirm = useConfirm();
 *   if (await confirm({ title: 'Apagar página?' })) { ... }
 */
export function useConfirm(): (options?: ConfirmOptions) => Promise<boolean> {
    const overlay = useOverlay();

    return async (options: ConfirmOptions = {}): Promise<boolean> => {
        const modal = overlay.create(ConfirmModal, { props: options });
        const instance = modal.open();
        const confirmed = await instance.result;

        return confirmed === true;
    };
}
