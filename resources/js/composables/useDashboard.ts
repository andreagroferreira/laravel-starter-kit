import {ref, watch} from 'vue';
import {router, usePage} from '@inertiajs/vue3';
import {createSharedComposable} from '@vueuse/core';

const _useDashboard = () => {
    const page = usePage();
    const isNotificationsSlideoverOpen = ref(false);

    defineShortcuts({
        'g-h': () => router.visit('/'),
        'g-i': () => router.visit('/inbox'),
        'g-c': () => router.visit('/customers'),
        'g-s': () => router.visit('/settings'),
        'n': () =>
            (isNotificationsSlideoverOpen.value =
                !isNotificationsSlideoverOpen.value),
    });

    watch(
        () => page.url,
        () => {
            isNotificationsSlideoverOpen.value = false;
        },
    );

    return {
        isNotificationsSlideoverOpen,
    };
};

export const useDashboard = createSharedComposable(_useDashboard);
