import { usePage } from '@inertiajs/vue3';
import { watch } from 'vue';

/**
 * Bridges Laravel session flash → Nuxt UI toasts. Mounted once in AppLayout;
 * controllers only need `back()->with('success', '…')`.
 */
export function useFlashToasts(): void {
    const page = usePage();
    const toast = useToast();

    watch(
        () => page.props.flash,
        (flash) => {
            if (flash?.success) {
                toast.add({
                    title: flash.success,
                    color: 'success',
                    icon: 'i-lucide-check-circle',
                });
            }

            if (flash?.error) {
                toast.add({
                    title: flash.error,
                    color: 'error',
                    icon: 'i-lucide-alert-circle',
                });
            }
        },
        { deep: true },
    );
}
